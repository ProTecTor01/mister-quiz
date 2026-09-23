@extends('app')

@section('content')
<a class="simple-link" href="{{ route('home') }}">Home</a>
<main class="content text-center">
    <h1 class="title">Leaderboard</h1>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Rank</th><th>Username</th><th>XP</th><th>Correct answers</th></tr></thead>
            <tbody>
                @forelse ($players as $player)
                <tr>
                    <td>{{ $loop->iteration }}</td><th scope="row">{{ $player->username }}</th>
                    <td>{{ $player->xp }}</td>
                    <td>{{ array_sum(array_map(function ($score) { return (int) explode('/', $score)[0]; }, [$player->art, $player->history, $player->geography, $player->science, $player->sports])) }}</td>
                </tr>
                @empty
                <tr><td colspan="4">No players yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</main>
@endsection
