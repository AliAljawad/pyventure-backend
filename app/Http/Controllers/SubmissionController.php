<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Models\Level;
use App\Models\UserStat;
use App\Models\UserProgress;
use App\Services\AchievementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class SubmissionController extends Controller
{
    public function index(Request $request)
    {
        $query = Submission::where('user_id', Auth::id())
            ->with('level')
            ->orderBy('submitted_at', 'desc');

        if ($request->has('level_id')) {
            $query->where('level_id', $request->level_id);
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'level_id' => 'required|exists:levels,id',
            'code' => 'required|string',
            'is_correct' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $submission = Submission::create([
                'user_id' => Auth::id(),
                'level_id' => $request->level_id,
                'code' => $request->code,
                'is_correct' => $request->is_correct,
                'submitted_at' => now(),
            ]);

            // Update user stats
            $this->updateUserStats($submission);

            // Handle level completion and unlocking
            $levelData = null;
            if ($request->is_correct) {
                $levelData = $this->handleLevelCompletion($request->level_id);
            }
            $achievementService = new AchievementService();
            $newAchievements = $achievementService->checkAndAwardAchievements(Auth::id());
            DB::commit();

            return response()->json([
                'message' => 'Submission saved successfully',
                'submission' => $submission,
                'level_completed' => $request->is_correct,
                'next_level_unlocked' => $levelData['next_level_unlocked'] ?? false,
                'next_level_id' => $levelData['next_level_id'] ?? null,
                'progress_updated' => $levelData['progress_updated'] ?? false
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Error saving submission',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $submission = Submission::where('user_id', Auth::id())
            ->where('id', $id)
            ->with('level')
            ->firstOrFail();

        return response()->json($submission);
    }

    private function updateUserStats($submission)
    {
        // Use firstOrCreate to handle the case where stats don't exist
        $userStats = UserStat::firstOrCreate(
            ['user_id' => Auth::id()],
            [
                'total_attempts' => 0,
                'total_completed_levels' => 0,
                'total_score' => 0,
            ]
        );

        // Always increment total attempts
        $userStats->increment('total_attempts');

        // Handle correct submissions
        if ($submission->is_correct) {
            // Check if this is the first correct submission for this level
            $previousCorrectSubmission = Submission::where('user_id', Auth::id())
                ->where('level_id', $submission->level_id)
                ->where('is_correct', true)
                ->where('id', '<', $submission->id)
                ->exists();

            // Only increment completed levels if this is the first correct submission for this level
            if (!$previousCorrectSubmission) {
                $userStats->increment('total_completed_levels');
                $userStats->increment('total_score', 100);
            }
        }
    }

    private function handleLevelCompletion($levelId)
    {
        $userId = Auth::id();
        $result = [
            'progress_updated' => false,
            'next_level_unlocked' => false,
            'next_level_id' => null
        ];

        // Check if level was already completed
        $existingProgress = UserProgress::where('user_id', $userId)
            ->where('level_id', $levelId)
            ->first();

        $wasAlreadyCompleted = $existingProgress && $existingProgress->is_completed;

        // Update or create user progress for current level
        $progress = UserProgress::updateOrCreate(
            [
                'user_id' => $userId,
                'level_id' => $levelId
            ],
            [
                'score' => 100,
                'is_completed' => true,
                'attempts' => $existingProgress ? $existingProgress->attempts + 1 : 1,
            ]
        );

        $result['progress_updated'] = true;

        // If this level wasn't completed before, unlock the next level
        if (!$wasAlreadyCompleted) {
            $currentLevel = Level::find($levelId);

            if ($currentLevel) {
                // Find the next level (assuming levels have an 'order' or 'sequence' field)
                // If you don't have an order field, you might use id + 1 or another logic
                $nextLevel = Level::where('id', '>', $levelId)
                    ->orderBy('id')
                    ->first();

                // Alternative if you have an 'order' field:
                // $nextLevel = Level::where('order', '>', $currentLevel->order)
                //     ->orderBy('order')
                //     ->first();

                if ($nextLevel) {
                    // Check if next level is already unlocked
                    $nextLevelProgress = UserProgress::where('user_id', $userId)
                        ->where('level_id', $nextLevel->id)
                        ->first();

                    if (!$nextLevelProgress) {
                        // Create progress entry for next level (unlocked but not completed)
                        UserProgress::create([
                            'user_id' => $userId,
                            'level_id' => $nextLevel->id,
                            'score' => 0,
                            'is_completed' => false,
                            'attempts' => 0,
                        ]);

                        $result['next_level_unlocked'] = true;
                        $result['next_level_id'] = $nextLevel->id;
                    }
                }
            }
        }

        return $result;
    }
}
