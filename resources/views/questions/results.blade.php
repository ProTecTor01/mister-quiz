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
</main>
@endsection
