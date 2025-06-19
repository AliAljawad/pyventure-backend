<?php

namespace App\Services;

use App\Models\UserAchievement;
use App\Models\Achievement;
use App\Models\UserProgress;
use App\Models\Submission;
use App\Models\Level;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AchievementService
{
    /**
     * Check and award achievements based on user actions
     */
    public function checkAndAwardAchievements($userId, $context = [])
    {
        $newAchievements = [];

        // Get all achievements user hasn't earned yet
        $unearned = Achievement::whereNotIn('id', function ($query) use ($userId) {
            $query->select('achievement_id')
                ->from('user_achievements')
                ->where('user_id', $userId);
        })->get();

        foreach ($unearned as $achievement) {
            if ($this->checkAchievementCondition($userId, $achievement, $context)) {
                $this->awardAchievement($userId, $achievement);
                $newAchievements[] = $achievement;
            }
        }

        return $newAchievements;
    }

    /**
     * Check if user meets conditions for a specific achievement
     */
    private function checkAchievementCondition($userId, $achievement, $context)
    {
        switch ($achievement->id) {
            case 1: // First Steps - Completed your first level
                return UserProgress::where('user_id', $userId)
                    ->where('is_completed', true)
                    ->exists();

            case 2: // Loop Master - Completed all looping challenges
                $loopLevels = Level::where('topic', 'LIKE', '%Loop%')->pluck('id');
                $completedLoops = UserProgress::where('user_id', $userId)
                    ->whereIn('level_id', $loopLevels)
                    ->where('is_completed', true)
                    ->count();
                return $completedLoops >= $loopLevels->count();

            case 3: // Comprehension Commander - Mastered comprehensions
                return UserProgress::where('user_id', $userId)
                    ->whereHas('level', function ($query) {
                        $query->where('topic', 'LIKE', '%Comprehension%');
                    })
                    ->where('is_completed', true)
                    ->exists();

            case 4: // Zero Bug Run - 3 levels in a row without failed submissions
                return $this->checkZeroBugRun($userId);

            case 5: // Array Whisperer - Solved all NumPy challenges
                return UserProgress::where('user_id', $userId)
                    ->whereHas('level', function ($query) {
                        $query->where('topic', 'LIKE', '%NumPy%');
                    })
                    ->where('is_completed', true)
                    ->exists();

            case 6: // Curious Coder - Attempted every level at least once
                $totalLevels = Level::count();
                $attemptedLevels = UserProgress::where('user_id', $userId)
                    ->where('attempts', '>', 0)
                    ->distinct('level_id')
                    ->count();
                return $attemptedLevels >= $totalLevels;

            case 7: // Mission Complete - Finished all levels
                $totalLevels = Level::count();
                $completedLevels = UserProgress::where('user_id', $userId)
                    ->where('is_completed', true)
                    ->count();
                return $completedLevels >= $totalLevels;

            default:
                return false;
        }
    }

    /**
     * Check for Zero Bug Run achievement (3 consecutive correct submissions)
     */
    private function checkZeroBugRun($userId)
    {
        $recentSubmissions = Submission::where('user_id', $userId)
            ->orderBy('submitted_at', 'desc')
            ->take(3)
            ->get();

        if ($recentSubmissions->count() < 3) {
            return false;
        }

        // Check if all 3 are correct and for different levels
        $correctCount = 0;
        $levelIds = [];

        foreach ($recentSubmissions as $submission) {
            if ($submission->is_correct && !in_array($submission->level_id, $levelIds)) {
                $correctCount++;
                $levelIds[] = $submission->level_id;
            }
        }

        return $correctCount >= 3;
    }

    /**
     * Award achievement to user
     */
    private function awardAchievement($userId, $achievement)
    {
        UserAchievement::create([
            'user_id' => $userId,
            'achievement_id' => $achievement->id,
            'earned_at' => now(),
        ]);
    }

    /**
     * Get user's achievements
     */
    public function getUserAchievements($userId)
    {
        return UserAchievement::where('user_id', $userId)
            ->with('achievement')
            ->orderBy('earned_at', 'desc')
            ->get();
    }
}
