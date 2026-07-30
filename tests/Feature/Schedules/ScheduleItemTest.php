<?php

use App\Models\Schedule;
use App\Models\ScheduleItem;
use App\Models\Subject;
use App\Models\User;

test('a user can add an item to their own weekly schedule with a valid day of week', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $schedule = Schedule::factory()->for($user, 'owner')->weekly()->create();
    $subject = Subject::factory()->create();

    $response = $this->actingAs($user)->post(route('schedules.schedule-items.store', $schedule), [
        'subject_id' => $subject->id,
        'day_of_week' => 2,
        'start_time' => '14:30',
        'duration_minutes' => 60,
    ]);

    $response->assertSessionHasNoErrors();
    expect($schedule->scheduleItems()->where('subject_id', $subject->id)->exists())->toBeTrue();
});

test('a user can add an item to their own monthly schedule with a valid day of month', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $schedule = Schedule::factory()->for($user, 'owner')->monthly()->create();
    $subject = Subject::factory()->create();

    $response = $this->actingAs($user)->post(route('schedules.schedule-items.store', $schedule), [
        'subject_id' => $subject->id,
        'day_of_month' => 15,
        'start_time' => '09:00',
        'duration_minutes' => 45,
    ]);

    $response->assertSessionHasNoErrors();
    expect($schedule->scheduleItems()->where('subject_id', $subject->id)->exists())->toBeTrue();
});

test('adding an item to a weekly schedule without day_of_week fails validation', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $schedule = Schedule::factory()->for($user, 'owner')->weekly()->create();
    $subject = Subject::factory()->create();

    $response = $this->actingAs($user)->post(route('schedules.schedule-items.store', $schedule), [
        'subject_id' => $subject->id,
        'start_time' => '14:30',
        'duration_minutes' => 60,
    ]);

    $response->assertSessionHasErrors('day_of_week');
});

test('adding an item to a weekly schedule with day_of_month present fails validation', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $schedule = Schedule::factory()->for($user, 'owner')->weekly()->create();
    $subject = Subject::factory()->create();

    $response = $this->actingAs($user)->post(route('schedules.schedule-items.store', $schedule), [
        'subject_id' => $subject->id,
        'day_of_week' => 2,
        'day_of_month' => 10,
        'start_time' => '14:30',
        'duration_minutes' => 60,
    ]);

    $response->assertSessionHasErrors('day_of_month');
});

test('adding an item to a monthly schedule without day_of_month fails validation', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $schedule = Schedule::factory()->for($user, 'owner')->monthly()->create();
    $subject = Subject::factory()->create();

    $response = $this->actingAs($user)->post(route('schedules.schedule-items.store', $schedule), [
        'subject_id' => $subject->id,
        'start_time' => '09:00',
        'duration_minutes' => 45,
    ]);

    $response->assertSessionHasErrors('day_of_month');
});

test('adding an item to a daily schedule with either day field present fails validation', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $schedule = Schedule::factory()->for($user, 'owner')->daily()->create();
    $subject = Subject::factory()->create();

    $response = $this->actingAs($user)->post(route('schedules.schedule-items.store', $schedule), [
        'subject_id' => $subject->id,
        'day_of_week' => 2,
        'start_time' => '09:00',
        'duration_minutes' => 45,
    ]);

    $response->assertSessionHasErrors('day_of_week');
});

test('day_of_week out of range fails validation', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $schedule = Schedule::factory()->for($user, 'owner')->weekly()->create();
    $subject = Subject::factory()->create();

    $response = $this->actingAs($user)->post(route('schedules.schedule-items.store', $schedule), [
        'subject_id' => $subject->id,
        'day_of_week' => 7,
        'start_time' => '09:00',
        'duration_minutes' => 45,
    ]);

    $response->assertSessionHasErrors('day_of_week');
});

