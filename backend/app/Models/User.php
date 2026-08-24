<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $hidden = [
    'password',
    'remember_token',
];

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    public function courses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Course::class, 'teacher_id');
    }

    public function quizAttempts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }
    // ... reste du fichier inchangé (hidden, casts, etc.)
    public function badges(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
{
    return $this->belongsToMany(Badge::class, 'user_badges')
        ->withPivot('date_obtained')
        ->withTimestamps();
}
}