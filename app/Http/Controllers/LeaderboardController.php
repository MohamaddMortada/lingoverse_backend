<?php

namespace App\Http\Controllers;

use App\Models\Leaderboard;
use App\Models\User;
use Illuminate\Http\Request;

class LeaderboardController extends Controller
{
    public function getLeaderboard()
    {
        $leaderboard = Leaderboard::with('user')->orderBy('rank')->get();
        
        if ($leaderboard->isEmpty()) {
            return response()->json(['message' => 'No leaderboard entries found'], 404);
        }

        return response()->json($leaderboard, 200);
    }

    public function getLeaderboardByUser($userId)
    {
        $leaderboard = Leaderboard::where('user_id', $userId)->first();

        if (!$leaderboard) {
            return response()->json(['message' => 'Leaderboard entry not found for this user'], 404);
        }

        return response()->json($leaderboard, 200);
    }

    public function createLeaderboard(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'rank' => 'required|integer',
            'total_score' => 'required|integer',
        ]);

        $leaderboard = Leaderboard::create([
            'user_id' => $request->user_id,
            'rank' => $request->rank,
            'total_score' => $request->total_score,
        ]);

        return response()->json([
            'message' => 'Leaderboard entry created successfully',
            'leaderboard' => $leaderboard
        ], 201); 
    }

    public function updateLeaderboard(Request $request, $id)
    {
        $leaderboard = Leaderboard::find($id);

        if (!$leaderboard) {
            return response()->json(['message' => 'Leaderboard entry not found'], 404);
        }

        $request->validate([
            'rank' => 'sometimes|required|integer',
            'total_score' => 'sometimes|required|integer',
        ]);

        $leaderboard->update([
            'rank' => $request->rank ?? $leaderboard->rank,
            'total_score' => $request->total_score ?? $leaderboard->total_score,
        ]);

        return response()->json([
            'message' => 'Leaderboard entry updated successfully',
            'leaderboard' => $leaderboard
        ], 200);
    }

    public function deleteLeaderboard($id)
    {
        $leaderboard = Leaderboard::find($id);

        if (!$leaderboard) {
            return response()->json(['message' => 'Leaderboard entry not found'], 404);
        }

        $leaderboard->delete();

        return response()->json(['message' => 'Leaderboard entry deleted successfully'], 200);
    }
}
