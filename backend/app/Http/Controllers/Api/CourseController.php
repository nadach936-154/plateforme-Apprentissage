<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CourseController extends Controller
{
    // Liste de tous les cours (accessible à tous les utilisateurs connectés)
    public function index()
    {
        $courses = Course::with(['teacher', 'chapters', 'quizzes'])->latest()->get();

        return CourseResource::collection($courses);
    }

    // Créer un cours (enseignant uniquement)
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'content' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $course = Course::create([
            'title' => $request->title,
            'description' => $request->description,
            'category' => $request->category,
            'content' => $request->content,
            'teacher_id' => $request->user()->id,
        ]);

        return new CourseResource($course->load(['teacher', 'chapters', 'quizzes']));
    }

    // Afficher un cours précis
    public function show(Course $course)
    {
        return new CourseResource($course->load(['teacher', 'chapters', 'quizzes']));
    }

    // Modifier un cours (enseignant propriétaire uniquement)
    public function update(Request $request, Course $course)
    {
        if ($course->teacher_id !== $request->user()->id) {
            return response()->json(['message' => 'Vous ne pouvez modifier que vos propres cours.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'content' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $course->update($request->only(['title', 'description', 'category', 'content']));

        return new CourseResource($course->load(['teacher', 'chapters', 'quizzes']));
    }

    // Supprimer un cours (enseignant propriétaire uniquement)
    public function destroy(Request $request, Course $course)
    {
        if ($course->teacher_id !== $request->user()->id) {
            return response()->json(['message' => 'Vous ne pouvez supprimer que vos propres cours.'], 403);
        }

        $course->delete();

        return response()->json(['message' => 'Cours supprimé avec succès.']);
    }
}