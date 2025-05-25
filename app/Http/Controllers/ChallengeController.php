<?php

namespace App\Http\Controllers;

use App\Models\Challenge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;



class ChallengeController extends Controller {
    public function getChallenges()
    {
        $challenges = Challenge::all();
        
        if ($challenges->isEmpty()) {
            return response()->json(['message' => 'No challenges found'], 404);
        }
        
        return response()->json($challenges, 200);
    }
    public function getChallenge($id) {
        $challenge = Challenge::find($id);

        if (!$challenge) {
            return response()->json(['message' => 'Challenge not found'], 404);
        }

        return response()->json($challenge, 200);
    }

    public function createChallenge(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'difficulty_level' => 'required|string|max:50',
        ]);

        $challenge = Challenge::create([
            'title' => $request->title,
            'description' => $request->description,
            'difficulty_level' => $request->difficulty_level,
        ]);

        if (!$challenge) {
            return response()->json(['message' => 'Error while creating challenge'], 500);
        }

        return response()->json([
            'message' => 'Challenge created successfully',
            'challenge' => $challenge
        ], 201);
    }

    public function updateChallenge(Request $request, $id) {
        $challenge = Challenge::find($id);

        if (!$challenge) {
            return response()->json(['message' => 'Challenge not found'], 404);
        }

        $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'difficulty_level' => 'nullable|string|max:50',
        ]);

        $challenge->update([
            'title' => $request->title ?? $challenge->title,
            'description' => $request->description ?? $challenge->description,
            'difficulty_level' => $request->difficulty_level ?? $challenge->difficulty_level,
        ]);

        return response()->json([
            'message' => 'Challenge updated successfully',
            'challenge' => $challenge
        ], 200);
    }

    public function deleteChallenge($id) {
        $challenge = Challenge::find($id);

        if (!$challenge) {
            return response()->json(['message' => 'Challenge not found'], 404);
        }

        $challenge->delete();

        return response()->json(['message' => 'Challenge deleted successfully'], 200);
    }

    public function generateAIChallenges(Request $request)
{
    $request->validate([
        'language' => 'required|string|max:50',
        'level' => 'required|string|max:50',
    ]);

    $language = $request->language;
    $level = $request->level;

    $prompt = <<<EOT
You are a professional language tutor helping students learn a new language.

Create an educational challenge set for a student learning **{$language}** at a **{$level}** level. The goal is to improve vocabulary, grammar, comprehension, and fluency in {$language}.

First, give a short topic title (1–3 words) in {$language} — this should reflect the learning objective (e.g., "Food Vocabulary", "Present Tense", "Daily Routines").

Then, create 3 to 5 **educational tasks** in {$language} that help the student practice and improve their language skills related to that topic. The tasks can include:
- Translating short phrases or words
- Creating sentences using specific words
- Identifying grammar or vocabulary mistakes
- Matching words with definitions
- Describing images or situations
- Short question-answer exercises

Write everything in **{$language}**, including instructions and tasks. Avoid English unless necessary.

Return ONLY in this **strict JSON format**:
{
  "title": "A short topic title in {$language}",
  "challenges": [
    "Challenge description 1 in {$language}",
    "Challenge description 2 in {$language}",
    ...
  ]
}
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
            'message' => 'OpenAI request failed',
            'error' => $response->body()
        ], 500);
    }

    $content = json_decode($response->json('choices.0.message.content'), true);

    if (!$content || !isset($content['title'], $content['challenges'])) {
        return response()->json([
            'message' => 'Invalid response format from OpenAI',
            'raw' => $response->json('choices.0.message.content')
        ], 500);
    }

    $created = [];

    foreach ($content['challenges'] as $desc) {
        $created[] = Challenge::create([
            'title' => $content['title'],
            'description' => $desc,
            'difficulty_level' => $level,
        ]);
    }

    return response()->json([
        'message' => 'AI-generated challenges created successfully',
        'language' => $language,
        'level' => $level,
        'title' => $content['title'],
        'challenges' => $created
    ], 201);
}

}
