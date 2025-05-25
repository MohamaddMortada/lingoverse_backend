<?php

namespace App\Http\Controllers;

use App\Models\ChatSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;


class ChatSessionController extends Controller
{
    public function getAllChatSessions()
    {
        $chatSessions = ChatSession::all();

        if ($chatSessions->isEmpty()) {
            return response()->json(['message' => 'No chat sessions found'], 404);
        }

        return response()->json($chatSessions, 200);
    }

    public function getChatSession($chat_id)
    {
        $chatSession = ChatSession::find($chat_id);

        if (!$chatSession) {
            return response()->json(['message' => 'Chat session not found'], 404);
        }

        return response()->json($chatSession, 200);
    }

    public function createChatSession(Request $request)
{
    $request->validate([
        'user_id' => 'required|exists:users,id',
        'session_date' => 'required|date',
        'ai_feedback' => 'required|string',
        'duration' => 'required|integer',
    ]);

    $chatSession = ChatSession::create([
        'user_id' => $request->user_id,
        'session_date' => $request->session_date,
        'ai_feedback' => $request->ai_feedback,
        'duration' => $request->duration,
    ]);

    if ($chatSession) {
        return response()->json([
            'message' => 'Chat session created successfully',
            'chat_session' => $chatSession
        ], 201); 
    }

    return response()->json(['message' => 'Error while creating chat session'], 500);
}


    public function updateChatSession(Request $request, $chat_id)
    {
        $chatSession = ChatSession::find($chat_id);

        if (!$chatSession) {
            return response()->json(['message' => 'Chat session not found'], 404);
        }

        $request->validate([
            'session_date' => 'nullable|date',
            'ai_feedback' => 'nullable|string',
            'duration' => 'nullable|integer',
        ]);

        $chatSession->update([
            'session_date' => $request->session_date ?? $chatSession->session_date,
            'ai_feedback' => $request->ai_feedback ?? $chatSession->ai_feedback,
            'duration' => $request->duration ?? $chatSession->duration,
        ]);

        return response()->json([
            'message' => 'Chat session updated successfully',
            'chat_session' => $chatSession
        ], 200);
    }

    public function deleteChatSession($chat_id)
    {
        $chatSession = ChatSession::find($chat_id);

        if (!$chatSession) {
            return response()->json(['message' => 'Chat session not found'], 404);
        }

        $chatSession->delete();

        return response()->json(['message' => 'Chat session deleted successfully'], 200);
    }

    public function chatWithAI(Request $request)
{
    $request->validate([
        'user_id' => 'required|exists:users,id',
        'message' => 'required|string',
        'language' => 'nullable|string',
    ]);

    $userMessage = $request->message;
    $language = $request->language ?? 'English';

    try {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.openai.key'),
            'Content-Type' => 'application/json',
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'system', 'content' => "You are a helpful language tutor. Reply only in $language."],
                ['role' => 'user', 'content' => $userMessage],
            ],
            'temperature' => 0.7,
        ]);

        $data = $response->json();
        $aiReply = $data['choices'][0]['message']['content'] ?? 'Sorry, I could not generate a response.';

        $chatSession = ChatSession::create([
            'user_id' => $request->user_id,
            'session_date' => Carbon::now(),
            'ai_feedback' => $aiReply,
            'duration' => 0,
        ]);

        return response()->json([
            'message' => 'AI replied successfully',
            'reply' => $aiReply,
            'chat_session_id' => $chatSession->id
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Error communicating with OpenAI',
            'error' => $e->getMessage()
        ], 500);
    }
}


}
