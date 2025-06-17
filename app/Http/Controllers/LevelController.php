<?php
namespace App\Http\Controllers;

use App\Models\Level;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LevelController extends Controller
{
    public function index()
    {
        $levels = Level::orderBy('id')->get();

        // Add user progress information
        $userProgress = Auth::user()->progress()->pluck('is_completed', 'level_id');

        $levels->each(function ($level) use ($userProgress) {
            $level->is_completed = $userProgress->get($level->id, false);
            $level->is_unlocked = $this->isLevelUnlocked($level->id);
        });

        return response()->json($levels);
    }

    public function show($id)
    {
        $level = Level::findOrFail($id);

        // Add user progress information
        $userProgress = Auth::user()->progress()
            ->where('level_id', $id)
            ->first();

        $level->is_completed = $userProgress ? $userProgress->is_completed : false;
        $level->is_unlocked = $this->isLevelUnlocked($id);
        $level->user_score = $userProgress ? $userProgress->score : 0;
        $level->attempts = $userProgress ? $userProgress->attempts : 0;

        return response()->json($level);
    }

    public function store(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'difficulty' => 'required|in:easy,medium,hard',
            'category' => 'required|string|max:255',
            'question' => 'required|string',
            'solution' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $level = Level::create($request->all());

        return response()->json([
            'message' => 'Level created successfully',
            'level' => $level
        ], 201);
    }

    // MISSING: update and destroy methods that your routes reference
    public function update(Request $request, $id)
    {
        $level = Level::findOrFail($id);

        $validator = \Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'difficulty' => 'sometimes|required|in:easy,medium,hard',
            'category' => 'sometimes|required|string|max:255',
            'question' => 'sometimes|required|string',
            'solution' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $level->update($request->all());

        return response()->json([
            'message' => 'Level updated successfully',
            'level' => $level
        ]);
    }

    public function destroy($id)
    {
        $level = Level::findOrFail($id);
        $level->delete();

        return response()->json([
            'message' => 'Level deleted successfully'
        ]);
    }

    private function isLevelUnlocked($levelId)
    {
        // Level 1 is always unlocked
        if ($levelId == 1) {
            return true;
        }

        // Check if previous level is completed
        $previousLevel = Level::where('id', $levelId - 1)->first();
        if (!$previousLevel) {
            return false;
        }

        $previousProgress = Auth::user()->progress()
            ->where('level_id', $previousLevel->id)
            ->first();

        return $previousProgress && $previousProgress->is_completed;
    }
}
