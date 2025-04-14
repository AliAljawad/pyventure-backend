<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubmissionController extends Controller
{
    public function index()
    {
        return Submission::where('user_id', Auth::id())->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'level_id' => 'required|exists:levels,id',
            'code' => 'required|string',
            'is_correct' => 'required|boolean',
        ]);

        $submission = Submission::create([
            'user_id' => Auth::id(),
            'level_id' => $request->level_id,
            'code' => $request->code,
            'is_correct' => $request->is_correct,
            'submitted_at' => now(),
        ]);

        return response()->json($submission, 201);
    }
}
