<?php

use App\Actions\QuestionLogs\CalculateQuestionStats;
use App\Models\QuestionLog;
use App\Models\Subject;
use App\Models\User;

test('a user with no logs gets zeroed-out stats', function () {
    $user = User::factory()->create();

    $stats = (new CalculateQuestionStats)->handle($user);

    expect($stats['overall'])->toBe(['total' => 0, 'correct' => 0, 'incorrect' => 0, 'accuracy_rate' => 0.0]);
    expect($stats['bySubject'])->toBe([]);
    expect($stats['history'])->toBe([]);
});

test('it aggregates overall totals and accuracy rate across all logs', function () {
    $user = User::factory()->create();
    QuestionLog::factory()->for($user, 'owner')->create(['total' => 10, 'correct' => 7, 'incorrect' => 3]);
    QuestionLog::factory()->for($user, 'owner')->create(['total' => 10, 'correct' => 3, 'incorrect' => 7]);

    $stats = (new CalculateQuestionStats)->handle($user);

    expect($stats['overall'])->toBe(['total' => 20, 'correct' => 10, 'incorrect' => 10, 'accuracy_rate' => 50.0]);
});

test('it groups accuracy by subject', function () {
    $user = User::factory()->create();
    $constitucional = Subject::factory()->create(['name' => 'Constitucional']);
    $portugues = Subject::factory()->create(['name' => 'Português']);

    QuestionLog::factory()->for($user, 'owner')->for($constitucional)->create(['total' => 10, 'correct' => 8, 'incorrect' => 2]);
    QuestionLog::factory()->for($user, 'owner')->for($portugues)->create(['total' => 10, 'correct' => 4, 'incorrect' => 6]);

    $stats = (new CalculateQuestionStats)->handle($user);

    expect($stats['bySubject'])->toBe([
        ['subject_id' => $constitucional->id, 'subject' => 'Constitucional', 'total' => 10, 'correct' => 8, 'accuracy_rate' => 80.0],
        ['subject_id' => $portugues->id, 'subject' => 'Português', 'total' => 10, 'correct' => 4, 'accuracy_rate' => 40.0],
    ]);
});

test('it groups accuracy by date for the historical evolution chart', function () {
    $user = User::factory()->create();
    QuestionLog::factory()->for($user, 'owner')->create(['date' => '2026-08-01', 'total' => 10, 'correct' => 5, 'incorrect' => 5]);
    QuestionLog::factory()->for($user, 'owner')->create(['date' => '2026-08-01', 'total' => 10, 'correct' => 5, 'incorrect' => 5]);
    QuestionLog::factory()->for($user, 'owner')->create(['date' => '2026-08-02', 'total' => 10, 'correct' => 10, 'incorrect' => 0]);

    $stats = (new CalculateQuestionStats)->handle($user);

    expect($stats['history'])->toBe([
        ['date' => '2026-08-01', 'total' => 20, 'correct' => 10, 'accuracy_rate' => 50.0],
        ['date' => '2026-08-02', 'total' => 10, 'correct' => 10, 'accuracy_rate' => 100.0],
    ]);
});

test('logs from other users are excluded from every indicator', function () {
    $user = User::factory()->create();
    QuestionLog::factory()->create(['total' => 100, 'correct' => 100, 'incorrect' => 0]);

    $stats = (new CalculateQuestionStats)->handle($user);

    expect($stats['overall']['total'])->toBe(0);
});
