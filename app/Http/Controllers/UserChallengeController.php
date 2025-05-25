<?php

namespace App\Http\Controllers;

use App\Models\UserChallenge;
use App\Models\Challenge;
use Illuminate\Http\Request;
use Carbon\Carbon; 
use Illuminate\Support\Facades\Http;



class UserChallengeController extends Controller
{
    public function getAllUserChallenges()
    {
        $userChallenges = UserChallenge::with(['user', 'challenge'])->get();

        if ($userChallenges->isEmpty()) {
            return response()->json(['message' => 'No user challenges found'], 404);
        }

        return response()->json($userChallenges, 200);
    }

    public function getUserChallengeByUser($userId)
    {
        $userChallenges = UserChallenge::where('user_id', $userId)->with('challenge')->get();

        if ($userChallenges->isEmpty()) {
            return response()->json(['message' => 'No challenges found for this user'], 404);
        }

        return response()->json($userChallenges, 200);
    }

    public function createUserChallenge(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'challenge_id' => 'required|exists:challenges,id',
            'status' => 'required|string|max:50',
            'score' => 'required|integer',
        ]);

        $userChallenge = UserChallenge::create([
            'user_id' => $request->user_id,
            'challenge_id' => $request->challenge_id,
            'status' => $request->status,
            'score' => $request->score,
        ]);

        return response()->json([
            'message' => 'User challenge created successfully',
            'user_challenge' => $userChallenge
        ], 201);
    }

    public function updateUserChallenge(Request $request, $id)
    {
        $userChallenge = UserChallenge::find($id);

        if (!$userChallenge) {
            return response()->json(['message' => 'User challenge not found'], 404);
        }

        $request->validate([
            'status' => 'sometimes|required|string|max:50',
            'score' => 'sometimes|required|integer',
        ]);

        $userChallenge->update([
            'status' => $request->status ?? $userChallenge->status,
            'score' => $request->score ?? $userChallenge->score,
        ]);

        return response()->json([
            'message' => 'User challenge updated successfully',
            'user_challenge' => $userChallenge
        ], 200);
    }

    public function deleteUserChallenge($id)
    {
        $userChallenge = UserChallenge::find($id);

        if (!$userChallenge) {
            return response()->json(['message' => 'User challenge not found'], 404);
        }

        $userChallenge->delete();

        return response()->json(['message' => 'User challenge deleted successfully'], 200);
    }

    public function getTodayChallenges($userId)
    {
        $today = Carbon::today();
    
        $existing = UserChallenge::with('challenge')
            ->where('user_id', $userId)
            ->whereDate('created_at', $today)
            ->get();
    
        if ($existing->isNotEmpty()) {
            return response()->json($existing, 200);
        }
    
        $randomChallenges = Challenge::inRandomOrder()->limit(3)->get();
    
        $assigned = [];
    
        foreach ($randomChallenges as $challenge) {
            $assigned[] = UserChallenge::create([
                'user_id' => $userId,
                'challenge_id' => $challenge->id,
                'status' => 'pending',
                'score' => 0,
            ]);
        }
    
        return response()->json($assigned, 201);
    }

    public function completeChallenge(Request $request)
{
    $request->validate([
        'user_challenge_id' => 'required|exists:user_challenges,user_challenge_id',
        'score' => 'required|integer|min:0|max:100',
    ]);

    $challenge = UserChallenge::find($request->user_challenge_id);

    $challenge->update([
        'status' => 'completed',
        'score' => $request->score,
    ]);

    return response()->json([
        'message' => 'Challenge marked as completed',
        'user_challenge' => $challenge,
    ], 200);
}


public function evaluateAnswer(Request $request)
{
    $request->validate([
        'user_id' => 'required|exists:users,id',
        'challenge_id' => 'required|exists:challenges,id',
        'user_answer' => 'required|string',
    ]);

    $challenge = Challenge::find($request->challenge_id);

    $prompt = <<<EOT
You are an English language tutor. A student is answering a challenge.

Challenge:
"{$challenge->description}"

Student's Answer:
"{$request->user_answer}"

Evaluate the answer and reply ONLY in strict JSON format like this:
{
  "is_correct": true or false,
  "score": number from 0 to 100,
  "feedback": "short explanation or correction"
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
        'temperature' => 0.6,
    ]);

    if (!$response->successful()) {
        return response()->json([
            'message' => 'OpenAI request failed',
            'details' => $response->body()
        ], 500);
    }

    $evaluation = json_decode($response->json('choices.0.message.content'), true);

    $userChallenge = UserChallenge::where('user_id', $request->user_id)
        ->where('challenge_id', $request->challenge_id)
        ->orderByDesc('created_at')
        ->first();

    if ($userChallenge) {
        $userChallenge->update([
            'status' => 'completed',
            'score' => $evaluation['score'] ?? 0,
        ]);
    } else {
        $userChallenge = UserChallenge::create([
            'user_id' => $request->user_id,
            'challenge_id' => $request->challenge_id,
            'status' => 'completed',
            'score' => $evaluation['score'] ?? 0,
        ]);
    }

    \Log::info("Challenge result stored", [
        'user_challenge_id' => $userChallenge->user_challenge_id ?? null,
        'user_id' => $request->user_id,
        'challenge_id' => $request->challenge_id,
        'status' => 'completed',
        'score' => $evaluation['score'] ?? 0,
    ]);

    return response()->json([
        'message' => 'Answer evaluated and challenge saved',
        'result' => $evaluation
    ]);
}

   
}
