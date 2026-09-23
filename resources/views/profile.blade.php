@extends('app')

@section('content')
<a class="simple-link" href="{{ route('home') }}">Home</a>
<main class="content text-center">
    <h1 class="title">Profile</h1>
    <p class="score">{{ $user->username }}</p>
    <p>{{ $user->email }}</p>
    <p>{{ $user->xp }} XP · {{ $rank }}</p>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Category</th><th>Correct</th><th>Answered</th><th>Accuracy</th></tr></thead>
            <tbody>
                @foreach ($scores as $category => $score)
                <tr><th scope="row">{{ $category }}</th><td>{{ $score['correct'] }}</td><td>{{ $score['total'] }}</td><td>{{ $score['percentage'] }}%</td></tr>
                @endforeach
            </tbody>
        </table>
    </div>
</main>
@endsection
