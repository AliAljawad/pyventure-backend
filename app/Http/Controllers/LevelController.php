<?php

namespace App\Http\Controllers;

use App\Models\Level;
use Illuminate\Http\Request;

class LevelController extends Controller
{
    public function index()
    {
        return Level::all();
    }

    public function show($id)
    {
        return Level::findOrFail($id);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'difficulty' => 'required|in:easy,medium,hard',
            'category' => 'required|string',
            'question' => 'required|string',
        ]);

        return Level::create($request->all());
    }

    public function update(Request $request, $id)
    {
        $level = Level::findOrFail($id);
        $level->update($request->all());

        return $level;
    }

    public function destroy($id)
    {
        Level::destroy($id);
        return response()->json(['message' => 'Level deleted']);
    }
}
