<?php

use App\Enums\ScheduleType;
use App\Models\Schedule;
use App\Models\ScheduleItem;
use App\Models\User;

test('guests cannot access schedules', function () {
    $response = $this->get(route('schedules.index'));

    $response->assertRedirect(route('login'));
});

test('a user only sees their own schedules in the index', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $own = Schedule::factory()->for($user, 'owner')->create();
    Schedule::factory()->create();

    $response = $this->actingAs($user)->get(route('schedules.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('schedules', 1)
        ->where('schedules.0.id', $own->id)
    );
});

test('a user can create a schedule', function () {
    $user = User::factory()->create();
    $user->assignRole('User');

    $response = $this->actingAs($user)->post(route('schedules.store'), [
        'name' => 'Cronograma TRF',
        'type' => ScheduleType::Weekly->value,
    ]);

    $response->assertSessionHasNoErrors();

    $schedule = Schedule::sole();
    expect($schedule->owner_id)->toBe($user->id);
    expect($schedule->type)->toBe(ScheduleType::Weekly);
    $response->assertRedirect(route('schedules.show', $schedule));
});

test('a user can view their own schedule', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $schedule = Schedule::factory()->for($user, 'owner')->create();

    $response = $this->actingAs($user)->get(route('schedules.show', $schedule));

    $response->assertOk();
});

test('a user cannot view another user\'s schedule', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $schedule = Schedule::factory()->create();

    $response = $this->actingAs($user)->get(route('schedules.show', $schedule));

    $response->assertForbidden();
});

test('a user can update their own schedule\'s name and type', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $schedule = Schedule::factory()->for($user, 'owner')->weekly()->create();

    $response = $this->actingAs($user)->put(route('schedules.update', $schedule), [
        'name' => 'Atualizado',
        'type' => ScheduleType::Monthly->value,
    ]);

    $response->assertSessionHasNoErrors();
    $schedule->refresh();
    expect($schedule->name)->toBe('Atualizado');
    expect($schedule->type)->toBe(ScheduleType::Monthly);
});

test('a user cannot update another user\'s schedule', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $schedule = Schedule::factory()->create();

    $response = $this->actingAs($user)->put(route('schedules.update', $schedule), [
        'name' => 'Atualizado',
    ]);

    $response->assertForbidden();
});

test('a user can delete their own schedule, cascading its items', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $schedule = Schedule::factory()->for($user, 'owner')->create();
    $item = ScheduleItem::factory()->for($schedule)->create();

    $response = $this->actingAs($user)->delete(route('schedules.destroy', $schedule));

    $response->assertRedirect(route('schedules.index'));
    expect(Schedule::find($schedule->id))->toBeNull();
    expect(ScheduleItem::find($item->id))->toBeNull();
});

test('a user cannot delete another user\'s schedule', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $schedule = Schedule::factory()->create();

    $response = $this->actingAs($user)->delete(route('schedules.destroy', $schedule));

    $response->assertForbidden();
});
