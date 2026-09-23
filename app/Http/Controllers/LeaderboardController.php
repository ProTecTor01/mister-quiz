<?php

namespace App\Http\Controllers;

use App\Models\User;

class LeaderboardController extends Controller
{
    public function index()
    {
        $players = User::orderByDesc('xp')->orderBy('id')->limit(10)->get();
        return view('leaderboard', compact('players'));
    }
}
