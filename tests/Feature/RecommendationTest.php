<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('recommendations.index'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can view their recommendations', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('recommendations.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('healthScore.score')
        ->has('healthScore.label')
        ->has('healthScore.components.consistency')
        ->has('healthScore.components.goals')
        ->has('healthScore.components.reviews')
        ->has('healthScore.components.schedule')
        ->has('studyToday')
        ->has('reviewToday')
        ->has('overdue')
    );
});
