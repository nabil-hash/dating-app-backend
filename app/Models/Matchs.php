<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Matchs extends Model
{
    use HasFactory;

    protected $table = 'matchs'; // important si ta table s'appelle bien "matchs"

    protected $fillable = [
        'user1_id',
        'user2_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Utilisateur 1 du match
     */
    public function user1()
    {
        return $this->belongsTo(User::class, 'user1_id');
    }

    /**
     * Utilisateur 2 du match
     */
    public function user2()
    {
        return $this->belongsTo(User::class, 'user2_id');
    }

    /**
     * Récupère l'autre utilisateur du match
     */
    public function getOtherUser($currentUserId)
    {
        if ($this->user1_id === $currentUserId) {
            return $this->user2;
        }

        return $this->user1;
    }

    /**
     * Tous les messages du match
     */
    public function messages()
    {
        return $this->hasMany(Message::class, 'match_id')
                    ->orderBy('created_at', 'asc');
    }

    /**
     * Dernier message du match
     */
    public function lastMessage()
    {
        return $this->hasOne(Message::class, 'match_id')
                    ->latestOfMany();
    }
}
 