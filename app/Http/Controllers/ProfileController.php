<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $scores = [];
        foreach (['Art', 'History', 'Geography', 'Science', 'Sports'] as $category) {
            [$correct, $total] = array_map('intval', explode('/', $user->{strtolower($category)}));
            $scores[$category] = [
                'correct' => $correct,
                'total' => $total,
                'percentage' => $total ? round($correct * 100 / $total, 1) : 0,
            ];
        }

        $rank = $user->xp >= 10000 ? 'Quiz Master'
            : ($user->xp >= 5000 ? 'Epic Quizer'
            : ($user->xp >= 1500 ? 'Average Quizer' : 'Quiz Aprentice'));

        return view('profile', compact('user', 'scores', 'rank'));
    }
}
