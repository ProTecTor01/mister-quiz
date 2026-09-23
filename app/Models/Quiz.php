<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = ['completed'];

    protected $casts = [
        'completed' => 'boolean',
        'results' => 'array',
    ];

    public function questions()
    {
        return $this->belongsToMany(Question::class, 'question_quiz');
    }
}
