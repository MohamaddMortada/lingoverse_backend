<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChallengeController;
use App\Http\Controllers\ChatSessionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\PronunciationFeedbackController;
use App\Http\Controllers\UserChallengeController;
use App\Http\Controllers\UserProgressController;
use App\Http\Controllers\VocabularyScanController;
use App\Http\Controllers\AIController;
use App\Http\Controllers\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
});


Route::get('/challenges', [ChallengeController::class, 'getChallenges']);
Route::get('/challenges/{id}', [ChallengeController::class, 'getChallenge']);
Route::post('/challenges', [ChallengeController::class, 'createChallenge']);
Route::put('/challenges/{id}', [ChallengeController::class, 'updateChallenge']);
Route::delete('/challenges/{id}', [ChallengeController::class, 'deleteChallenge']);

Route::get('/chat_sessions', [ChatSessionController::class, 'getAllChatSessions']);
Route::get('/chat_sessions/{chat_id}', [ChatSessionController::class, 'getChatSession']);
Route::post('/chat_sessions', [ChatSessionController::class, 'createChatSession']);
Route::put('/chat_sessions/{chat_id}', [ChatSessionController::class, 'updateChatSession']);
Route::delete('/chat_sessions/{chat_id}', [ChatSessionController::class, 'deleteChatSession']);

Route::get('users', [UserController::class, 'getUsers']);
Route::get('users/{id}', [UserController::class, 'getUser']);
Route::post('users', [UserController::class, 'createUser']);
Route::put('users/{id}', [UserController::class, 'updateUser']);
Route::delete('users/{id}', [UserController::class, 'deleteUser']);

Route::get('leaderboard', [LeaderboardController::class, 'getLeaderboard']);
Route::get('leaderboard/{userId}', [LeaderboardController::class, 'getLeaderboardByUser']);
Route::post('leaderboard', [LeaderboardController::class, 'createLeaderboard']);
Route::put('leaderboard/{id}', [LeaderboardController::class, 'updateLeaderboard']);
Route::delete('leaderboard/{id}', [LeaderboardController::class, 'deleteLeaderboard']);

Route::get('pronunciation-feedback', [PronunciationFeedbackController::class, 'getAllFeedback']);
Route::get('pronunciation-feedback/{userId}', [PronunciationFeedbackController::class, 'getFeedbackByUser']);
Route::post('pronunciation-feedback', [PronunciationFeedbackController::class, 'createFeedback']);
Route::put('pronunciation-feedback/{id}', [PronunciationFeedbackController::class, 'updateFeedback']);
Route::delete('pronunciation-feedback/{id}', [PronunciationFeedbackController::class, 'deleteFeedback']);

Route::get('user-challenges', [UserChallengeController::class, 'getAllUserChallenges']);
Route::get('user-challenges/user/{userId}', [UserChallengeController::class, 'getUserChallengeByUser']);
Route::post('user-challenges', [UserChallengeController::class, 'createUserChallenge']);
Route::put('user-challenges/{id}', [UserChallengeController::class, 'updateUserChallenge']);
Route::delete('user-challenges/{id}', [UserChallengeController::class, 'deleteUserChallenge']);

Route::get('user-progress', [UserProgressController::class, 'getAllUserProgress']);
Route::get('user-progress/{userId}', [UserProgressController::class, 'getUserProgress']);
Route::post('user-progress', [UserProgressController::class, 'createUserProgress']);
Route::put('user-progress/{id}', [UserProgressController::class, 'updateUserProgress']);
Route::delete('user-progress/{id}', [UserProgressController::class, 'deleteUserProgress']);

Route::get('vocabulary-scans', [VocabularyScanController::class, 'getAllVocabularyScans']);
Route::get('vocabulary-scans/{id}', [VocabularyScanController::class, 'getVocabularyScan']);
Route::post('vocabulary-scans', [VocabularyScanController::class, 'createVocabularyScan']);
Route::put('vocabulary-scans/{id}', [VocabularyScanController::class, 'updateVocabularyScan']);
Route::delete('vocabulary-scans/{id}', [VocabularyScanController::class, 'deleteVocabularyScan']);


Route::get('/speech/random-text', [AIController::class, 'generateParagraph']);

Route::post('/speech/analyze-fluency', [PronunciationFeedbackController::class, 'analyzeFluency']);

Route::get('/challenges/today/{user_id}', [UserChallengeController::class, 'getTodayChallenges']);
Route::post('/challenges/complete', [UserChallengeController::class, 'completeChallenge']);

Route::post('/challenges/generate-ai', [ChallengeController::class, 'generateAIChallenges']);

Route::post('/challenges/answer', [UserChallengeController::class, 'evaluateAnswer']);

Route::post('/speech/paragraph', [AIController::class, 'generateFluencyParagraph']);

Route::post('/speech/chat', [ChatSessionController::class, 'chatWithAI']);

Route::get('/user-progress/stats/{userId}', [UserProgressController::class, 'getUserStats']);

Route::get('/dashboard/users', [UserController::class, 'getUserDashboardStats']);
