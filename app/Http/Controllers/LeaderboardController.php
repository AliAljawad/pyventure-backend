<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaderboardController extends Controller
{
    public function index(Request $request)
    {
        $timeFrame = $request->get('timeFrame', 'allTime');
        $sortBy = $request->get('sortBy', 'score');
        $limit = $request->get('limit', 10);

        $query = User::select([
            'users.id',
            'users.username',
            'users.rank',
            'user_stats.total_score',
            'user_stats.total_completed_levels',
            'user_stats.total_attempts',
            'user_stats.time_spent'
        ])
        ->join('user_stats', 'users.id', '=', 'user_stats.user_id')
        ->groupBy('users.id', 'users.username', 'users.rank', 'user_stats.total_score', 
                 'user_stats.total_completed_levels', 'user_stats.total_attempts', 'user_stats.time_spent');

        // Apply time frame filter
        if ($timeFrame === 'weekly') {
            $query->where('user_stats.updated_at', '>=', now()->subWeek());
        } elseif ($timeFrame === 'daily') {
            $query->where('user_stats.updated_at', '>=', now()->startOfDay());
        }

        // Apply sorting
        switch ($sortBy) {
            case 'attempts':
                $query->orderBy('user_stats.total_attempts', 'desc');
                break;
            case 'levels':
                $query->orderBy('user_stats.total_completed_levels', 'desc');
                break;
            default: // score
                $query->orderBy('user_stats.total_score', 'desc');
        }

        $users = $query->limit($limit)->get();

        // Get achievements for each user
        $users->each(function ($user) {
            $user->achievements = DB::table('user_achievements')
                ->join('achievements', 'user_achievements.achievement_id', '=', 'achievements.id')
                ->where('user_achievements.user_id', $user->id)
                ->pluck('achievements.title')
                ->toArray();
        });

        return response()->json([
            'users' => $users,
            'timeFrame' => $timeFrame,
            'sortBy' => $sortBy
        ]);
    }
}