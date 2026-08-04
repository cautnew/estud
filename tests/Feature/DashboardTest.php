<?php

use App\Models\StudySession;
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

test('the dashboard exposes the advanced stats indicators and chart datasets', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('stats.indicators.hoursStudied')
        ->has('stats.indicators.subjectsCompleted')
        ->has('stats.indicators.topicsCompleted')
        ->has('stats.indicators.reviews')
        ->has('stats.indicators.goals')
        ->has('stats.heatmap', 365)
        ->has('stats.monthlyEvolution', 6)
        ->has('stats.periodComparison')
        ->has('stats.radarBySubject')
        ->has('stats.subjectRanking')
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
