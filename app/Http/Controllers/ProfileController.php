<?php

namespace App\Http\Controllers;

use App\Models\UserStat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserProgress;
use App\Models\UserStats;
use App\Models\UserAchievement;

class ProfileController extends Controller
{
    /**
     * Get the authenticated user's profile data
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show()
    {
        // Get authenticated user
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }

            // Get or create user stats
            $stats = $user->stats ?? UserStat::create([
                'user_id' => $user->id,
                'total_attempts' => 0,
                'total_completed_levels' => 0,
                'total_score' => 0,
            ]);

            // Get user progress with level details
            $progress = $user->progress()
                ->with('level')
                ->get()
                ->map(function ($item) {
                    return [
                        'user_id' => $item->user_id,
                        'level_id' => $item->level_id,
                        'is_completed' => (bool) $item->is_completed,
                        'score' => $item->score,
                        'attempts' => $item->attempts,
                        'last_updated' => $item->last_updated,
                        'level' => [
                            'id' => $item->level->id,
                            'title' => $item->level->title,
                            'description' => $item->level->description,
                            'difficulty' => $item->level->difficulty,
                            'category' => $item->level->category
                        ]
                    ];
                });

            // Get user achievements with details
            $achievements = $user->achievements()->get()->map(function ($achievement) {
                return [
                    'id' => $achievement->id,
                    'title' => $achievement->title,
                    'description' => $achievement->description,
                    'icon_url' => $achievement->icon_url,
                    'earned_at' => $achievement->pivot->earned_at  // Access pivot data like this
                ];
            });
            // Return formatted response
            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email,
                    'rank' => $user->rank ?? 'Python Explorer'
                ],
                'stats' => [
                    'user_id' => $stats->user_id,
                    'total_attempts' => $stats->total_attempts,
                    'total_completed_levels' => $stats->total_completed_levels,
                    'total_score' => $stats->total_score,
                ],
                'progress' => $progress,
                'achievements' => $achievements
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Server error',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'fuck' => $e->getTraceAsString()
            ], 500);
        }
    }
}
