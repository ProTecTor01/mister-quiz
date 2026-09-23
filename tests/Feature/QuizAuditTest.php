<?php

namespace Tests\Feature;

use App\Models\Quiz;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuizAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_menu_and_authentication()
    {
        $this->get('/')->assertOk()->assertSee('Login')->assertSee('Leaderboard')->assertSee('Start Quiz');
        $this->get('/quiz')->assertRedirect('/login');
        $this->get('/profile')->assertRedirect('/login');
        $this->get('/register')->assertOk()->assertSee('Login');
        $this->get('/login')->assertOk()->assertSee('Register');

        $this->post('/register', [
            'username' => 'player', 'email' => 'player@example.com',
            'password' => 'password123', 'password_confirmation' => 'different',
        ])->assertSessionHasErrors('password');

        $this->post('/register', [
            'username' => 'player', 'email' => 'player@example.com',
            'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertRedirect('/');
        $this->assertAuthenticated();
        $this->get('/')->assertSee('Profile')->assertSee('Logout')->assertSee('Leaderboard')->assertSee('Start Quiz');
        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();

        $this->post('/login', ['email' => 'player@example.com', 'password' => 'wrong'])
            ->assertSessionHasErrors('email');
        $this->post('/login', ['email' => 'player@example.com', 'password' => 'password123'])
            ->assertRedirect('/');
        $this->assertAuthenticated();
    }

    public function test_quiz_is_stable_requires_every_answer_and_scores_once()
    {
        $this->seed();
        $user = User::factory()->create();
        $this->actingAs($user);

        $first = $this->get('/quiz')->assertOk();
        $quiz = Quiz::firstOrFail();
        $ids = $quiz->questions()->pluck('question.id')->sort()->values()->all();
        $this->assertCount(20, $ids);
        $this->assertEqualsCanonicalizing(
            ['Art', 'History', 'Geography', 'Science', 'Sports'],
            $quiz->questions()->distinct()->pluck('category')->all()
        );
        $this->get('/quiz')->assertOk();
        $this->assertSame($quiz->id, Quiz::latest('id')->first()->id);
        $this->assertSame($ids, Quiz::first()->questions()->pluck('question.id')->sort()->values()->all());

        $this->post('/quiz/' . $quiz->id, ['answers' => []])->assertSessionHasErrors('answers');
        $this->assertFalse($quiz->fresh()->completed);
        $this->get('/quiz/' . $quiz->id . '/results')->assertNotFound();

        $answers = [];
        $expectedXp = 0;
        foreach ($quiz->questions as $question) {
            $answers[$question->id] = $question->answers()->where('correct', true)->firstOrFail()->id;
            $expectedXp += $question->xp;
        }
        $bad = $answers;
        $bad[$ids[0]] = 999999;
        $this->post('/quiz/' . $quiz->id, ['answers' => $bad])->assertSessionHasErrors('answers');
        $this->assertSame(0, $user->fresh()->xp);

        $this->post('/quiz/' . $quiz->id, ['answers' => $answers])
            ->assertRedirect('/quiz/' . $quiz->id . '/results');
        $results = $this->get('/quiz/' . $quiz->id . '/results')
            ->assertOk()->assertSee('20 / 20 correct')->assertSee('Correct answers');
        foreach ($quiz->questions as $question) {
            $results->assertSeeText($question->question)
                ->assertSeeText($question->answers()->where('correct', true)->firstOrFail()->answer);
        }
        $this->assertSame($expectedXp, $user->fresh()->xp);
        foreach (['art', 'history', 'geography', 'science', 'sports'] as $category) {
            $this->assertSame('4/4', $user->fresh()->$category);
        }
        $this->post('/quiz/' . $quiz->id, ['answers' => $answers])->assertStatus(409);
        $this->assertSame($expectedXp, $user->fresh()->xp);

        $other = User::factory()->create();
        $this->actingAs($other)->get('/quiz/' . $quiz->id . '/results')->assertForbidden();
        $this->actingAs($other)->post('/quiz/' . $quiz->id, ['answers' => $answers])->assertForbidden();
    }

    public function test_profile_and_leaderboard()
    {
        $user = User::factory()->create(['username' => 'leader', 'xp' => 5000, 'art' => '3/4']);
        User::factory()->count(11)->create(['xp' => 100]);

        $leaderboard = $this->get('/leaderboard')->assertOk()->assertSee('leader')->assertSee('Correct answers');
        $this->assertSame(11, substr_count($leaderboard->getContent(), '<tr>'));

        $this->actingAs($user->fresh())->get('/profile')->assertOk()
            ->assertSee('leader')->assertSee($user->email)->assertSee('5000 XP')
            ->assertSee('Epic Quizer')->assertSee('75%')->assertSee('Art');
        $this->get('/profile/' . $user->id)->assertNotFound();
    }
}
