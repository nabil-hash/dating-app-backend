<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('match.{matchId}', function ($user, $matchId) {
    $match = \App\Models\Matchs::find($matchId);

    if (!$match) {
        return false;
    }

    return $match->user1_id === $user->id || $match->user2_id === $user->id;
});
