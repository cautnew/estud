<?php

use App\Actions\ExamNotices\DuplicateExamNotice;
use App\Models\ExamNotice;
use App\Models\ExamSubject;
use App\Models\ExamTopic;
use App\Models\User;

test('it copies a notice\'s subjects and topics into a new notice owned by the actor', function () {
    $owner = User::factory()->create();
    $admin = User::factory()->create();
    $notice = ExamNotice::factory()->for($owner, 'owner')->create(['name' => 'Concurso TRF']);
    $examSubject = ExamSubject::factory()->for($notice)->create();
    ExamTopic::factory()->for($examSubject)->create();
    ExamTopic::factory()->for($examSubject)->create();

    $copy = app(DuplicateExamNotice::class)->handle($notice, $admin);

    expect($copy->id)->not->toBe($notice->id);
    expect($copy->owner_id)->toBe($admin->id);
    expect($copy->name)->toBe('Concurso TRF (cópia)');
    expect($copy->examSubjects)->toHaveCount(1);
    expect($copy->examSubjects->first()->examTopics)->toHaveCount(2);
});

test('the original notice is left untouched', function () {
    $admin = User::factory()->create();
    $notice = ExamNotice::factory()->for($admin, 'owner')->create();
    $examSubject = ExamSubject::factory()->for($notice)->create();
    ExamTopic::factory()->for($examSubject)->create();

    app(DuplicateExamNotice::class)->handle($notice, $admin);

    expect($notice->examSubjects()->count())->toBe(1);
    expect(ExamNotice::count())->toBe(2);
});
