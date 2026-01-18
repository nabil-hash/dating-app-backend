<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'email',
        'password',
        'first_name',
        'date_of_birth',
        'gender',
        'city',
        'latitude',
        'longitude',
        'bio',
        'is_verified',
        'is_premium',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'is_verified' => 'boolean',
        'is_premium' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Accès pour l'âge calculé
     */
    public function getAgeAttribute(): int
    {
        return now()->diffInYears($this->date_of_birth);
    }

    /**
     * Photos de l'utilisateur
     */
    public function photos()
    {
        return $this->hasMany(Photo::class)->orderBy('order_index');
    }

    /**
     * Centres d'intérêt de l'utilisateur
     */
    public function interests()
    {
        return $this->belongsToMany(Interest::class, 'user_interests');
    }

    /**
     * Swipes envoyés
     */
    public function swipesSent()
    {
        return $this->hasMany(Swipe::class, 'swiper_id');
    }

    /**
     * Swipes reçus
     */
    public function swipesReceived()
    {
        return $this->hasMany(Swipe::class, 'swiped_id');
    }

    /**
     * Matchs où l'utilisateur est user1
     */
    public function matchesAsUser1()
    {
        return $this->hasMany(Matchs::class, 'user1_id');
    }

    /**
     * Matchs où l'utilisateur est user2
     */
    public function matchesAsUser2()
    {
        return $this->hasMany(Matchs::class, 'user2_id');
    }

    /**
     * Tous les matchs de l'utilisateur
     */
    public function matches()
    {
        return Matchs::where(function ($query) {
            $query->where('user1_id', $this->id)
                  ->orWhere('user2_id', $this->id);
        });
    }

    /**
     * Messages envoyés
     */
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Messages reçus
     */
    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }
}
