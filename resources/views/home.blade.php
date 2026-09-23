@extends('app')

@section('content')

@auth
<a class="top-left-corner blue-btn" href="{{ route('profile') }}">Profile</a>
@endauth
@if (session('error'))
<p class="error-msg text-center" role="alert">{{ session('error') }}</p>
@endif

@guest
<a class="top-left-corner blue-btn" href="{{ route('login') }}">Login</a>

@endguest

<a class="top-right-corner blue-btn" href="{{ route('leaderboard') }}">Leaderboard</a>

@auth
<form class="bottom-right-corner" method="post" action="{{ route('logout') }}">
    @csrf
    <button class="red-btn" type="submit">Logout</button>
</form>
@endauth

<div class="main-img">
    <img src="{{ asset('images/mister_quiz.png') }}" alt="">
    <p class="title">Mister Quiz</p>

    <a style="margin-bottom:20px" class="green-btn center" href="{{ route('quiz') }}">Start Quiz</a>
</div>

@endsection
