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
        'native' => 'required|string|max:50',
        'level' => 'required|string|max:50',
    ]);

    $language = $request->language;
    $native = $request->native;
    $level = $request->level;

$prompt = <<<EOT
You are a professional language teacher helping students learn a new language.

The student’s native language is "{$native}" and they are learning "{$language}" at a "{$level}" level.

Your job is to generate a challenge set that helps them improve vocabulary, grammar, fluency, and comprehension in "{$language}".

**Instructions:**

1. Start with a short, meaningful topic title (1–3 words) in "{$language}" (e.g., "الماضي البسيط", "أسماء الحيوانات", "شراء الطعام").
2. Then, create 3–5 **logical, valuable** learning tasks.
3. **Each task instruction must be written in "{$native}"**, and the **expected student response must be in "{$language}"**.
4. Do **not** include translation requests or examples where the source and target language are the same.
5. Use task types such as:
   - Compose a sentence using specific vocabulary
   - Answer a question in "{$language}"
   - Fill-in-the-blank using the correct word/tense
   - Respond to a short scenario
   - Describe a picture or daily situation
6. Avoid repetition, filler, or meaningless exercises.

**Return only in this exact JSON format (no explanation or markdown):**

{
  "title": "Title in {$language}",
  "challenges": [
    "Instruction 1 in {$native} (expect answer in {$language})",
    "Instruction 2 in {$native} (expect answer in {$language})",
    ...
  ]
}
EOT;



    $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . config('services.openai.key'),
        'Content-Type' => 'application/json',
    ])->post('https://api.openai.com/v1/chat/completions', [
'model' => 'gpt-4',
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
