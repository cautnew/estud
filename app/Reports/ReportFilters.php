<?php

namespace App\Reports;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;

/**
 * The advanced filters shared by every report: a date range (applied to
 * whichever "when did this happen" column the report's data source has)
 * and optional subject/study-plan scoping. Not every report honors every
 * filter — a report ignores a filter that doesn't apply to its data model
 * (e.g. `studyPlanId` has no meaning for the Goals report, since goals
 * aren't tied to a study plan). Each report action documents which
 * filters it applies.
 */
final class ReportFilters
{
    public function __construct(
        public readonly CarbonInterface $from,
        public readonly CarbonInterface $to,
        public readonly ?int $subjectId = null,
        public readonly ?int $studyPlanId = null,
    ) {}

    /**
     * Defaults to the last 30 days when no range is given.
     */
    public static function fromRequest(Request $request): self
    {
        return new self(
            from: $request->filled('date_from')
                ? CarbonImmutable::parse($request->string('date_from')->toString())->startOfDay()
                : today()->subDays(29),
            to: $request->filled('date_to')
                ? CarbonImmutable::parse($request->string('date_to')->toString())->startOfDay()
                : today(),
            subjectId: $request->integer('subject_id') ?: null,
            studyPlanId: $request->integer('study_plan_id') ?: null,
        );
    }
}
