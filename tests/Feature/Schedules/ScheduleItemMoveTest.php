<?php

use App\Models\Schedule;
use App\Models\ScheduleItem;
use App\Models\User;

test('a user can move their own item to a new day, time, and priority', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $schedule = Schedule::factory()->for($user, 'owner')->weekly()->create();
    $item = ScheduleItem::factory()->for($schedule)->create(['day_of_week' => 1, 'priority' => 0]);

    $response = $this->actingAs($user)->patch(route('schedule-items.move', $item), [
        'day_of_week' => 4,
        'start_time' => '15:00',
        'duration_minutes' => 90,
        'priority' => 3,
    ]);

    $response->assertSessionHasNoErrors();
    $item->refresh();
    expect($item->day_of_week)->toBe(4);
    expect($item->start_time->format('H:i'))->toBe('15:00');
    expect($item->duration_minutes)->toBe(90);
    expect($item->priority)->toBe(3);
});

test('a user can move an item\'s priority alone, leaving day and time unchanged', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $schedule = Schedule::factory()->for($user, 'owner')->weekly()->create();
    $item = ScheduleItem::factory()->for($schedule)->create([
        'day_of_week' => 2,
        'start_time' => '10:00',
        'duration_minutes' => 60,
        'priority' => 0,
    ]);

    $response = $this->actingAs($user)->patch(route('schedule-items.move', $item), [
        'day_of_week' => $item->day_of_week,
        'start_time' => '10:00',
        'duration_minutes' => 60,
        'priority' => 5,
    ]);

    $response->assertSessionHasNoErrors();
    $item->refresh();
    expect($item->day_of_week)->toBe(2);
    expect($item->start_time->format('H:i'))->toBe('10:00');
    expect($item->priority)->toBe(5);
});

test('a user cannot move an item belonging to another user\'s schedule', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $schedule = Schedule::factory()->weekly()->create();
    $item = ScheduleItem::factory()->for($schedule)->create();

    $response = $this->actingAs($user)->patch(route('schedule-items.move', $item), [
        'day_of_week' => $item->day_of_week,
        'start_time' => '15:00',
        'duration_minutes' => 90,
        'priority' => 3,
    ]);

    $response->assertForbidden();
});

test('moving an item with day fields inconsistent with the schedule\'s type fails validation', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $schedule = Schedule::factory()->for($user, 'owner')->weekly()->create();
    $item = ScheduleItem::factory()->for($schedule)->create(['day_of_week' => 1]);

    $response = $this->actingAs($user)->patch(route('schedule-items.move', $item), [
        'day_of_month' => 10,
        'start_time' => '15:00',
        'duration_minutes' => 90,
        'priority' => 3,
    ]);

    $response->assertSessionHasErrors('day_of_month');
});

test('the move endpoint returns a redirect back', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $schedule = Schedule::factory()->for($user, 'owner')->weekly()->create();
    $item = ScheduleItem::factory()->for($schedule)->create(['day_of_week' => 1]);

    $response = $this->actingAs($user)
        ->from(route('schedules.show', $schedule))
        ->patch(route('schedule-items.move', $item), [
            'day_of_week' => 1,
            'start_time' => '15:00',
            'duration_minutes' => 90,
            'priority' => 3,
        ]);

    $response->assertRedirect(route('schedules.show', $schedule));
});
