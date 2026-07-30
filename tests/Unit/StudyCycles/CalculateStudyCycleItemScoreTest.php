<?php

use App\Actions\StudyCycles\CalculateStudyCycleItemScore;

test('score is bounded between 0 and 100', function () {
    $calculator = new CalculateStudyCycleItemScore;

    $min = $calculator->handle(difficulty: 1, importance: 1, progress: 100, daysSinceLastStudied: 0);
    $max = $calculator->handle(difficulty: 5, importance: 5, progress: 0, daysSinceLastStudied: null);

    expect($min)->toBeGreaterThanOrEqual(0)->toBeLessThanOrEqual(100);
    expect($max)->toBeGreaterThanOrEqual(0)->toBeLessThanOrEqual(100)->toBe(100.0);
});

test('higher difficulty and importance increase the score', function () {
    $calculator = new CalculateStudyCycleItemScore;

    $easy = $calculator->handle(difficulty: 1, importance: 1, progress: 50, daysSinceLastStudied: 5);
    $hard = $calculator->handle(difficulty: 5, importance: 5, progress: 50, daysSinceLastStudied: 5);

    expect($hard)->toBeGreaterThan($easy);
});

test('lower progress increases the score', function () {
    $calculator = new CalculateStudyCycleItemScore;

    $mastered = $calculator->handle(difficulty: 3, importance: 3, progress: 100, daysSinceLastStudied: 5);
    $untouched = $calculator->handle(difficulty: 3, importance: 3, progress: 0, daysSinceLastStudied: 5);

    expect($untouched)->toBeGreaterThan($mastered);
});

test('a longer delay since the last review increases the score', function () {
    $calculator = new CalculateStudyCycleItemScore;

    $recent = $calculator->handle(difficulty: 3, importance: 3, progress: 50, daysSinceLastStudied: 1);
    $overdue = $calculator->handle(difficulty: 3, importance: 3, progress: 50, daysSinceLastStudied: 20);

    expect($overdue)->toBeGreaterThan($recent);
});

test('an item that was never studied is treated as maximally overdue', function () {
    $calculator = new CalculateStudyCycleItemScore;

    $neverStudied = $calculator->handle(difficulty: 3, importance: 3, progress: 50, daysSinceLastStudied: null);
    $veryOverdue = $calculator->handle(difficulty: 3, importance: 3, progress: 50, daysSinceLastStudied: 30);

    expect($neverStudied)->toBe($veryOverdue);
});

test('delay beyond the cap does not further increase the score', function () {
    $calculator = new CalculateStudyCycleItemScore;

    $atCap = $calculator->handle(difficulty: 3, importance: 3, progress: 50, daysSinceLastStudied: 30);
    $wellPastCap = $calculator->handle(difficulty: 3, importance: 3, progress: 50, daysSinceLastStudied: 365);

    expect($wellPastCap)->toBe($atCap);
});

test('the same inputs produce the same score deterministically', function () {
    $calculator = new CalculateStudyCycleItemScore;

    $score = fn () => $calculator->handle(difficulty: 4, importance: 2, progress: 30, daysSinceLastStudied: 5);

    expect($score())->toBe($score());
});
