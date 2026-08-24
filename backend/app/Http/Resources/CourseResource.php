<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category,
            'content' => $this->content,
            'ai_summary' => $this->ai_summary,
            'teacher' => [
                'id' => $this->teacher->id,
                'name' => $this->teacher->name,
            ],
            'chapters' => $this->chapters,
            'quizzes' => $this->quizzes,
            'created_at' => $this->created_at,
        ];
    }
}