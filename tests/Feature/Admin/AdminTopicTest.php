<?php

use App\Models\Subject;
use App\Models\Topic;
use App\Models\User;

test('non-admin users are forbidden from managing topics through admin routes', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $subject = Subject::factory()->create();

    $response = $this->actingAs($user)->post(route('admin.subjects.topics.store', $subject), [
        'name' => 'Princípios fundamentais',
    ]);

    $response->assertForbidden();
});

test('admin can create a topic on any subject, including global ones', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $subject = Subject::factory()->create();

    $response = $this->actingAs($admin)->post(route('admin.subjects.topics.store', $subject), [
        'name' => 'Princípios fundamentais',
        'description' => null,
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('admin.subjects.show', $subject));
    expect($subject->topics()->where('name', 'Princípios fundamentais')->exists())->toBeTrue();
});

test('admin can update any topic', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $topic = Topic::factory()->create();

    $response = $this->actingAs($admin)->put(route('admin.topics.update', $topic), [
        'name' => 'Novo nome',
        'description' => null,
    ]);

    $response->assertSessionHasNoErrors();
    expect($topic->refresh()->name)->toBe('Novo nome');
});

test('admin can delete any topic', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $topic = Topic::factory()->create();

    $response = $this->actingAs($admin)->delete(route('admin.topics.destroy', $topic));

    $response->assertRedirect(route('admin.subjects.show', $topic->subject));
    $this->assertModelMissing($topic);
});
