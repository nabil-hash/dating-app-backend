<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Autoriser tous les utilisateurs (même non authentifiés)
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Règles de validation
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed', // password_confirmation requis
            'first_name' => 'required|string|min:2|max:50',
            'date_of_birth' => 'required|date|before:today|after:1920-01-01',
            'gender' => 'required|in:male,female,non_binary,other',
            'city' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ];
    }

    /**
     * Messages d'erreur personnalisés (en français)
     */
    public function messages(): array
    {
        return [
            'email.required' => 'L\'email est obligatoire',
            'email.email' => 'Format email invalide',
            'email.unique' => 'Cet email est déjà utilisé',
            'password.required' => 'Le mot de passe est obligatoire',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères',
            'password.confirmed' => 'Les mots de passe ne correspondent pas',
            'first_name.required' => 'Le prénom est obligatoire',
            'date_of_birth.required' => 'La date de naissance est obligatoire',
            'date_of_birth.before' => 'La date de naissance doit être dans le passé',
            'gender.required' => 'Le genre est obligatoire',
            'gender.in' => 'Genre invalide',
        ];
    }
}
