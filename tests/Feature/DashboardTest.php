<?php

use App\Models\StudySession;
use App\Models\Subject;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('the dashboard summarizes today\'s and this week\'s completed study time', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create(['name' => 'Direito Constitucional']);

    StudySession::factory()->for($user, 'owner')->for($subject)->completed()->create([
        'date' => today(),
        'duration_seconds' => 1800,
    ]);
    StudySession::factory()->for($user, 'owner')->for($subject)->completed()->create([
        'date' => today()->subDays(3),
        'duration_seconds' => 900,
    ]);
    StudySession::factory()->create(['date' => today()]); // another user, must not count

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('summary.todaySeconds', 1800)
        ->where('summary.weekSeconds', 2700)
        ->where('summary.completedThisWeek', 2)
        ->where('summary.bySubject.0.subject', 'Direito Constitucional')
        ->where('summary.bySubject.0.seconds', 2700)
    );
});

test('the dashboard surfaces the currently active session for the stopwatch widget', function () {
    $user = User::factory()->create();
    $session = StudySession::factory()->for($user, 'owner')->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('activeSession.id', $session->id)
    );
});
