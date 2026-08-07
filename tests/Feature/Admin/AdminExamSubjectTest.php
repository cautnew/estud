<?php

use App\Models\ExamNotice;
use App\Models\ExamSubject;
use App\Models\Subject;
use App\Models\User;

test('non-admin users are forbidden from managing exam subjects', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $notice = ExamNotice::factory()->create();
    $subject = Subject::factory()->create();

    $response = $this->actingAs($user)->post(route('admin.exam-notices.exam-subjects.store', $notice), [
        'subject_id' => $subject->id,
    ]);

    $response->assertForbidden();
});

test('admin can add a subject to an exam notice', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $notice = ExamNotice::factory()->create();
    $subject = Subject::factory()->create();

    $response = $this->actingAs($admin)->post(route('admin.exam-notices.exam-subjects.store', $notice), [
        'subject_id' => $subject->id,
    ]);

    $response->assertSessionHasNoErrors();
    expect($notice->examSubjects()->where('subject_id', $subject->id)->exists())->toBeTrue();
});

test('admin cannot add the same subject twice to a notice', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $notice = ExamNotice::factory()->create();
    $subject = Subject::factory()->create();
    ExamSubject::factory()->for($notice)->for($subject)->create();

    $response = $this->actingAs($admin)->post(route('admin.exam-notices.exam-subjects.store', $notice), [
        'subject_id' => $subject->id,
    ]);

    $response->assertSessionHasErrors('subject_id');
});

test('admin can remove a subject (and its topics) from an exam notice', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $notice = ExamNotice::factory()->create();
    $examSubject = ExamSubject::factory()->for($notice)->create();

    $response = $this->actingAs($admin)->delete(route('admin.exam-subjects.destroy', $examSubject));

    $response->assertRedirect(route('admin.exam-notices.show', $notice));
    $this->assertModelMissing($examSubject);
});
