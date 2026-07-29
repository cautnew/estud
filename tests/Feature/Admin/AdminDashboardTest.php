<?php

use App\Models\User;

test('guests cannot access the admin dashboard', function () {
    $response = $this->get(route('admin.dashboard'));

    $response->assertRedirect(route('login'));
});

test('non-admin users are forbidden from the admin dashboard', function () {
    $user = User::factory()->create();
    $user->assignRole('User');

    $response = $this->actingAs($user)->get(route('admin.dashboard'));

    $response->assertForbidden();
});

test('admin users can access the admin dashboard', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertOk();
});
