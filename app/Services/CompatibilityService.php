<?php

namespace App\Services;

use App\Models\User;

class CompatibilityService
{
    public function calculateScore(User $user1, User $user2): int
    {
        $score = 0;
        $score += $this->calculateInterestScore($user1, $user2);
        $score += $this->calculateDistanceScore($user1, $user2);
        $score += $this->calculateAgeScore($user1, $user2);

        return min($score, 100);
    }

    private function calculateInterestScore(User $user1, User $user2): int
    {
        $interests1 = $user1->interests->pluck('id')->toArray();
        $interests2 = $user2->interests->pluck('id')->toArray();

        $common = count(array_intersect($interests1, $interests2));

        if (empty($interests1) || empty($interests2)) {
            return 20;
        }

        return min($common * 8, 40);
    }

    private function calculateDistanceScore(User $user1, User $user2): int
    {
        if (!$user1->latitude || !$user1->longitude || !$user2->latitude || !$user2->longitude) {
            return 15;
        }

        $distance = $this->calculateDistance(
            $user1->latitude,
            $user1->longitude,
            $user2->latitude,
            $user2->longitude
        );

        if ($distance < 10) return 30;
        if ($distance < 50) return 20;
        if ($distance < 100) return 10;

        return 5;
    }

    private function calculateAgeScore(User $user1, User $user2): int
    {
        $ageDiff = abs($user1->age - $user2->age);

        if ($ageDiff <= 1) return 30;
        if ($ageDiff <= 5) return 25;
        if ($ageDiff <= 10) return 15;

        return 5;
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2): float
    {
        $earthRadius = 6371;

        $latDiff = deg2rad($lat2 - $lat1);
        $lonDiff = deg2rad($lon2 - $lon1);

        $a = sin($latDiff / 2) * sin($latDiff / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lonDiff / 2) * sin($lonDiff / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    public function getScoreDetails(User $user1, User $user2): array
    {
        $interestScore = $this->calculateInterestScore($user1, $user2);
        $distanceScore = $this->calculateDistanceScore($user1, $user2);
        $ageScore = $this->calculateAgeScore($user1, $user2);
        $totalScore = min($interestScore + $distanceScore + $ageScore, 100);

        $interests1 = $user1->interests->pluck('id')->toArray();
        $interests2 = $user2->interests->pluck('id')->toArray();
        $commonInterestIds = array_intersect($interests1, $interests2);

        $commonInterests = $user1->interests
            ->whereIn('id', $commonInterestIds)
            ->pluck('name')
            ->toArray();

        $distance = null;
        if ($user1->latitude && $user2->latitude) {
            $distance = round($this->calculateDistance(
                $user1->latitude,
                $user1->longitude,
                $user2->latitude,
                $user2->longitude
            ));
        }

        $ageDiff = abs($user1->age - $user2->age);

        return [
            'total_score' => $totalScore,
            'breakdown' => [
                'interests' => [
                    'score' => $interestScore,
                    'max' => 40,
                    'common_interests' => $commonInterests,
                    'count' => count($commonInterests),
                ],
                'distance' => [
                    'score' => $distanceScore,
                    'max' => 30,
                    'distance_km' => $distance,
                ],
                'age' => [
                    'score' => $ageScore,
                    'max' => 30,
                    'difference' => $ageDiff,
                ],
            ],
        ];
    }
}
