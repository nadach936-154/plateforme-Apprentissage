<?php
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\AiController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\ChapterController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\AnswerController;
use App\Http\Controllers\Api\QuizAttemptController;
use App\Http\Controllers\Api\GamificationController;
// Ces 8 lignes "use" importent les classes des contrôleurs pour pouvoir
// les appeler simplement par leur nom (ex: AuthController) plus bas,
// au lieu d'écrire le chemin complet à chaque fois.

// ROUTES PUBLIQUES — accessibles sans être connecté
Route::post('/register', [AuthController::class, 'register']);
// Inscription : n'importe qui peut créer un compte, donc pas de middleware.
Route::post('/login', [AuthController::class, 'login']);
// Connexion : idem, accessible sans être déjà authentifié.

// ROUTES PROTÉGÉES — nécessitent un token Sanctum valide (utilisateur connecté)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/leaderboard', [GamificationController::class, 'leaderboard']);
Route::get('/my-badges', [GamificationController::class, 'myBadges']);
Route::post('/chat', [ChatController::class, 'send']);
Route::get('/chat/history', [ChatController::class, 'history']);

    Route::post('/logout', [AuthController::class, 'logout']);
    // Déconnexion : logique, il faut être connecté pour se déconnecter.
    Route::get('/me', [AuthController::class, 'me']);
    // Récupère les infos de l'utilisateur actuellement connecté.

    // --- LECTURE : accessible à TOUS les connectés (étudiants ET enseignants) ---
    Route::get('/courses', [CourseController::class, 'index']);
    // Liste de tous les cours — un étudiant doit pouvoir la consulter.
    Route::get('/courses/{course}', [CourseController::class, 'show']);
    // Détail d'un cours précis.
    Route::get('/courses/{course}/chapters', [ChapterController::class, 'index']);
    // Liste des chapitres d'un cours — un étudiant doit pouvoir lire le contenu.
    Route::get('/courses/{course}/quizzes', [QuizController::class, 'index']);
    // Liste des quiz disponibles pour un cours.
    Route::get('/quizzes/{quiz}', [QuizController::class, 'show']);
    // Détail d'un quiz (questions + réponses) — nécessaire pour qu'un étudiant
    // puisse voir les questions avant/pendant qu'il passe le quiz.

    // --- ACTIONS ÉTUDIANTES : accessibles à tout connecté, PAS besoin d'être enseignant ---
    Route::post('/quizzes/{quiz}/attempt', [QuizAttemptController::class, 'store']);
    // Un étudiant soumet ses réponses à un quiz. ⚠️ CORRIGÉ : sortie du groupe
    // role:teacher, car c'est une action réservée aux étudiants, pas aux profs.
    Route::get('/my-attempts', [QuizAttemptController::class, 'index']);
    // Historique des tentatives de quiz de l'utilisateur connecté. ⚠️ CORRIGÉ aussi.

    // --- ÉCRITURE : réservée aux enseignants uniquement ---
    Route::middleware('role:teacher')->group(function () {
        Route::post('/courses/{course}/generate-summary', [AiController::class, 'summary']);
        Route::post('/courses/{course}/generate-quiz', [AiController::class, 'quiz']);
        Route::post('/courses', [CourseController::class, 'store']);
        // Créer un cours — seul un enseignant peut le faire.
        Route::put('/courses/{course}', [CourseController::class, 'update']);
        // Modifier un cours existant.
        Route::delete('/courses/{course}', [CourseController::class, 'destroy']);
        // Supprimer un cours.

        Route::post('/courses/{course}/chapters', [ChapterController::class, 'store']);
        // Ajouter un chapitre à un cours.
        Route::put('/courses/{course}/chapters/{chapter}', [ChapterController::class, 'update']);
        // Modifier un chapitre.
        Route::delete('/courses/{course}/chapters/{chapter}', [ChapterController::class, 'destroy']);
        // Supprimer un chapitre.

        Route::post('/courses/{course}/quizzes', [QuizController::class, 'store']);
        // Créer un quiz pour un cours.
        Route::put('/quizzes/{quiz}', [QuizController::class, 'update']);
        // Modifier un quiz.
        Route::delete('/quizzes/{quiz}', [QuizController::class, 'destroy']);
        // Supprimer un quiz.

        Route::post('/quizzes/{quiz}/questions', [QuestionController::class, 'store']);
        // Ajouter une question à un quiz.
        Route::put('/quizzes/{quiz}/questions/{question}', [QuestionController::class, 'update']);
        // Modifier une question.
        Route::delete('/quizzes/{quiz}/questions/{question}', [QuestionController::class, 'destroy']);
        // Supprimer une question.

        Route::post('/questions/{question}/answers', [AnswerController::class, 'store']);
        // Ajouter une réponse possible à une question.
        Route::put('/questions/{question}/answers/{answer}', [AnswerController::class, 'update']);
        // Modifier une réponse.
        Route::delete('/questions/{question}/answers/{answer}', [AnswerController::class, 'destroy']);
        // Supprimer une réponse.

    }); // fin du groupe role:teacher
}); // fin du groupe auth:sanctum