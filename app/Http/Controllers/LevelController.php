<?php

namespace App\Http\Controllers;

use App\Models\Level;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LevelController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();

        $levels = Level::select([
            'id',
            'title',
            'description',
            'difficulty',
            'category',
            'topic',
            'is_completed',
            'is_unlocked'
        ])->get();

        // If user is authenticated, get their progress
        if ($userId) {
            $levels = $levels->map(function ($level) use ($userId) {
                $progress = \App\Models\UserProgress::where('user_id', $userId)
                    ->where('level_id', $level->id)
                    ->first();

                $level->is_completed = $progress ? $progress->is_completed : false;

                return $level;
            });
        }

        return response()->json([
            'success' => true,
            'data' => $levels
        ]);
    }

    public function show($id)
    {
        $level = Level::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $level
        ]);
    }
}
