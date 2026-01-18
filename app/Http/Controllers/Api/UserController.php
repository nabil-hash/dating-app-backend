<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'first_name' => 'sometimes|string|min:2|max:50',
            'date_of_birth' => 'sometimes|date|before:today',
            'gender' => 'sometimes|in:male,female,non_binary,other',
            'city' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'bio' => 'nullable|string|max:500',
        ]);

        try {
            $user->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Profil mis à jour avec succès',
                'data' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'first_name' => $user->first_name,
                    'age' => $user->age,
                    'gender' => $user->gender,
                    'city' => $user->city,
                    'bio' => $user->bio,
                    'is_verified' => $user->is_verified,
                    'is_premium' => $user->is_premium,
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        $user = \App\Models\User::with('photos')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'age' => $user->age,
                'gender' => $user->gender,
                'city' => $user->city,
                'bio' => $user->bio,
                'is_verified' => $user->is_verified,
                'photos' => $user->photos,
            ],
        ], 200);
    }
}
