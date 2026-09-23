<?php

namespace App\Http\Controllers\Questions;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuestionController extends Controller
{
    private const CATEGORIES = ['Art', 'History', 'Geography', 'Science', 'Sports'];

    public function index(Request $request)
    {
        $user = $request->user();
        $quiz = $user->quizzes()->where('completed', false)->latest('id')->first();

        if (!$quiz) {
            $questions = collect();
            foreach (self::CATEGORIES as $category) {
                $selection = Question::where('category', $category)
                    ->whereHas('answers')->inRandomOrder()->limit(4)->get();
                if ($selection->isEmpty()) {
                    return redirect()->route('home')->with('error', 'Quiz questions are unavailable. Run the database seeder.');
                }
                $questions = $questions->concat($selection);
            }

            $quiz = DB::transaction(function () use ($user, $questions) {
                $quiz = $user->quizzes()->create(['completed' => false]);
                $quiz->questions()->attach($questions->pluck('id'));
                return $quiz;
            });
        }

        $quiz->load('questions.answers');
        return view('questions.list', compact('quiz'));
    }

    public function submit(Request $request, Quiz $quiz)
    {
        abort_unless($quiz->user_id === $request->user()->id, 403);

        DB::transaction(function () use ($request, $quiz) {
            $quiz = Quiz::whereKey($quiz->id)->lockForUpdate()->firstOrFail();
            abort_if($quiz->completed, 409, 'This quiz has already been submitted.');

            $questions = $quiz->questions()->with('answers')->get();
            $answers = $request->input('answers', []);
            if (!is_array($answers) || count($answers) !== $questions->count()) {
                throw ValidationException::withMessages(['answers' => 'Answer every question before submitting.']);
            }

            $results = ['overall' => 0, 'total' => $questions->count(), 'categories' => []];
            foreach (self::CATEGORIES as $category) {
                $results['categories'][$category] = ['correct' => 0, 'total' => 0];
            }

            $xp = 0;
            foreach ($questions as $question) {
                $answerId = $answers[$question->id] ?? null;
                $answer = $question->answers->firstWhere('id', $answerId);
                if (!$answer) {
                    throw ValidationException::withMessages(['answers' => 'Choose a valid answer for every question.']);
                }
                $results['categories'][$question->category]['total']++;
                if ($answer->correct) {
                    $results['overall']++;
                    $results['categories'][$question->category]['correct']++;
                    $xp += $question->xp;
                }
            }

            $user = User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $user->xp += $xp;
            foreach ($results['categories'] as $category => $score) {
                $field = strtolower($category);
                [$correct, $total] = array_map('intval', explode('/', $user->$field));
                $user->$field = ($correct + $score['correct']) . '/' . ($total + $score['total']);
            }
            $user->save();

            $quiz->completed = true;
            $quiz->results = $results;
            $quiz->save();
        });

        return redirect()->route('quiz.results', $quiz);
    }

    public function results(Request $request, Quiz $quiz)
    {
        abort_unless($quiz->user_id === $request->user()->id, 403);
        abort_unless($quiz->completed, 404);
        return view('questions.results', ['results' => $quiz->results]);
    }
}
