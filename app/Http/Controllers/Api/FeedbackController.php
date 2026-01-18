<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:bug,feature,other',
            'page' => 'nullable|string|max:255',
            'message' => 'required|string|max:1000',
            'email' => 'nullable|email',
        ]);

        try {
            $feedback = Feedback::create([
                'user_id' => $request->user() ? $request->user()->id : null,
                'type' => $validated['type'],
                'page' => $validated['page'] ?? null,
                'message' => $validated['message'],
                'email' => $validated['email'] ?? ($request->user() ? $request->user()->email : null),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Merci pour votre feedback !',
                'data' => $feedback,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'envoi du feedback',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function index(Request $request)
    {
        $feedbacks = Feedback::with('user:id,first_name,email')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $feedbacks,
        ], 200);
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:new,in_progress,resolved',
        ]);

        $feedback = Feedback::findOrFail($id);
        $feedback->update(['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'message' => 'Statut mis à jour',
            'data' => $feedback,
        ], 200);
    }
}
