<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        return response()->json([
            'user' => $user,
            'stats' => $user->stats,
            'progress' => $user->progress()->with('level')->get(),
            'achievements' => $user->achievements
        ]);
    }
}
