<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AiContentService
{
    protected string $apiKey;
    protected string $model = 'gemini-3.6-flash';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key');
    }

    // Génère un résumé à partir du contenu d'un cours
    public function generateSummary(string $courseContent): string
    {
        $prompt = "Résume ce contenu de cours de façon claire et concise, en 5-8 phrases maximum, en français :\n\n" . $courseContent;

        $response = $this->callGemini($prompt);

        return $response ?? "Impossible de générer le résumé pour le moment.";
    }

    // Génère un quiz de 5 questions à partir du contenu d'un cours
    public function generateQuiz(string $courseContent): ?array
    {
        $prompt = "À partir du contenu de cours suivant, génère exactement 5 questions à choix unique en français. 
Réponds UNIQUEMENT avec un JSON valide, sans texte avant ni après, sans balises markdown, au format exact suivant :
{
  \"questions\": [
    {
      \"question_text\": \"...\",
      \"answers\": [
        {\"answer_text\": \"...\", \"is_correct\": true},
        {\"answer_text\": \"...\", \"is_correct\": false},
        {\"answer_text\": \"...\", \"is_correct\": false},
        {\"answer_text\": \"...\", \"is_correct\": false}
      ]
    }
  ]
}

Contenu du cours :
" . $courseContent;

        $response = $this->callGemini($prompt);

        if (! $response) {
            return null;
        }

        // Nettoie une éventuelle balise markdown ```json autour de la réponse
        $cleaned = preg_replace('/```json|```/', '', $response);
        $cleaned = trim($cleaned);

        $decoded = json_decode($cleaned, true);

        return $decoded['questions'] ?? null;
    }
        // Répond à une question libre posée par l'utilisateur
    public function chat(string $userMessage, array $history = []): string
    {
        $conversationContext = "Tu es un assistant pédagogique intégré à une plateforme d'apprentissage en ligne. Réponds de façon claire, concise et bienveillante en français, pour aider l'utilisateur dans son apprentissage.\n\n";

        foreach ($history as $exchange) {
            $conversationContext .= "Utilisateur : " . $exchange['message'] . "\n";
            $conversationContext .= "Assistant : " . $exchange['response'] . "\n\n";
        }

        $conversationContext .= "Utilisateur : " . $userMessage;

        $response = $this->callGemini($conversationContext);

        return $response ?? "Désolé, je n'ai pas pu répondre pour le moment. Réessayez.";
    }

    // Appel générique à l'API Gemini
    protected function callGemini(string $prompt): ?string
    {
        try {
            $response = Http::timeout(30)->post(
                "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}",
                [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt],
                            ],
                        ],
                    ],
                ]
            );

            if ($response->failed()) {
                \Log::error('Erreur API Gemini: ' . $response->body());
                return null;
            }

            return $response->json('candidates.0.content.parts.0.text');
        } catch (\Exception $e) {
            \Log::error('Exception API Gemini: ' . $e->getMessage());
            return null;
        }
    }
}