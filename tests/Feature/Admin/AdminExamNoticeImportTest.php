<?php

use App\Models\ExamNotice;
use App\Models\User;

test('non-admin users are forbidden from importing exam notices', function () {
    $user = User::factory()->create();
    $user->assignRole('User');

    $response = $this->actingAs($user)->post(route('admin.exam-notices.import.store'), [
        'payload' => json_encode(['name' => 'x']),
    ]);

    $response->assertForbidden();
});

test('admin can import an exam notice from a JSON payload', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $payload = json_encode([
        'name' => 'Concurso TRF',
        'exam_board' => 'FGV',
        'subjects' => [
            ['name' => 'Direito Constitucional', 'topics' => ['Princípios fundamentais']],
        ],
    ]);

    $response = $this->actingAs($admin)->post(route('admin.exam-notices.import.store'), [
        'payload' => $payload,
    ]);

    $response->assertSessionHasNoErrors();
    $notice = ExamNotice::sole();
    expect($notice->owner_id)->toBe($admin->id);
    expect($notice->examSubjects()->count())->toBe(1);
});

test('an invalid JSON payload fails validation', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $response = $this->actingAs($admin)->post(route('admin.exam-notices.import.store'), [
        'payload' => 'not json',
    ]);

    $response->assertSessionHasErrors('data');
    expect(ExamNotice::count())->toBe(0);
});
