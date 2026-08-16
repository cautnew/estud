<?php

namespace App\Jobs;

use App\Actions\StudyCycles\RecalculateStudyCycleOrder;
use App\Models\StudyCycle;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RecalculateStudyCycleOrderJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 30;

    /**
     * Queue a re-scoring of an automatic cycle's items, e.g. after an
     * item's progress or last_studied_at changes, so the drop in urgency
     * doesn't block the request that triggered it.
     */
    public function __construct(
        public readonly StudyCycle $studyCycle,
    ) {}

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [10, 30, 60];
    }

    public function handle(RecalculateStudyCycleOrder $action): void
    {
        $action->handle($this->studyCycle);
    }
}
