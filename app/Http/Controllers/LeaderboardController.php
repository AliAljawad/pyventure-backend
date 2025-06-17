<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserStat;
use Illuminate\Http\Request;


class LeaderboardController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->get('limit', 50);

        $leaderboard = User::with('stats')
            ->whereHas('stats')
            ->get()
            ->sortByDesc(function ($user) {
                return $user->stats->total_score;
            })
            ->take($limit)
            ->values()
            ->map(function ($user, $index) {
                return [
                    'rank' => $index + 1,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'username' => $user->username,
                        'rank' => $user->rank ?? 'Python Explorer'
                    ],
                    'stats' => [
                        'total_score' => $user->stats->total_score,
                        'total_completed_levels' => $user->stats->total_completed_levels,
                        'total_attempts' => $user->stats->total_attempts,
                    ]
                ];
            });

        return response()->json($leaderboard);
    }
}