<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class AchievementController extends Controller
{
    public function index()
    {
        return Auth::user()->achievements;
    }
}
