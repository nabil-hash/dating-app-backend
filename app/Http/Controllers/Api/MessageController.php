<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Matchs;
use Illuminate\Http\Request;
use App\Events\NewMessage as NewMessageEvent;

class MessageController extends Controller
{
    public function index(Request $request, $matchId)
    {
        $currentUser = $request->user();
        $match = Matchs::findOrFail($matchId);

        if ($match->user1_id !== $currentUser->id && $match->user2_id !== $currentUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé',
            ], 403);
        }

        $messages = $match->messages()
            ->with(['sender:id,first_name', 'receiver:id,first_name'])
            ->get()
            ->map(function ($message) use ($currentUser) {
                return [
                    'id' => $message->id,
                    'content' => $message->content,
                    'type' => $message->type,
                    'sender_id' => $message->sender_id,
                    'is_mine' => $message->sender_id === $currentUser->id,
                    'is_read' => $message->is_read,
                    'sent_at' => $message->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $messages,
        ], 200);
    }

    public function store(Request $request, $matchId)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:1000',
            'type' => 'sometimes|in:text,audio,image',
        ], [
            'content.required' => 'Le message ne peut pas être vide',
            'content.max' => 'Le message ne peut pas dépasser 1000 caractères',
        ]);

        $currentUser = $request->user();
        $match = Matchs::findOrFail($matchId);

        if ($match->user1_id !== $currentUser->id && $match->user2_id !== $currentUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé',
            ], 403);
        }

        $receiverId = $match->user1_id === $currentUser->id
            ? $match->user2_id
            : $match->user1_id;

        try {
            $message = Message::create([
                'match_id' => $matchId,
                'sender_id' => $currentUser->id,
                'receiver_id' => $receiverId,
                'content' => $validated['content'],
                'type' => $validated['type'] ?? 'text',
            ]);

            // Diffuser l'événement
            broadcast(new NewMessageEvent($message))->toOthers();

            return response()->json([
                'success' => true,
                'message' => 'Message envoyé',
                'data' => [
                    'id' => $message->id,
                    'content' => $message->content,
                    'type' => $message->type,
                    'sender_id' => $message->sender_id,
                    'is_mine' => true,
                    'is_read' => false,
                    'sent_at' => $message->created_at,
                ],
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'envoi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function markAsRead(Request $request, $matchId)
    {
        $currentUser = $request->user();
        $match = Matchs::findOrFail($matchId);

        if ($match->user1_id !== $currentUser->id && $match->user2_id !== $currentUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé',
            ], 403);
        }

        Message::where('match_id', $matchId)
            ->where('receiver_id', $currentUser->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Messages marqués comme lus',
        ], 200);
    }

    public function conversations(Request $request)
    {
        $currentUser = $request->user();

        $matches = Matchs::where(function ($query) use ($currentUser) {
                $query->where('user1_id', $currentUser->id)
                      ->orWhere('user2_id', $currentUser->id);
            })
            ->where('is_active', true)
            ->with(['user1.photos', 'user2.photos', 'lastMessage'])
            ->get()
            ->map(function ($match) use ($currentUser) {
                $otherUser = $match->getOtherUser($currentUser->id);

                $unreadCount = Message::where('match_id', $match->id)
                    ->where('receiver_id', $currentUser->id)
                    ->where('is_read', false)
                    ->count();

                return [
                    'match_id' => $match->id,
                    'user' => [
                        'id' => $otherUser->id,
                        'first_name' => $otherUser->first_name,
                        'age' => $otherUser->age,
                        'photos' => $otherUser->photos,
                    ],
                    'last_message' => $match->lastMessage ? [
                        'content' => $match->lastMessage->content,
                        'sent_at' => $match->lastMessage->created_at,
                        'is_mine' => $match->lastMessage->sender_id === $currentUser->id,
                    ] : null,
                    'unread_count' => $unreadCount,
                ];
            })
            ->sortByDesc(function ($conversation) {
                return $conversation['last_message']['sent_at'] ?? $conversation['match_id'];
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => $matches,
        ], 200);
    }
}
