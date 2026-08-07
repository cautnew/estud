<?php

use App\Models\ExamNotice;
use App\Models\User;

test('non-admin users are forbidden from managing exam notices', function () {
    $user = User::factory()->create();
    $user->assignRole('User');

    $response = $this->actingAs($user)->get(route('admin.exam-notices.index'));

    $response->assertForbidden();
});

test('guests are redirected to the login page', function () {
    $response = $this->get(route('admin.exam-notices.index'));

    $response->assertRedirect(route('login'));
});

test('admin can list every exam notice', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    ExamNotice::factory()->count(2)->create();

    $response = $this->actingAs($admin)->get(route('admin.exam-notices.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->has('examNotices', 2));
});

test('admin can register a new exam notice', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $response = $this->actingAs($admin)->post(route('admin.exam-notices.store'), [
        'name' => 'Concurso TRF',
        'exam_board' => 'FGV',
        'exam_date' => '2026-12-01',
    ]);

    $response->assertSessionHasNoErrors();
    $notice = ExamNotice::sole();
    expect($notice->owner_id)->toBe($admin->id);
    expect($notice->name)->toBe('Concurso TRF');
    $response->assertRedirect(route('admin.exam-notices.show', $notice));
});

test('admin can view an exam notice with its computed study progress', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $notice = ExamNotice::factory()->create();

    $response = $this->actingAs($admin)->get(route('admin.exam-notices.show', $notice));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('examNotice.id', $notice->id)
        ->has('progress.studiedPercentage')
        ->has('progress.remainingPercentage')
    );
});

test('admin can update an exam notice', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $notice = ExamNotice::factory()->create(['name' => 'Antigo']);

    $response = $this->actingAs($admin)->put(route('admin.exam-notices.update', $notice), [
        'name' => 'Novo nome',
        'exam_board' => $notice->exam_board,
    ]);

    $response->assertSessionHasNoErrors();
    expect($notice->refresh()->name)->toBe('Novo nome');
});

test('admin can delete an exam notice', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $notice = ExamNotice::factory()->create();

    $response = $this->actingAs($admin)->delete(route('admin.exam-notices.destroy', $notice));

    $response->assertRedirect(route('admin.exam-notices.index'));
    $this->assertModelMissing($notice);
});

test('admin can clone an exam notice', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $notice = ExamNotice::factory()->create(['name' => 'Concurso TRF']);

    $response = $this->actingAs($admin)->post(route('admin.exam-notices.duplicate', $notice));

    $response->assertSessionHasNoErrors();
    expect(ExamNotice::where('name', 'Concurso TRF (cópia)')->exists())->toBeTrue();
});
