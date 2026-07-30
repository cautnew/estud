<?php

use App\Models\Subject;
use App\Models\Topic;
use App\Models\User;

test('guests cannot access subject management', function () {
    $response = $this->get(route('admin.subjects.index'));

    $response->assertRedirect(route('login'));
});

test('non-admin users are forbidden from subject management', function () {
    $user = User::factory()->create();
    $user->assignRole('User');

    $response = $this->actingAs($user)->get(route('admin.subjects.index'));

    $response->assertForbidden();
});

test('admin can list global and user-owned subjects', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    Subject::factory()->create();
    Subject::factory()->ownedBy()->create();

    $response = $this->actingAs($admin)->get(route('admin.subjects.index'));

    $response->assertOk();
});

test('admin can create a subject', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $response = $this->actingAs($admin)->post(route('admin.subjects.store'), [
        'name' => 'Direito Constitucional',
        'description' => 'Estudo da Constituição',
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('admin.subjects.index'));

    $subject = Subject::sole();
    expect($subject->name)->toBe('Direito Constitucional');
    expect($subject->owner_id)->toBe($admin->id);
});

test('admin can update any subject, including global ones', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $subject = Subject::factory()->create();

    $response = $this->actingAs($admin)->put(route('admin.subjects.update', $subject), [
        'name' => 'Novo nome',
        'description' => null,
    ]);

    $response->assertSessionHasNoErrors();
    expect($subject->refresh()->name)->toBe('Novo nome');
});

test('admin cannot delete a global subject', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $subject = Subject::factory()->create();

    $response = $this->actingAs($admin)->delete(route('admin.subjects.destroy', $subject));

    $response->assertForbidden();
    $this->assertModelExists($subject);
});

test('admin cannot delete a user-owned subject that still has topics', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $subject = Subject::factory()->ownedBy()->create();
    Topic::factory()->for($subject)->create();

    $response = $this->actingAs($admin)->delete(route('admin.subjects.destroy', $subject));

    $response->assertStatus(422);
    $this->assertModelExists($subject);
});

test('admin can delete a user-owned subject with no topics', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $subject = Subject::factory()->ownedBy()->create();

    $response = $this->actingAs($admin)->delete(route('admin.subjects.destroy', $subject));

    $response->assertRedirect(route('admin.subjects.index'));
    $this->assertModelMissing($subject);
});
