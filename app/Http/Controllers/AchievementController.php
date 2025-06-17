<?php
namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Models\UserAchievement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AchievementController extends Controller
{
    public function index()
    {
        $userAchievements = Auth::user()->achievements()
            ->get()
            ->keyBy('id');

        $allAchievements = Achievement::all()->map(function ($achievement) use ($userAchievements) {
            $achievement->is_earned = $userAchievements->has($achievement->id);
            $achievement->earned_at = $userAchievements->has($achievement->id)
                ? $userAchievements->get($achievement->id)->pivot->earned_at
                : null;
            return $achievement;
        });

        return response()->json($allAchievements);
    }
}
