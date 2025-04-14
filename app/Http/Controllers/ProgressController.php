<?php

namespace App\Http\Controllers;

use App\Models\UserProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProgressController extends Controller
{
    public function index()
    {
        return UserProgress::where('user_id', Auth::id())->get();
    }

    public function update(Request $request)
    {
        $request->validate([
            'level_id' => 'required|exists:levels,id',
            'is_completed' => 'boolean',
            'score' => 'integer',
            'attempts' => 'integer',
        ]);

        $progress = UserProgress::updateOrCreate(
            ['user_id' => Auth::id(), 'level_id' => $request->level_id],
            [
                'is_completed' => $request->input('is_completed', false),
                'score' => $request->input('score', 0),
                'attempts' => $request->input('attempts', 1),
                'last_updated' => now(),
            ]
        );

        return response()->json($progress);
    }
}
