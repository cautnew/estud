<?php

use App\Actions\ExamNotices\ImportExamNoticeFromJson;
use App\Models\ExamNotice;
use App\Models\Subject;
use App\Models\User;

test('it creates an exam notice with its subjects and topics from a JSON payload', function () {
    $admin = User::factory()->create();

    $notice = app(ImportExamNoticeFromJson::class)->handle([
        'name' => 'Concurso TRF',
        'exam_board' => 'FGV',
        'exam_date' => '2026-12-01',
        'subjects' => [
            [
                'name' => 'Direito Constitucional',
                'topics' => ['Princípios fundamentais', 'Direitos e garantias'],
            ],
            [
                'name' => 'Português',
                'topics' => ['Concordância verbal'],
            ],
        ],
    ], $admin);

    expect($notice->owner_id)->toBe($admin->id);
    expect($notice->name)->toBe('Concurso TRF');
    expect($notice->examSubjects)->toHaveCount(2);
    expect($notice->examSubjects->first()->examTopics)->toHaveCount(2);
});

test('it reuses an existing subject/topic instead of creating duplicates', function () {
    $admin = User::factory()->create();
    $subject = Subject::factory()->create(['name' => 'Direito Constitucional']);

    app(ImportExamNoticeFromJson::class)->handle([
        'name' => 'Concurso TRF',
        'exam_board' => 'FGV',
        'subjects' => [
            ['name' => 'Direito Constitucional', 'topics' => ['Princípios fundamentais']],
        ],
    ], $admin);

    expect(Subject::where('name', 'Direito Constitucional')->count())->toBe(1);
    expect($subject->topics()->where('name', 'Princípios fundamentais')->exists())->toBeTrue();
});

test('a failed import does not leave a partial exam notice behind', function () {
    $admin = User::factory()->create();

    try {
        app(ImportExamNoticeFromJson::class)->handle([
            'name' => 'Concurso TRF',
            'exam_board' => 'FGV',
            'subjects' => [
                ['name' => 'Direito Constitucional', 'topics' => [null]],
            ],
        ], $admin);
    } catch (Throwable) {
        // expected: an invalid topic name should abort the transaction
    }

    expect(ExamNotice::count())->toBe(0);
});
