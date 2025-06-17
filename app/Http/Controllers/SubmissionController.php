<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Models\Level;
use App\Models\UserStat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SubmissionController extends Controller
{
    public function index(Request $request)
    {
        $query = Submission::where('user_id', Auth::id())
            ->with('level')
            ->orderBy('submitted_at', 'desc');

        if ($request->has('level_id')) {
            $query->where('level_id', $request->level_id);
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'level_id' => 'required|exists:levels,id',
            'code' => 'required|string',
            'is_correct' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $submission = Submission::create([
            'user_id' => Auth::id(),
            'level_id' => $request->level_id,
            'code' => $request->code,
            'is_correct' => $request->is_correct,
            'submitted_at' => now(),
        ]);

        // Update user stats
        $this->updateUserStats($submission);

        return response()->json([
            'message' => 'Submission saved successfully',
            'submission' => $submission
        ], 201);
    }

    public function show($id)
    {
        $submission = Submission::where('user_id', Auth::id())
            ->where('id', $id)
            ->with('level')
            ->firstOrFail();

        return response()->json($submission);
    }

    private function updateUserStats($submission)
    {
        // Use firstOrCreate to handle the case where stats don't exist
        $userStats = UserStat::firstOrCreate(
            ['user_id' => Auth::id()],
            [
                'total_attempts' => 0,
                'total_completed_levels' => 0,
                'total_score' => 0,
                'time_spent' => 0,
            ]
        );

        // Always increment total attempts
        $userStats->increment('total_attempts');

        // Handle correct submissions
        if ($submission->is_correct) {
            // Check if this is the first correct submission for this level
            $previousCorrectSubmission = Submission::where('user_id', Auth::id())
                ->where('level_id', $submission->level_id)
                ->where('is_correct', true)
                ->where('id', '<', $submission->id)
                ->exists();

            // Only increment completed levels if this is the first correct submission for this level
            if (!$previousCorrectSubmission) {
                $userStats->increment('total_completed_levels');
                $userStats->increment('total_score', 100);
            }
        }
    }
}