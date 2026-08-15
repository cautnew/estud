<?php

namespace App\Http\Controllers;

use App\Actions\Reports\CalculateEvolutionReport;
use App\Actions\Reports\CalculateGoalsReport;
use App\Actions\Reports\CalculatePerformanceReport;
use App\Actions\Reports\CalculateProductivityReport;
use App\Actions\Reports\CalculateReviewsReport;
use App\Enums\ReportType;
use App\Exports\ReportExport;
use App\Models\StudyPlan;
use App\Models\Subject;
use App\Models\User;
use App\Reports\ReportFilters;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    /**
     * Render the reports page: the currently selected report type's data
     * (for on-screen preview), plus everything the filter form needs.
     */
    public function index(Request $request): InertiaResponse
    {
        $user = $request->user();
        $type = $this->resolveType($request);
        $filters = ReportFilters::fromRequest($request);

        return Inertia::render('reports/index', [
            'report' => $this->calculate($type, $user, $filters),
            'reportTypes' => collect(ReportType::cases())
                ->map(fn (ReportType $reportType): array => ['value' => $reportType->value, 'label' => $reportType->label()])
                ->all(),
            'selectedType' => $type->value,
            'filters' => [
                'date_from' => $filters->from->toDateString(),
                'date_to' => $filters->to->toDateString(),
                'subject_id' => $filters->subjectId,
                'study_plan_id' => $filters->studyPlanId,
            ],
            'availableSubjects' => Subject::query()->visibleTo($user)->orderBy('name')->get(['id', 'name']),
            'availableStudyPlans' => StudyPlan::query()->ownedBy($user)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Stream the currently filtered report as a PDF download.
     */
    public function exportPdf(Request $request): Response
    {
        $report = $this->calculate($this->resolveType($request), $request->user(), ReportFilters::fromRequest($request));

        return Pdf::loadView('reports.report', ['report' => $report])
            ->download($this->fileName($this->resolveType($request), 'pdf'));
    }

    /**
     * Stream the currently filtered report as an Excel (.xlsx) download.
     */
    public function exportExcel(Request $request): BinaryFileResponse
    {
        $type = $this->resolveType($request);
        $report = $this->calculate($type, $request->user(), ReportFilters::fromRequest($request));

        return Excel::download(new ReportExport($report), $this->fileName($type, 'xlsx'));
    }

    private function resolveType(Request $request): ReportType
    {
        return ReportType::tryFrom((string) $request->query('type')) ?? ReportType::Productivity;
    }

    /**
     * @return array{title: string, filters_description: string, summary: array<int, array{label: string, value: string}>, columns: array<int, string>, rows: array<int, array<int, string>>}
     */
    private function calculate(ReportType $type, User $user, ReportFilters $filters): array
    {
        return match ($type) {
            ReportType::Productivity => app(CalculateProductivityReport::class)->handle($user, $filters),
            ReportType::Evolution => app(CalculateEvolutionReport::class)->handle($user, $filters),
            ReportType::Reviews => app(CalculateReviewsReport::class)->handle($user, $filters),
            ReportType::Performance => app(CalculatePerformanceReport::class)->handle($user, $filters),
            ReportType::Goals => app(CalculateGoalsReport::class)->handle($user, $filters),
        };
    }

    private function fileName(ReportType $type, string $extension): string
    {
        return sprintf('relatorio-%s-%s.%s', $type->value, today()->toDateString(), $extension);
    }
}
