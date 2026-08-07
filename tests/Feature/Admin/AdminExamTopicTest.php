<?php

use App\Models\ExamSubject;
use App\Models\ExamTopic;
use App\Models\Topic;
use App\Models\User;

test('non-admin users are forbidden from managing exam topics', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $examSubject = ExamSubject::factory()->create();
    $topic = Topic::factory()->for($examSubject->subject)->create();

    $response = $this->actingAs($user)->post(route('admin.exam-subjects.exam-topics.store', $examSubject), [
        'topic_id' => $topic->id,
    ]);

    $response->assertForbidden();
});

test('admin can add a topic to an exam subject', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $examSubject = ExamSubject::factory()->create();
    $topic = Topic::factory()->for($examSubject->subject)->create();

    $response = $this->actingAs($admin)->post(route('admin.exam-subjects.exam-topics.store', $examSubject), [
        'topic_id' => $topic->id,
    ]);

    $response->assertSessionHasNoErrors();
    expect($examSubject->examTopics()->where('topic_id', $topic->id)->exists())->toBeTrue();
});

test('admin cannot add a topic that does not belong to the exam subject\'s subject', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $examSubject = ExamSubject::factory()->create();
    $unrelatedTopic = Topic::factory()->create();

    $response = $this->actingAs($admin)->post(route('admin.exam-subjects.exam-topics.store', $examSubject), [
        'topic_id' => $unrelatedTopic->id,
    ]);

    $response->assertSessionHasErrors('topic_id');
});

test('admin can remove a topic from an exam subject', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $examSubject = ExamSubject::factory()->create();
    $examTopic = ExamTopic::factory()->for($examSubject)->create();

    $response = $this->actingAs($admin)->delete(route('admin.exam-topics.destroy', $examTopic));

    $response->assertRedirect(route('admin.exam-notices.show', $examSubject->examNotice));
    $this->assertModelMissing($examTopic);
});
