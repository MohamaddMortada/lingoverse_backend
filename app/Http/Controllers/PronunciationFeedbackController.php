<?php

namespace App\Http\Controllers;

use App\Models\PronunciationFeedback;
use Illuminate\Http\Request;

class PronunciationFeedbackController extends Controller
{
    public function getAllFeedback()
    {
        $feedback = PronunciationFeedback::with('user')->get();

        if ($feedback->isEmpty()) {
            return response()->json(['message' => 'No pronunciation feedback found'], 404);
        }

        return response()->json($feedback, 200);
    }

    public function getFeedbackByUser($userId)
    {
        $feedback = PronunciationFeedback::where('user_id', $userId)->get();

        if ($feedback->isEmpty()) {
            return response()->json(['message' => 'No pronunciation feedback found for this user'], 404);
        }

        return response()->json($feedback, 200);
    }

    public function createFeedback(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'audio_url' => 'required|string',
            'accuracy_score' => 'required|integer',
            'mistakes_highlighted' => 'required|string',
        ]);

        $feedback = PronunciationFeedback::create([
            'user_id' => $request->user_id,
            'audio_url' => $request->audio_url,
            'accuracy_score' => $request->accuracy_score,
            'mistakes_highlighted' => $request->mistakes_highlighted,
        ]);

        return response()->json([
            'message' => 'Pronunciation feedback created successfully',
            'feedback' => $feedback
        ], 201); 
    }

    public function updateFeedback(Request $request, $id)
    {
        $feedback = PronunciationFeedback::find($id);

        if (!$feedback) {
            return response()->json(['message' => 'Pronunciation feedback not found'], 404);
        }

        $request->validate([
            'audio_url' => 'sometimes|required|string',
            'accuracy_score' => 'sometimes|required|integer',
            'mistakes_highlighted' => 'sometimes|required|string',
        ]);

        $feedback->update([
            'audio_url' => $request->audio_url ?? $feedback->audio_url,
            'accuracy_score' => $request->accuracy_score ?? $feedback->accuracy_score,
            'mistakes_highlighted' => $request->mistakes_highlighted ?? $feedback->mistakes_highlighted,
        ]);

        return response()->json([
            'message' => 'Pronunciation feedback updated successfully',
            'feedback' => $feedback
        ], 200);
    }

    public function deleteFeedback($id)
    {
        $feedback = PronunciationFeedback::find($id);

        if (!$feedback) {
            return response()->json(['message' => 'Pronunciation feedback not found'], 404);
        }

        $feedback->delete();

        return response()->json(['message' => 'Pronunciation feedback deleted successfully'], 200);
    }


    public function analyzeFluency(Request $request)
{
    $request->validate([
        'user_id' => 'required|exists:users,id',
        'expected_text' => 'required|string',
        'spoken_text' => 'required|string',
    ]);

    $expected = strtolower($request->expected_text);
    $spoken = strtolower($request->spoken_text);

$expectedWords = preg_split('/\s+/u', $expected, -1, PREG_SPLIT_NO_EMPTY);
$spokenWords = preg_split('/\s+/u', $spoken, -1, PREG_SPLIT_NO_EMPTY);


    $matchedWords = array_intersect($expectedWords, $spokenWords);
    $accuracy = count($matchedWords) / max(1, count($expectedWords));
    $score = round($accuracy * 100);

    $missedWords = array_diff($expectedWords, $spokenWords);

    $feedback = [
        'missed_words' => array_values($missedWords),
        'matched_words' => array_values($matchedWords),
    ];

    $record = PronunciationFeedback::create([
        'user_id' => $request->user_id,
        'accuracy_score' => $score,
        'mistakes_highlighted' => json_encode($feedback),
        'expected_text' => $expected,
        'spoken_text' => $spoken,
        'audio_url' => '', 
    ]);

    return response()->json([
        'message' => 'Fluency analyzed successfully',
        'score' => $score,
        'feedback' => $feedback,
        'id' => $record->feedback_id
    ], 201);
}

}
