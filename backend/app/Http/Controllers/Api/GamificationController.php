<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class GamificationController extends Controller
{
    // Classement des étudiants par XP décroissant
    public function leaderboard()
    {
        $leaderboard = User::where('role', 'student')
            ->orderByDesc('xp')
            ->take(20)
            ->get(['id', 'name', 'xp', 'level']);

        return response()->json($leaderboard);
    }

    // Badges de l'utilisateur connecté (débloqués + verrouillés)
    public function myBadges(Request $request)
    {
        $user = $request->user();

        $unlockedIds = $user->badges()->pluck('badges.id');

        $allBadges = \App\Models\Badge::all()->map(function ($badge) use ($unlockedIds, $user) {
            $unlocked = $unlockedIds->contains($badge->id);

            return [
                'id' => $badge->id,
                'name' => $badge->name,
                'description' => $badge->description,
                'icon' => $badge->icon,
                'unlocked' => $unlocked,
                'date_obtained' => $unlocked
                    ? $user->badges()->where('badges.id', $badge->id)->first()->pivot->date_obtained
                    : null,
            ];
        });

        return response()->json($allBadges);
    }
}