test('day_of_month out of range fails validation', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $schedule = Schedule::factory()->for($user, 'owner')->monthly()->create();
    $subject = Subject::factory()->create();

    $response = $this->actingAs($user)->post(route('schedules.schedule-items.store', $schedule), [
        'subject_id' => $subject->id,
        'day_of_month' => 32,
        'start_time' => '09:00',
        'duration_minutes' => 45,
    ]);

    $response->assertSessionHasErrors('day_of_month');
});

test('duration_minutes below minimum fails validation', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $schedule = Schedule::factory()->for($user, 'owner')->weekly()->create();
    $subject = Subject::factory()->create();

    $response = $this->actingAs($user)->post(route('schedules.schedule-items.store', $schedule), [
        'subject_id' => $subject->id,
        'day_of_week' => 1,
        'start_time' => '09:00',
        'duration_minutes' => 4,
    ]);

    $response->assertSessionHasErrors('duration_minutes');
});

test('duration_minutes above maximum fails validation', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $schedule = Schedule::factory()->for($user, 'owner')->weekly()->create();
    $subject = Subject::factory()->create();

    $response = $this->actingAs($user)->post(route('schedules.schedule-items.store', $schedule), [
        'subject_id' => $subject->id,
        'day_of_week' => 1,
        'start_time' => '09:00',
        'duration_minutes' => 1441,
    ]);

    $response->assertSessionHasErrors('duration_minutes');
});

test('start_time in an invalid format fails validation', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $schedule = Schedule::factory()->for($user, 'owner')->weekly()->create();
    $subject = Subject::factory()->create();

    $response = $this->actingAs($user)->post(route('schedules.schedule-items.store', $schedule), [
        'subject_id' => $subject->id,
        'day_of_week' => 1,
        'start_time' => '2:30 PM',
        'duration_minutes' => 60,
    ]);

    $response->assertSessionHasErrors('start_time');
});

test('a user cannot add an item to another user\'s schedule', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $schedule = Schedule::factory()->weekly()->create();
    $subject = Subject::factory()->create();

    $response = $this->actingAs($user)->post(route('schedules.schedule-items.store', $schedule), [
        'subject_id' => $subject->id,
        'day_of_week' => 1,
        'start_time' => '09:00',
        'duration_minutes' => 60,
    ]);

    $response->assertForbidden();
});

test('a user can update an item\'s start_time, duration, and priority on their own schedule', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $schedule = Schedule::factory()->for($user, 'owner')->weekly()->create();
    $item = ScheduleItem::factory()->for($schedule)->create();

    $response = $this->actingAs($user)->put(route('schedule-items.update', $item), [
        'start_time' => '16:00',
        'duration_minutes' => 90,
        'priority' => 2,
        'day_of_week' => $item->day_of_week,
    ]);

    $response->assertSessionHasNoErrors();
    $item->refresh();
    expect($item->start_time->format('H:i'))->toBe('16:00');
    expect($item->duration_minutes)->toBe(90);
    expect($item->priority)->toBe(2);
});

test('a user cannot update an item on another user\'s schedule', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $schedule = Schedule::factory()->weekly()->create();
    $item = ScheduleItem::factory()->for($schedule)->create();

    $response = $this->actingAs($user)->put(route('schedule-items.update', $item), [
        'duration_minutes' => 90,
    ]);

    $response->assertForbidden();
});

test('a user can remove an item from their own schedule', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $schedule = Schedule::factory()->for($user, 'owner')->weekly()->create();
    $item = ScheduleItem::factory()->for($schedule)->create();

    $response = $this->actingAs($user)->delete(route('schedule-items.destroy', $item));

    $response->assertSessionHasNoErrors();
    expect(ScheduleItem::find($item->id))->toBeNull();
});

test('a user cannot remove an item from another user\'s schedule', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $schedule = Schedule::factory()->weekly()->create();
    $item = ScheduleItem::factory()->for($schedule)->create();

    $response = $this->actingAs($user)->delete(route('schedule-items.destroy', $item));

    $response->assertForbidden();
});
