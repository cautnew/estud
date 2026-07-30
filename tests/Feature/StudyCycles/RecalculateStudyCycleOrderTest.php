<?php

use App\Actions\StudyCycles\RecalculateStudyCycleOrder;
use App\Jobs\RecalculateStudyCycleOrderJob;
use App\Models\StudyCycle;
use App\Models\StudyCycleItem;
use Illuminate\Support\Facades\Queue;

test('an automatic cycle reorders its items by descending priority score', function () {
    $cycle = StudyCycle::factory()->automatic()->create();

    $lowPriority = StudyCycleItem::factory()->for($cycle)->create([
        'difficulty' => 1,
        'importance' => 1,
        'progress' => 100,
        'last_studied_at' => now(),
    ]);

    $highPriority = StudyCycleItem::factory()->for($cycle)->create([
        'difficulty' => 5,
        'importance' => 5,
        'progress' => 0,
        'last_studied_at' => null,
    ]);

    app(RecalculateStudyCycleOrder::class)->handle($cycle);

    $highPriority->refresh();
    $lowPriority->refresh();

    expect($highPriority->order)->toBe(0);
    expect($lowPriority->order)->toBe(1);
    expect($highPriority->priority_score)->toBeGreaterThan($lowPriority->priority_score);
});

test('a manual cycle is left untouched by the recalculation', function () {
    $cycle = StudyCycle::factory()->manual()->create();

    $item = StudyCycleItem::factory()->for($cycle)->create([
        'order' => 7,
        'priority_score' => 0,
    ]);

    app(RecalculateStudyCycleOrder::class)->handle($cycle);

    $item->refresh();

    expect($item->order)->toBe(7);
    expect($item->priority_score)->toBe(0.0);
});

test('recalculating an automatic cycle can be queued via the update job', function () {
    Queue::fake();

    $cycle = StudyCycle::factory()->automatic()->create();
    StudyCycleItem::factory()->for($cycle)->create();

    RecalculateStudyCycleOrderJob::dispatch($cycle);

    Queue::assertPushed(RecalculateStudyCycleOrderJob::class, fn ($job) => $job->studyCycle->is($cycle));
});

test('the update job executes the recalculation for the cycle it was given', function () {
    $cycle = StudyCycle::factory()->automatic()->create();
    $item = StudyCycleItem::factory()->for($cycle)->create([
        'difficulty' => 5,
        'importance' => 5,
        'progress' => 0,
        'last_studied_at' => null,
    ]);

    (new RecalculateStudyCycleOrderJob($cycle))->handle(app(RecalculateStudyCycleOrder::class));

    $item->refresh();

    expect($item->priority_score)->toBeGreaterThan(0);
});
