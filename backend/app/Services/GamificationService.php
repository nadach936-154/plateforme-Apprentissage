<?php

namespace App\Services;

use App\Models\User;
use App\Models\Badge;

class GamificationService
{
    // Ajoute de l'XP à un utilisateur et met à jour son niveau
    public function addXp(User $user, int $xp): void
    {
        $user->xp += $xp;
        $user->level = intdiv($user->xp, 100) + 1;
        $user->save();

        $this->checkBadges($user);
    }

    // Vérifie et attribue les badges mérités par l'utilisateur
    protected function checkBadges(User $user): void
    {
        $attemptsCount = $user->quizAttempts()->count();

        // Badge "Premier quiz réussi"
        if ($attemptsCount === 1) {
            $this->awardBadge($user, 'Premier quiz réussi');
        }

        // Badge "5 quiz complétés"
        if ($attemptsCount === 5) {
            $this->awardBadge($user, '5 quiz complétés');
        }

        // Badge "Niveau 5 atteint"
        if ($user->level >= 5) {
            $this->awardBadge($user, 'Niveau 5 atteint');
        }
    }

    // Attribue un badge à un utilisateur s'il ne l'a pas déjà
    protected function awardBadge(User $user, string $badgeName): void
    {
        $badge = Badge::where('name', $badgeName)->first();

        if (! $badge) {
            return; // le badge n'existe pas encore en base, on ignore
        }

        if (! $user->badges()->where('badge_id', $badge->id)->exists()) {
            $user->badges()->attach($badge->id, ['date_obtained' => now()]);
        }
    }
}