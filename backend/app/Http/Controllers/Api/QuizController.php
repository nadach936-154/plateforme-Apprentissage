<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QuizController extends Controller
{
    // Liste des quiz d'un cours
    public function index(Course $course)
    {
        return response()->json(
            $course->quizzes()->with('questions.answers')->get()
        );
    }

    // Créer un quiz manuellement (enseignant propriétaire uniquement)
    public function store(Request $request, Course $course)
    {
        if ($course->teacher_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $quiz = $course->quizzes()->create([
            'title' => $request->title,
            'generated_by_ai' => false,
        ]);

        return response()->json($quiz, 201);
    }

    // Détail d'un quiz avec ses questions et réponses
    public function show(Quiz $quiz)
    {
        return response()->json(
            $quiz->load('questions.answers')
        );
    }

    // Modifier un quiz
    public function update(Request $request, Quiz $quiz)
    {
        if ($quiz->course->teacher_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $quiz->update($request->only(['title']));

        return response()->json($quiz);
    }

    // Supprimer un quiz
    public function destroy(Request $request, Quiz $quiz)
    {
        if ($quiz->course->teacher_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $quiz->delete();

        return response()->json(['message' => 'Quiz supprimé.']);
    }
}