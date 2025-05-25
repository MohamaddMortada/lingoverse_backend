<?php

namespace App\Http\Controllers;

use App\Models\UserProgress;
use App\Models\UserChallenge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class UserProgressController extends Controller
{
    public function getAllUserProgress()
    {
        $userProgress = UserProgress::with('user')->get();

        if ($userProgress->isEmpty()) {
            return response()->json(['message' => 'No user progress found'], 404);
        }

        return response()->json($userProgress, 200);
    }

    public function getUserProgress($userId)
    {
        $userProgress = UserProgress::where('user_id', $userId)->first();

        if (!$userProgress) {
            return response()->json(['message' => 'User progress not found'], 404);
        }

        return response()->json($userProgress, 200);
    }

    public function createUserProgress(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'language' => 'required|string|max:255',
            'current_level' => 'required|integer',
            'total_points' => 'required|integer',
        ]);

        $userProgress = UserProgress::create([
            'user_id' => $request->user_id,
            'language' => $request->language,
            'current_level' => $request->current_level,
            'total_points' => $request->total_points,
        ]);

        return response()->json([
            'message' => 'User progress created successfully',
            'user_progress' => $userProgress
        ], 201);
    }

    public function updateUserProgress(Request $request, $id)
    {
        $userProgress = UserProgress::find($id);

        if (!$userProgress) {
            return response()->json(['message' => 'User progress not found'], 404);
        }

        $request->validate([
            'language' => 'sometimes|required|string|max:255',
            'current_level' => 'sometimes|required|integer',
            'total_points' => 'sometimes|required|integer',
        ]);

        $userProgress->update([
            'language' => $request->language ?? $userProgress->language,
            'current_level' => $request->current_level ?? $userProgress->current_level,
            'total_points' => $request->total_points ?? $userProgress->total_points,
        ]);

        return response()->json([
            'message' => 'User progress updated successfully',
            'user_progress' => $userProgress
        ], 200);
    }

    public function deleteUserProgress($id)
    {
        $userProgress = UserProgress::find($id);

        if (!$userProgress) {
            return response()->json(['message' => 'User progress not found'], 404);
        }

        $userProgress->delete();

        return response()->json(['message' => 'User progress deleted successfully'], 200);
    }

    public function getUserStats($userId)
{
    $bestScore = UserChallenge::where('user_id', $userId)
        ->where('status', 'completed')
        ->max('score');

    $averageScore = UserChallenge::where('user_id', $userId)
        ->where('status', 'completed')
        ->avg('score');

    // Get average scores for all users
    $userAverages = UserChallenge::select('user_id', DB::raw('AVG(score) as avg_score'))
        ->where('status', 'completed')
        ->groupBy('user_id')
        ->orderByDesc('avg_score')
        ->get();

    // Calculate rank
    $rank = null;
    foreach ($userAverages as $index => $user) {
        if ($user->user_id == $userId) {
            $rank = $index + 1;
            break;
        }
    }

    return response()->json([
        'best_score' => $bestScore ?? 0,
        'learning_ratio' => round($averageScore ?? 0, 2),
        'best_rank' => $rank,
    ]);
}


}
