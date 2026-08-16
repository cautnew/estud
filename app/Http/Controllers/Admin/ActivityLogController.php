<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamNotice;
use App\Models\Goal;
use App\Models\QuestionLog;
use App\Models\Review;
use App\Models\Schedule;
use App\Models\StudyCycle;
use App\Models\StudyPlan;
use App\Models\StudySession;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    /**
     * List recorded activity (create/update/delete) across the app's
     * audited models, with filters for event, subject type, causer and
     * date range.
     */
    public function index(Request $request): Response
    {
        $activities = Activity::query()
            ->with('causer:id,name')
            ->when($request->filled('event'), fn ($query) => $query->where('event', $request->string('event')))
            ->when($request->filled('subject_type'), fn ($query) => $query->where('subject_type', $request->string('subject_type')))
            ->when($request->filled('causer_id'), fn ($query) => $query->where('causer_id', $request->integer('causer_id')))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('created_at', '>=', $request->date('date_from')))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('created_at', '<=', $request->date('date_to')))
            ->latest()
            ->paginate(25)
            ->withQueryString()
            ->through(fn (Activity $activity): array => [
                'id' => $activity->id,
                'event' => $activity->event,
                'subject_type' => $activity->subject_type !== null ? class_basename($activity->subject_type) : null,
                'subject_id' => $activity->subject_id,
                'causer' => $activity->causer instanceof User ? [
                    'id' => $activity->causer->id,
                    'name' => $activity->causer->name,
                ] : null,
                'changes' => [
                    'old' => $activity->properties->get('old', []),
                    'attributes' => $activity->properties->get('attributes', []),
                ],
                'created_at' => $activity->created_at?->toISOString(),
            ]);

        return Inertia::render('admin/activity-log/index', [
            'activities' => $activities,
            'filters' => $request->only(['event', 'subject_type', 'causer_id', 'date_from', 'date_to']),
            'subjectTypes' => $this->availableSubjectTypes(),
            'causers' => User::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    private function availableSubjectTypes(): array
    {
        return collect([
            Subject::class => 'Disciplina',
            Topic::class => 'Tópico',
            StudyPlan::class => 'Plano de Estudo',
            Schedule::class => 'Cronograma',
            StudySession::class => 'Sessão de Estudo',
            Review::class => 'Revisão',
            QuestionLog::class => 'Registro de Questões',
            Goal::class => 'Meta',
            ExamNotice::class => 'Edital',
            StudyCycle::class => 'Ciclo de Estudos',
            User::class => 'Usuário',
        ])
            ->map(fn (string $label, string $class): array => ['value' => $class, 'label' => $label])
            ->values()
            ->all();
    }
}
