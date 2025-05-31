<?php

namespace App\Http\Controllers;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class AIController extends Controller
{
    public function generateParagraph()
    {
        $paragraph = Arr::random(config('fluency_texts'));

        return response()->json([
            'paragraph' => $paragraph
        ]);
    }

    public function generateFluencyParagraph(Request $request)
{
    $language = $request->input('language', 'English'); 

    $prompt = <<<EOT
Generate a short {$language} reading passage (3 to 4 sentences). It should be appropriate for students practicing reading fluency. Avoid difficult vocabulary. Keep the tone simple and educational.

Reply with just the paragraph, no headers, no explanations.
For example, make it about any sports,Football, players, university, old story, family, anything, most important thing is to always generate a new topic.
EOT;

    $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . config('services.openai.key'),
        'Content-Type' => 'application/json',
    ])->post('https://api.openai.com/v1/chat/completions', [
        'model' => 'gpt-3.5-turbo',
        'messages' => [
            ['role' => 'user', 'content' => $prompt],
        ],
        'temperature' => 0.7,
    ]);

    if (!$response->successful()) {
        return response()->json([
            'message' => 'OpenAI failed to generate paragraph',
            'error' => $response->body()
        ], 500);
    }

    $paragraph = trim($response->json('choices.0.message.content'));

    return response()->json([
        'paragraph' => $paragraph
    ]);
}

}

