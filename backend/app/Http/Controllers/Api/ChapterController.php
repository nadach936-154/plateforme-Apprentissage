<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChapterController extends Controller
{
    // Liste des chapitres d'un cours précis
    public function index(Course $course)
    {
        return response()->json(
            $course->chapters()->orderBy('order')->get()
        );
    }

    // Ajouter un chapitre à un cours (enseignant propriétaire uniquement)
    public function store(Request $request, Course $course)
    {
        if ($course->teacher_id !== $request->user()->id) {
            return response()->json(['message' => 'Vous ne pouvez modifier que vos propres cours.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $chapter = $course->chapters()->create([
            'title' => $request->title,
            'content' => $request->content,
            'order' => $request->order ?? $course->chapters()->count(),
        ]);

        return response()->json($chapter, 201);
    }

    // Modifier un chapitre
    public function update(Request $request, Course $course, Chapter $chapter)
    {
        if ($course->teacher_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'content' => 'nullable|string',
            'order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $chapter->update($request->only(['title', 'content', 'order']));

        return response()->json($chapter);
    }

    // Supprimer un chapitre
    public function destroy(Request $request, Course $course, Chapter $chapter)
    {
        if ($course->teacher_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $chapter->delete();

        return response()->json(['message' => 'Chapitre supprimé.']);
    }
}