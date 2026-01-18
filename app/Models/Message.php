<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'match_id',
        'sender_id',
        'receiver_id',
        'content',
        'type',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    /**
     * Le match auquel appartient le message
     */
    public function match()
    {
        return $this->belongsTo(Matchs::class);
    }

    /**
     * L'expéditeur du message
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Le destinataire du message
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
