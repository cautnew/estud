<?php

namespace App\Actions\StudyCycles;

use App\Enums\StudyCycleType;
use App\Models\StudyCycle;
use Illuminate\Support\Facades\DB;

class RecalculateStudyCycleOrder
{
    public function __construct(
        private readonly CalculateStudyCycleItemScore $calculator,
    ) {}

    /**
     * Re-score every item of an automatic cycle and persist the resulting
     * order (highest priority first). No-op for manual cycles, whose order
     * is set by the user and never touched by the algorithm.
     */
    public function handle(StudyCycle $cycle): void
    {
        if ($cycle->type !== StudyCycleType::Automatic) {
            return;
        }

        $scored = $cycle->studyCycleItems()
            ->get()
            ->map(fn ($item) => [
                'item' => $item,
                'score' => $this->calculator->handle(
                    $item->difficulty,
                    $item->importance,
                    $item->progress,
                    $item->last_studied_at === null ? null : (int) $item->last_studied_at->diffInDays(now()),
                ),
            ])
            ->sortByDesc('score')
            ->values();

        DB::transaction(function () use ($scored): void {
            foreach ($scored as $order => $entry) {
                $entry['item']->forceFill([
                    'priority_score' => $entry['score'],
                    'order' => $order,
                ])->save();
            }
        });
    }
}
