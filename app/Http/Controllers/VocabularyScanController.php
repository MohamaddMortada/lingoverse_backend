<?php

namespace App\Http\Controllers;

use App\Models\VocabularyScan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;


class VocabularyScanController extends Controller
{
    public function getAllVocabularyScans()
    {
        $vocabularyScans = VocabularyScan::with('user')->get();

        if ($vocabularyScans->isEmpty()) {
            return response()->json(['message' => 'No vocabulary scans found'], 404);
        }

        return response()->json($vocabularyScans, 200);
    }

    public function getVocabularyScan($id)
    {
        $vocabularyScan = VocabularyScan::find($id);

        if (!$vocabularyScan) {
            return response()->json(['message' => 'Vocabulary scan not found'], 404);
        }

        return response()->json($vocabularyScan, 200);
    }

    public function createVocabularyScan(Request $request)
{
    $request->validate([
        'user_id' => 'required|exists:users,id',
        'image_url' => 'required|string',
    ]);

    $prompt = "Describe the object shown in this image: " . $request->image_url . 
              ". Just reply with the object name, nothing else.";

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
            'message' => 'Failed to get vocabulary from AI',
            'details' => $response->body()
        ], 500);
    }

    $objectName = trim($response->json('choices.0.message.content'));

    $vocabularyScan = VocabularyScan::create([
        'user_id' => $request->user_id,
        'image_url' => $request->image_url,
        'translated_text' => $objectName,
    ]);

    return response()->json([
        'message' => 'Vocabulary scan created with AI',
        'vocabulary_scan' => $vocabularyScan
    ], 201);
}

    public function updateVocabularyScan(Request $request, $id)
    {
        $vocabularyScan = VocabularyScan::find($id);

        if (!$vocabularyScan) {
            return response()->json(['message' => 'Vocabulary scan not found'], 404);
        }

        $request->validate([
            'image_url' => 'sometimes|required|string',
            'translated_text' => 'sometimes|required|string',
        ]);

        $vocabularyScan->update([
            'image_url' => $request->image_url ?? $vocabularyScan->image_url,
            'translated_text' => $request->translated_text ?? $vocabularyScan->translated_text,
        ]);

        return response()->json([
            'message' => 'Vocabulary scan updated successfully',
            'vocabulary_scan' => $vocabularyScan
        ], 200);
    }

    public function deleteVocabularyScan($id)
    {
        $vocabularyScan = VocabularyScan::find($id);

        if (!$vocabularyScan) {
            return response()->json(['message' => 'Vocabulary scan not found'], 404);
        }

        $vocabularyScan->delete();

        return response()->json(['message' => 'Vocabulary scan deleted successfully'], 200);
    }
}
