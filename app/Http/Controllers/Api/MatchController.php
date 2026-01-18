<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Swipe;
use App\Models\Matchs;
use Illuminate\Http\Request;
use App\Services\CompatibilityService;

class MatchController extends Controller
{
    protected $compatibilityService;

    public function __construct(CompatibilityService $compatibilityService)
    {
        $this->compatibilityService = $compatibilityService;
    }

    public function discover(Request $request)
    {
        $currentUser = $request->user()->load('interests');

        $swipedIds = Swipe::where('swiper_id', $currentUser->id)
            ->pluck('swiped_id')
            ->toArray();

        $swipedIds[] = $currentUser->id;

        $users = User::with(['photos', 'interests'])
            ->whereNotIn('id', $swipedIds)
            ->where('is_active', true)
            ->limit(20)
            ->get()
            ->map(function ($user) use ($currentUser) {
                $score = $this->compatibilityService->calculateScore($currentUser, $user);

                return [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'age' => $user->age,
                    'gender' => $user->gender,
                    'city' => $user->city,
                    'bio' => $user->bio,
                    'is_verified' => $user->is_verified,
                    'photos' => $user->photos,
                    'compatibility_score' => $score,
                ];
            })
            ->sortByDesc('compatibility_score')
            ->take(10)
            ->values();

        return response()->json([
            'success' => true,
            'data' => $users,
        ], 200);
    }

    public function swipe(Request $request)
    {
        $validated = $request->validate([
            'swiped_id' => 'required|exists:users,id',
            'direction' => 'required|in:like,pass',
        ]);

        $currentUser = $request->user();
        $swipedId = $validated['swiped_id'];
        $direction = $validated['direction'];

        if ($currentUser->id == $swipedId) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez pas swiper votre propre profil',
            ], 400);
        }

        try {
            $swipe = Swipe::create([
                'swiper_id' => $currentUser->id,
                'swiped_id' => $swipedId,
                'direction' => $direction,
            ]);

            $matched = false;
            $match = null;
            $otherUser = null;

            if ($direction === 'like') {
                $reciprocalSwipe = Swipe::where('swiper_id', $swipedId)
                    ->where('swiped_id', $currentUser->id)
                    ->where('direction', 'like')
                    ->first();

                if ($reciprocalSwipe) {
                    $matched = true;
                    $user1Id = min($currentUser->id, $swipedId);
                    $user2Id = max($currentUser->id, $swipedId);

                    $match = Matchs::firstOrCreate([
                        'user1_id' => $user1Id,
                        'user2_id' => $user2Id,
                    ]);

                    $otherUser = User::with('photos')->find($swipedId);
                }
            }

            return response()->json([
                'success' => true,
                'message' => $direction === 'like' ? 'Like envoyé' : 'Passé',
                'data' => [
                    'swipe' => $swipe,
                    'matched' => $matched,
                    'match' => $matched ? [
                        'id' => $match->id,
                        'user' => [
                            'id' => $otherUser->id,
                            'first_name' => $otherUser->first_name,
                            'age' => $otherUser->age,
                            'photos' => $otherUser->photos,
                        ],
                    ] : null,
                ],
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du swipe',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function matches(Request $request)
    {
        $currentUser = $request->user();

        $matches = Matchs::where(function ($query) use ($currentUser) {
                $query->where('user1_id', $currentUser->id)
                      ->orWhere('user2_id', $currentUser->id);
            })
            ->where('is_active', true)
            ->with(['user1.photos', 'user2.photos'])
            ->get()
            ->map(function ($match) use ($currentUser) {
                $otherUser = $match->getOtherUser($currentUser->id);
                return [
                    'match_id' => $match->id,
                    'matched_at' => $match->created_at,
                    'user' => [
                        'id' => $otherUser->id,
                        'first_name' => $otherUser->first_name,
                        'age' => $otherUser->age,
                        'city' => $otherUser->city,
                        'bio' => $otherUser->bio,
                        'photos' => $otherUser->photos,
                    ],
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $matches,
        ], 200);
    }

    public function unmatch(Request $request, $matchId)
    {
        $currentUser = $request->user();
        $match = Matchs::findOrFail($matchId);

        if ($match->user1_id !== $currentUser->id && $match->user2_id !== $currentUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'Ce match ne vous appartient pas',
            ], 403);
        }

        $match->update(['is_active' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Match supprimé',
        ], 200);
    }
}
