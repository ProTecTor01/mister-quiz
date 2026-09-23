@props(['question'=>$question])

<div class="mb4">
    <p class="center question-title">{{ $question->question }} <small>({{ $question->category }}, {{ $question->xp }} XP)</small></p>

    <div class="checkboxes-wrapper">
        @foreach ($question->answers as $answer)
        <label class="checkbox">
            <input type="radio" name="answers[{{ $question->id }}]" value="{{ $answer->id }}"
                @if ((string) old('answers.' . $question->id) === (string) $answer->id) checked @endif required>
            <span>{{ $answer->answer }}</span>
        </label>
        @endforeach
    </div>

    <div class="center line"></div>
</div>
