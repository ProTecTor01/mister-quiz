@extends('app')

@section('content')
<a class="simple-link" href="{{ route('home') }}">Home</a>
<main class="content text-center">
    <h1 class="title">Results</h1>
    <p class="score">{{ $results['overall'] }} / {{ $results['total'] }} correct</p>
    <div class="results-wrapper">
        @foreach ($results['categories'] as $category => $score)
        <div class="result">
            <h2>{{ $category }}</h2>
            <p>{{ $score['correct'] }} / {{ $score['total'] }}</p>
        </div>
        @endforeach
    </div>
    <section class="answers-review" aria-labelledby="answers-title">
        <h2 id="answers-title">Correct answers</h2>
        <ol>
            @foreach ($questions as $question)
            <li>
                <p class="review-question">{{ $question->question }}</p>
                @foreach ($question->answers as $answer)
                <p class="review-answer">{{ $answer->answer }}</p>
                @endforeach
            </li>
            @endforeach
        </ol>
    </section>
</main>
@endsection
