<?php

use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Facades\Route;

test('guests cannot access subjects', function () {
    $response = $this->get(route('subjects.index'));

    $response->assertRedirect(route('login'));
});

test('a user sees global subjects and their own, but not other users\' subjects', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $global = Subject::factory()->create();
    $own = Subject::factory()->ownedBy($user)->create();
    $others = Subject::factory()->ownedBy()->create();

    $response = $this->actingAs($user)->get(route('subjects.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('subjects', 2)
    );

    expect(Subject::query()->visibleTo($user)->pluck('id')->sort()->values()->all())
        ->toBe(collect([$global->id, $own->id])->sort()->values()->all());

    expect(Subject::query()->visibleTo($user)->pluck('id'))->not->toContain($others->id);
});

test('a user can create their own subject', function () {
    $user = User::factory()->create();
    $user->assignRole('User');

    $response = $this->actingAs($user)->post(route('subjects.store'), [
        'name' => 'Direito Civil',
        'description' => null,
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('subjects.index'));

    $subject = Subject::sole();
    expect($subject->owner_id)->toBe($user->id);
});

test('a user can view a global subject', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $subject = Subject::factory()->create();

    $response = $this->actingAs($user)->get(route('subjects.show', $subject));

    $response->assertOk();
});

test('a user cannot view another user\'s subject', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $subject = Subject::factory()->ownedBy()->create();

    $response = $this->actingAs($user)->get(route('subjects.show', $subject));

    $response->assertForbidden();
});

test('a user can update their own subject but not a global one', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $own = Subject::factory()->ownedBy($user)->create();
    $global = Subject::factory()->create();

    $this->actingAs($user)
        ->put(route('subjects.update', $own), ['name' => 'Atualizado', 'description' => null])
        ->assertSessionHasNoErrors();
    expect($own->refresh()->name)->toBe('Atualizado');

    $this->actingAs($user)
        ->put(route('subjects.update', $global), ['name' => 'Atualizado', 'description' => null])
        ->assertForbidden();
});

test('there is no route for a user to delete a subject', function () {
    expect(Route::has('subjects.destroy'))->toBeFalse();
});
