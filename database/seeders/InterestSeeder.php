<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Interest;

class InterestSeeder extends Seeder
{
    public function run(): void
    {
        $interests = [
            // Sports
            ['name' => 'Football', 'category' => 'Sport', 'icon' => '⚽'],
            ['name' => 'Basketball', 'category' => 'Sport', 'icon' => '🏀'],
            ['name' => 'Tennis', 'category' => 'Sport', 'icon' => '🎾'],
            ['name' => 'Natation', 'category' => 'Sport', 'icon' => '🏊'],
            ['name' => 'Randonnée', 'category' => 'Sport', 'icon' => '🥾'],
            ['name' => 'Yoga', 'category' => 'Sport', 'icon' => '🧘'],
            ['name' => 'Course à pied', 'category' => 'Sport', 'icon' => '🏃'],
            ['name' => 'Musculation', 'category' => 'Sport', 'icon' => '🏋️'],

            // Musique
            ['name' => 'Pop', 'category' => 'Musique', 'icon' => '🎵'],
            ['name' => 'Rock', 'category' => 'Musique', 'icon' => '🎸'],
            ['name' => 'Hip-Hop', 'category' => 'Musique', 'icon' => '🎤'],
            ['name' => 'Electro', 'category' => 'Musique', 'icon' => '🎧'],
            ['name' => 'Jazz', 'category' => 'Musique', 'icon' => '🎷'],
            ['name' => 'Classique', 'category' => 'Musique', 'icon' => '🎻'],
            ['name' => 'Reggae', 'category' => 'Musique', 'icon' => '🎶'],

            // Cinéma
            ['name' => 'Action', 'category' => 'Cinéma', 'icon' => '💥'],
            ['name' => 'Comédie', 'category' => 'Cinéma', 'icon' => '😂'],
            ['name' => 'Drame', 'category' => 'Cinéma', 'icon' => '🎭'],
            ['name' => 'Science-fiction', 'category' => 'Cinéma', 'icon' => '👽'],
            ['name' => 'Horreur', 'category' => 'Cinéma', 'icon' => '👻'],
            ['name' => 'Romance', 'category' => 'Cinéma', 'icon' => '💕'],

            // Loisirs
            ['name' => 'Voyages', 'category' => 'Loisirs', 'icon' => '✈️'],
            ['name' => 'Cuisine', 'category' => 'Loisirs', 'icon' => '👨‍🍳'],
            ['name' => 'Lecture', 'category' => 'Loisirs', 'icon' => '📚'],
            ['name' => 'Jeux vidéo', 'category' => 'Loisirs', 'icon' => '🎮'],
            ['name' => 'Photographie', 'category' => 'Loisirs', 'icon' => '📷'],
            ['name' => 'Dessin', 'category' => 'Loisirs', 'icon' => '🎨'],
            ['name' => 'Jardinage', 'category' => 'Loisirs', 'icon' => '🌱'],
            ['name' => 'Bricolage', 'category' => 'Loisirs', 'icon' => '🔨'],

            // Culture
            ['name' => 'Art', 'category' => 'Culture', 'icon' => '🖼️'],
            ['name' => 'Théâtre', 'category' => 'Culture', 'icon' => '🎪'],
            ['name' => 'Musées', 'category' => 'Culture', 'icon' => '🏛️'],
            ['name' => 'Histoire', 'category' => 'Culture', 'icon' => '📜'],
            ['name' => 'Philosophie', 'category' => 'Culture', 'icon' => '🤔'],

            // Social
            ['name' => 'Sorties', 'category' => 'Social', 'icon' => '🍻'],
            ['name' => 'Restaurants', 'category' => 'Social', 'icon' => '🍽️'],
            ['name' => 'Fêtes', 'category' => 'Social', 'icon' => '🎉'],
            ['name' => 'Rencontres', 'category' => 'Social', 'icon' => '💑'],
        ];

        foreach ($interests as $interest) {
            Interest::create($interest);
        }
    }
}
