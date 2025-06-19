<?php
namespace App\Http\Controllers;

use App\Models\UserStat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserStatsController extends Controller
{
    public function index()
    {
        $stats = Auth::user()->stats ?? new UserStat([
            'user_id' => Auth::id(),
            'total_attempts' => 0,
            'total_completed_levels' => 0,
            'total_score' => 0,
            'time_spent' => 0,
        ]);

        // Calculate additional stats
        $stats->completion_rate = $stats->total_attempts > 0
            ? round(($stats->total_completed_levels / $stats->total_attempts) * 100, 2)
            : 0;

        $stats->average_score = $stats->total_completed_levels > 0
            ? round($stats->total_score / $stats->total_completed_levels, 2)
            : 0;

        return response()->json($stats);
    }
}
