<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PhotoController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'order_index' => 'required|integer|between:1,6',
            'is_primary' => 'boolean',
        ], [
            'photo.required' => 'La photo est obligatoire',
            'photo.image' => 'Le fichier doit être une image',
            'photo.mimes' => 'Format accepté : jpeg, png, jpg',
            'photo.max' => 'La photo ne doit pas dépasser 5MB',
            'order_index.required' => 'L\'ordre est obligatoire',
            'order_index.between' => 'L\'ordre doit être entre 1 et 6',
        ]);

        $user = $request->user();

        if ($user->photos()->count() >= 6) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez pas avoir plus de 6 photos',
            ], 400);
        }

        try {
            $file = $request->file('photo');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('photos', $filename, 'public');
            $url = Storage::url($path);

            if ($request->is_primary) {
                $user->photos()->update(['is_primary' => false]);
            }

            $photo = Photo::create([
                'user_id' => $user->id,
                'url' => $url,
                'order_index' => $request->order_index,
                'is_primary' => $request->is_primary ?? false,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Photo uploadée avec succès',
                'data' => $photo,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'upload',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function index(Request $request)
    {
        $photos = $request->user()->photos()->orderBy('order_index')->get();

        return response()->json([
            'success' => true,
            'data' => $photos,
        ], 200);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $photo = Photo::findOrFail($id);

        if ($photo->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Cette photo ne vous appartient pas',
            ], 403);
        }

        try {
            $filename = basename($photo->url);
            Storage::disk('public')->delete('photos/' . $filename);
            $photo->delete();

            return response()->json([
                'success' => true,
                'message' => 'Photo supprimée avec succès',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function setPrimary(Request $request, $id)
    {
        $user = $request->user();
        $photo = Photo::findOrFail($id);

        if ($photo->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Cette photo ne vous appartient pas',
            ], 403);
        }

        $user->photos()->update(['is_primary' => false]);
        $photo->update(['is_primary' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Photo principale définie',
            'data' => $photo,
        ], 200);
    }
}
