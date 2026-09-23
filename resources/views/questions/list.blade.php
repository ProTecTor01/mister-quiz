@extends('app')

@section('content')

<a class="simple-link" href="{{ route('home') }}">Home</a>
<h1 class="title">Quiz</h1>
@error('answers')
<p class="error-msg text-center" role="alert">{{ $message }}</p>
@enderror
<form action="{{ route('quiz.submit', $quiz) }}" method="post">
    @csrf

    @foreach ($quiz->questions as $question)
    <x-question :question="$question" />
    @endforeach

    <button type="submit" class="center green-btn">Submit</button>
</form>


@endsection
