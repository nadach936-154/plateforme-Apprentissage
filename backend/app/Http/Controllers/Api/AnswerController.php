<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AnswerController extends Controller
{
    // Ajouter une réponse à une question
    public function store(Request $request, Question $question)
    {
        if ($question->quiz->course->teacher_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'answer_text' => 'required|string',
            'is_correct' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $answer = $question->answers()->create([
            'answer_text' => $request->answer_text,
            'is_correct' => $request->is_correct ?? false,
        ]);

        return response()->json($answer, 201);
    }

    // Modifier une réponse
    public function update(Request $request, Question $question, Answer $answer)
    {
        if ($question->quiz->course->teacher_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $answer->update($request->only(['answer_text', 'is_correct']));

        return response()->json($answer);
    }

    // Supprimer une réponse
    public function destroy(Request $request, Question $question, Answer $answer)
    {
        if ($question->quiz->course->teacher_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $answer->delete();

        return response()->json(['message' => 'Réponse supprimée.']);
    }
}
