<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('reports.index'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can view the reports page with the default report type', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('reports.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('selectedType', 'productivity')
        ->has('report.title')
        ->has('report.summary')
        ->has('report.columns')
        ->has('report.rows')
        ->has('reportTypes', 5)
        ->has('filters.date_from')
        ->has('filters.date_to')
        ->has('availableSubjects')
        ->has('availableStudyPlans')
    );
});

test('each report type can be selected via the type query parameter', function (string $type) {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('reports.index', ['type' => $type]));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->where('selectedType', $type));
})->with(['productivity', 'evolution', 'reviews', 'performance', 'goals']);

test('an unknown report type falls back to productivity', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('reports.index', ['type' => 'not-a-real-type']));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->where('selectedType', 'productivity'));
});

test('the pdf export streams a pdf download', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('reports.export.pdf', ['type' => 'productivity']));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/pdf');
});

test('the excel export streams an xlsx download', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('reports.export.excel', ['type' => 'productivity']));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
});

test('guests cannot export reports', function () {
    $this->get(route('reports.export.pdf'))->assertRedirect(route('login'));
    $this->get(route('reports.export.excel'))->assertRedirect(route('login'));
});
