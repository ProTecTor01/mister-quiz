<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        foreach (json_decode(file_get_contents(__DIR__ . '/questions.json'), true) as $item) {
            $question = \App\Models\Question::updateOrCreate(
                ['question' => $item['question']],
                ['xp' => $item['xp'], 'category' => $item['category']]
            );
            foreach ($item['answers'] as $answer) {
                \App\Models\Answer::updateOrCreate(
                    ['question_id' => $question->id, 'answer' => $answer['answer']],
                    ['correct' => $answer['correct']]
                );
            }
        }
    }
}
