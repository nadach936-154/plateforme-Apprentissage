<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        $badges = [
            [
                'name' => 'Premier quiz réussi',
                'description' => 'Félicitations pour votre premier quiz complété !',
                'icon' => '🏆',
                'condition' => '1 quiz complété',
            ],
            [
                'name' => '5 quiz complétés',
                'description' => 'Vous avez complété 5 quiz, continuez comme ça !',
                'icon' => '⭐',
                'condition' => '5 quiz complétés',
            ],
            [
                'name' => 'Niveau 5 atteint',
                'description' => 'Vous avez atteint le niveau 5 !',
                'icon' => '🚀',
                'condition' => 'Niveau 5',
            ],
        ];

        foreach ($badges as $badge) {
            Badge::updateOrCreate(
                ['name' => $badge['name']],
                $badge
            );
        }
    }
}