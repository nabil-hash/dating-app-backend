<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Swipe extends Model
{
    use HasFactory;

    protected $fillable = [
        'swiper_id',
        'swiped_id',
        'direction',
    ];

    /**
     * L'utilisateur qui swipe
     */
    public function swiper()
    {
        return $this->belongsTo(User::class, 'swiper_id');
    }

    /**
     * L'utilisateur qui est swipé
     */
    public function swiped()
    {
        return $this->belongsTo(User::class, 'swiped_id');
    }
}
