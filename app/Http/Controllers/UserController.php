<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\UserChallenge;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;


class UserController extends Controller
{
    public function getUsers()
    {
        $users = User::all();
        
        if ($users->isEmpty()) {
            return response()->json(['message' => 'No users found'], 404);
        }

        return response()->json($users, 200);
    }

    public function getUser($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json($user, 200);
    }

    public function createUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password), 
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user
        ], 201);
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'sometimes|required|string|min:8',
            'age' => 'sometimes|nullable|integer|min:1',
            'image' => 'sometimes|nullable|string',
        ]);

        $user->update([
            'name' => $request->name ?? $user->name,
            'email' => $request->email ?? $user->email,
            'password' => $request->password ? bcrypt($request->password) : $user->password,
            'age' => $request->has('age') ? $request->age : $user->age,
    'image' => $request->has('image') ? $request->image : $user->image,
        ]);

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user
        ], 200);
    }

    public function deleteUser($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully'], 200);
    }
    
    public function getUserDashboardStats()
{
    $users = User::withCount([
        'chatSessions',
        'pronunciationFeedbacks'
    ])
    ->with([
        'userChallenges.challenge:id,title'
    ])
    ->get(['id', 'name', 'email', 'age', 'created_at']);

    $userAverages = UserChallenge::select('user_id', DB::raw('AVG(score) as avg_score'))
        ->where('status', 'completed')
        ->groupBy('user_id')
        ->orderByDesc('avg_score')
        ->get();

    $ranks = $userAverages->pluck('user_id')->flip()->map(fn($i) => $i + 1);

    $data = $users->map(function ($user) use ($ranks) {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'age' => $user->age,
            'chatbot_messages' => $user->chat_sessions_count,
            'fluency_analyses' => $user->pronunciation_feedbacks_count,
            'challenges' => $user->userChallenges->pluck('challenge.title')->unique()->values(),
            'created_at' => $user->created_at,
            'rank' => $ranks[$user->id] ?? null,
            'image' => $user->image,

        ];
    });

    return response()->json($data);
}
public function verifyPassword(Request $request)
{
    $request->validate([
        'user_id' => 'required|integer|exists:users,id',
        'password' => 'required|string',
    ]);

    $user = User::find($request->user_id);

    if (!$user) {
        return response()->json(['valid' => false, 'message' => 'User not found'], 404);
    }

    if (Hash::check($request->password, $user->password)) {
        return response()->json(['valid' => true], 200);
    } else {
        return response()->json(['valid' => false, 'message' => 'Incorrect password'], 401);
    }
}
    

}
