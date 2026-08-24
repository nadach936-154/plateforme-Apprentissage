<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Services\AiContentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    protected AiContentService $aiService;

    public function __construct(AiContentService $aiService)
    {
        $this->aiService = $aiService;
    }

    // Envoyer un message au chatbot et recevoir une réponse
    public function send(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Récupère les 5 derniers échanges pour donner du contexte à l'IA
        $recentHistory = ChatMessage::where('user_id', $request->user()->id)
            ->latest()
            ->take(5)
            ->get()
            ->reverse()
            ->map(fn ($msg) => [
                'message' => $msg->message,
                'response' => $msg->response,
            ])
            ->values()
            ->toArray();

        $response = $this->aiService->chat($request->message, $recentHistory);

        $chatMessage = ChatMessage::create([
            'user_id' => $request->user()->id,
            'message' => $request->message,
            'response' => $response,
        ]);

        return response()->json($chatMessage, 201);
    }

    // Récupérer l'historique de conversation de l'utilisateur connecté
    public function history(Request $request)
    {
        $messages = ChatMessage::where('user_id', $request->user()->id)
            ->oldest()
            ->get();

        return response()->json($messages);
    }
}