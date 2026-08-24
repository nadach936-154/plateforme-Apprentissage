<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Services\GamificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QuizAttemptController extends Controller
{
    protected GamificationService $gamification;

    public function __construct(GamificationService $gamification)
    {
        $this->gamification = $gamification;
    }

    // L'étudiant soumet ses réponses à un quiz
    public function store(Request $request, Quiz $quiz)
    {
        $validator = Validator::make($request->all(), [
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:questions,id',
            'answers.*.answer_id' => 'required|exists:answers,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $totalQuestions = $quiz->questions()->count();
        $correctCount = 0;

        foreach ($request->answers as $submitted) {
            $isCorrect = Answer::where('id', $submitted['answer_id'])
                ->where('question_id', $submitted['question_id'])
                ->where('is_correct', true)
                ->exists();

            if ($isCorrect) {
                $correctCount++;
            }
        }

        $score = $totalQuestions > 0
            ? round(($correctCount / $totalQuestions) * 10)
            : 0;

        $xpEarned = $score * 10;

        $attempt = QuizAttempt::create([
            'user_id' => $request->user()->id,
            'quiz_id' => $quiz->id,
            'score' => $score,
            'xp_earned' => $xpEarned,
            'completed_at' => now(),
        ]);

        // Délègue toute la logique XP/niveau/badges au service dédié
        $this->gamification->addXp($request->user(), $xpEarned);

        $user = $request->user()->fresh(); // recharge l'utilisateur avec les valeurs à jour

        return response()->json([
            'message' => 'Quiz terminé.',
            'score' => $score,
            'total_questions' => $totalQuestions,
            'correct_answers' => $correctCount,
            'xp_earned' => $xpEarned,
            'new_total_xp' => $user->xp,
            'new_level' => $user->level,
            'badges_debloques' => $user->badges()->latest('user_badges.created_at')->get(['badges.name']),
        ], 201);
    }

    // Historique des tentatives de l'utilisateur connecté
    public function index(Request $request)
    {
        $attempts = QuizAttempt::where('user_id', $request->user()->id)
            ->with('quiz')
            ->latest()
            ->get();

        return response()->json($attempts);
    }
}