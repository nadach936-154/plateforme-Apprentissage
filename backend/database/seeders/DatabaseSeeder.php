<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Course;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(BadgeSeeder::class);

        $teacher = User::factory()->create([
            'name' => 'Ahmed Enseignant',
            'email' => 'ahmed@test.com',
            'password' => Hash::make('password123'),
            'role' => 'teacher',
        ]);

        $student = User::factory()->create([
            'name' => 'Nada Étudiante',
            'email' => 'nada@test.com',
            'password' => Hash::make('password123'),
            'role' => 'student',
        ]);

        Course::create([
            'title' => 'Introduction à Laravel',
            'description' => 'Un cours pour apprendre les bases de Laravel',
            'category' => 'Développement Web',
            'content' => 'Laravel est un framework PHP moderne qui facilite le développement web...',
            'teacher_id' => $teacher->id,
        ]);
    }
}