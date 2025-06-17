<?php

namespace App\Http\Controllers;

use App\Models\UserProgress;
use App\Models\Level;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ProgressController extends Controller
{
    public function index()
    {
        $progress = UserProgress::where('user_id', Auth::id())
            ->with('level')
            ->get();

        return response()->json($progress);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'level_id' => 'required|exists:levels,id',
            'score' => 'required|integer|min:0|max:100',
            'is_completed' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $userId = Auth::id();
        $levelId = $request->level_id;

        // Manual approach to handle composite key
        $progress = UserProgress::where('user_id', $userId)
            ->where('level_id', $levelId)
            ->first();

        if ($progress) {
            // Update existing record
            $progress->update([
                'score' => $request->score,
                'is_completed' => $request->is_completed,
                'attempts' => $progress->attempts + 1,
            ]);
        } else {
            // Create new record
            $progress = UserProgress::create([
                'user_id' => $userId,
                'level_id' => $levelId,
                'score' => $request->score,
                'is_completed' => $request->is_completed,
                'attempts' => 1,
            ]);
        }

        // Update user stats when progress is updated
        $this->updateUserStats($levelId, $request->is_completed);

        return response()->json([
            'message' => 'Progress updated successfully',
            'progress' => $progress
        ]);
    }

    public function show($levelId)
    {
        $progress = UserProgress::where('user_id', Auth::id())
            ->where('level_id', $levelId)
            ->with('level')
            ->first();

        if (!$progress) {
            return response()->json([
                'message' => 'No progress found for this level',
                'progress' => null
            ]);
        }

        return response()->json($progress);
    }

    private function updateUserStats($levelId, $isCompleted)
    {
        $user = Auth::user();
        $userStats = $user->stats;

        if (!$userStats) {
            $userStats = $user->stats()->create([
                'user_id' => Auth::id(),
                'total_attempts' => 0,
                'total_completed_levels' => 0,
                'total_score' => 0,
                'time_spent' => 0,
            ]);
        }

        $userStats->increment('total_attempts');

        if ($isCompleted) {
            // Check if this level was not previously completed
            $wasCompleted = UserProgress::where('user_id', Auth::id())
                ->where('level_id', $levelId)
                ->where('is_completed', true)
                ->exists();

            if (!$wasCompleted) {
                $userStats->increment('total_completed_levels');
                $userStats->increment('total_score', 100);
            }
        }
    }
}