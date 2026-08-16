<?php

use App\Models\Subject;
use App\Models\User;

test('guests cannot access the activity log', function () {
    $response = $this->get(route('admin.activity-log.index'));

    $response->assertRedirect(route('login'));
});

test('non-admin users are forbidden from the activity log', function () {
    $user = User::factory()->create();
    $user->assignRole('User');

    $response = $this->actingAs($user)->get(route('admin.activity-log.index'));

    $response->assertForbidden();
});

test('creating an audited model records an activity entry', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $this->actingAs($admin)->post(route('admin.subjects.store'), [
        'name' => 'Direito Constitucional',
        'description' => null,
    ]);

    $subject = Subject::sole();

    $this->assertDatabaseHas('activity_log', [
        'subject_type' => Subject::class,
        'subject_id' => $subject->id,
        'causer_id' => $admin->id,
        'event' => 'created',
    ]);
});

test('admin can list and filter the activity log', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    Subject::factory()->create();

    $response = $this->actingAs($admin)->get(route('admin.activity-log.index', [
        'event' => 'created',
        'subject_type' => Subject::class,
    ]));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/activity-log/index')
        ->has('activities.data', 1)
        ->where('activities.data.0.event', 'created')
    );
});
