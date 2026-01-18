<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Interest;
use Illuminate\Http\Request;

class InterestController extends Controller
{
    public function index()
    {
        $interests = Interest::orderBy('category')->orderBy('name')->get();

        $grouped = $interests->groupBy('category')->map(function ($items) {
            return $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'icon' => $item->icon,
                ];
            })->values();
        });

        return response()->json([
            'success' => true,
            'data' => $grouped,
        ], 200);
    }

    public function attach(Request $request)
    {
        $validated = $request->validate([
            'interest_ids' => 'required|array',
            'interest_ids.*' => 'exists:interests,id',
        ]);

        $user = $request->user();

        if (count($validated['interest_ids']) > 10) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez pas avoir plus de 10 centres d\'intérêt',
            ], 400);
        }

        $user->interests()->sync($validated['interest_ids']);

        return response()->json([
            'success' => true,
            'message' => 'Centres d\'intérêt mis à jour',
            'data' => $user->interests,
        ], 200);
    }

    public function myInterests(Request $request)
    {
        $interests = $request->user()->interests;

        return response()->json([
            'success' => true,
            'data' => $interests,
        ], 200);
    }
}
