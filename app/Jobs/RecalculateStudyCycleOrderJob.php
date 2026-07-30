<?php

namespace App\Jobs;

use App\Actions\StudyCycles\RecalculateStudyCycleOrder;
use App\Models\StudyCycle;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RecalculateStudyCycleOrderJob implements ShouldQueue
{
    use Queueable;

    /**
     * Queue a re-scoring of an automatic cycle's items, e.g. after an
     * item's progress or last_studied_at changes, so the drop in urgency
     * doesn't block the request that triggered it.
     */
    public function __construct(
        public readonly StudyCycle $studyCycle,
    ) {}

    public function handle(RecalculateStudyCycleOrder $action): void
    {
        $action->handle($this->studyCycle);
    }
}
