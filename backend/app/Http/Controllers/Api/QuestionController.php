<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QuestionController extends Controller
{
    // Ajouter une question à un quiz
    public function store(Request $request, Quiz $quiz)
    {
        if ($quiz->course->teacher_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'question_text' => 'required|string',
            'type' => 'nullable|in:single_choice,multiple_choice',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $question = $quiz->questions()->create([
            'question_text' => $request->question_text,
            'type' => $request->type ?? 'single_choice',
        ]);

        return response()->json($question, 201);
    }

    // Modifier une question
    public function update(Request $request, Quiz $quiz, Question $question)
    {
        if ($quiz->course->teacher_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $question->update($request->only(['question_text', 'type']));

        return response()->json($question);
    }

    // Supprimer une question
    public function destroy(Request $request, Quiz $quiz, Question $question)
    {
        if ($quiz->course->teacher_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $question->delete();

        return response()->json(['message' => 'Question supprimée.']);
    }
}