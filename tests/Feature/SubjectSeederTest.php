<?php

use App\Models\Subject;
use Database\Seeders\SubjectSeeder;

test('it seeds the global legal subjects', function () {
    $this->seed(SubjectSeeder::class);

    expect(Subject::whereNull('owner_id')->count())->toBe(14);
    expect(Subject::where('name', 'Direito Constitucional')->whereNull('owner_id')->exists())->toBeTrue();
    expect(Subject::where('name', 'Direitos Humanos')->whereNull('owner_id')->exists())->toBeTrue();
});

test('seeding twice does not duplicate global subjects', function () {
    $this->seed(SubjectSeeder::class);
    $this->seed(SubjectSeeder::class);

    expect(Subject::whereNull('owner_id')->count())->toBe(14);
});
