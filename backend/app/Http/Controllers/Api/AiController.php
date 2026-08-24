<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Quiz;
use App\Services\AiContentService;
use Illuminate\Http\Request;

class AiController extends Controller
{
    protected AiContentService $aiService;

    public function __construct(AiContentService $aiService)
    {
        $this->aiService = $aiService;
    }

    // Génère et sauvegarde un résumé pour un cours
    public function summary(Request $request, Course $course)
    {
        if ($course->teacher_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        if (empty($course->content)) {
            return response()->json(['message' => 'Ce cours n\'a pas encore de contenu à résumer.'], 422);
        }

        $summary = $this->aiService->generateSummary($course->content);

        $course->update(['ai_summary' => $summary]);

        return response()->json([
            'message' => 'Résumé généré avec succès.',
            'ai_summary' => $summary,
        ]);
    }

    // Génère un quiz complet (questions + réponses) à partir du contenu d'un cours
    public function quiz(Request $request, Course $course)
    {
        if ($course->teacher_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        if (empty($course->content)) {
            return response()->json(['message' => 'Ce cours n\'a pas encore de contenu pour générer un quiz.'], 422);
        }

        $questions = $this->aiService->generateQuiz($course->content);

        if (! $questions) {
            return response()->json(['message' => 'La génération du quiz a échoué. Réessayez.'], 500);
        }

        // Création du quiz en base, marqué comme généré par IA
        $quiz = $course->quizzes()->create([
            'title' => 'Quiz généré par IA - ' . $course->title,
            'generated_by_ai' => true,
        ]);

        // Création des questions et réponses à partir du JSON généré
        foreach ($questions as $q) {
            $question = $quiz->questions()->create([
                'question_text' => $q['question_text'],
                'type' => 'single_choice',
            ]);

            foreach ($q['answers'] as $a) {
                $question->answers()->create([
                    'answer_text' => $a['answer_text'],
                    'is_correct' => $a['is_correct'],
                ]);
            }
        }

        return response()->json(
            $quiz->load('questions.answers'),
            201
        );
    }
}