<?php

use App\Models\Subject;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Support\Facades\Route;

test('a user can create a topic on their own subject', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $subject = Subject::factory()->ownedBy($user)->create();

    $response = $this->actingAs($user)->post(route('subjects.topics.store', $subject), [
        'name' => 'Princípios fundamentais',
        'description' => null,
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('subjects.show', $subject));
    expect($subject->topics()->where('name', 'Princípios fundamentais')->exists())->toBeTrue();
});

test('a user cannot create a topic on a global subject', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $subject = Subject::factory()->create();

    $response = $this->actingAs($user)->post(route('subjects.topics.store', $subject), [
        'name' => 'Princípios fundamentais',
    ]);

    $response->assertForbidden();
});

test('a user cannot create a topic on another user\'s subject', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $subject = Subject::factory()->ownedBy()->create();

    $response = $this->actingAs($user)->post(route('subjects.topics.store', $subject), [
        'name' => 'Princípios fundamentais',
    ]);

    $response->assertForbidden();
});

test('a user can update a topic on their own subject', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $subject = Subject::factory()->ownedBy($user)->create();
    $topic = Topic::factory()->for($subject)->create();

    $response = $this->actingAs($user)->put(route('topics.update', $topic), [
        'name' => 'Novo nome',
        'description' => null,
    ]);

    $response->assertSessionHasNoErrors();
    expect($topic->refresh()->name)->toBe('Novo nome');
});

test('a user cannot update a topic on a subject they do not own', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $subject = Subject::factory()->create();
    $topic = Topic::factory()->for($subject)->create();

    $response = $this->actingAs($user)->put(route('topics.update', $topic), [
        'name' => 'Novo nome',
    ]);

    $response->assertForbidden();
});

test('there is no route for a user to delete a topic', function () {
    expect(Route::has('subjects.topics.destroy'))->toBeFalse();
    expect(Route::has('topics.destroy'))->toBeFalse();
});
