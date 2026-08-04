<?php

use App\Actions\Dashboard\CalculateDashboardStats;
use App\Actions\QuestionLogs\CalculateQuestionStats;
use App\Enums\StudyPlanTopicStatus;
use App\Models\QuestionLog;
use App\Models\Review;
use App\Models\StudyPlan;
use App\Models\StudyPlanSubject;
use App\Models\StudyPlanTopic;
use App\Models\StudySession;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Carbon;

beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-08-15 12:00:00'));
});

test('hours studied compares this month against last month with a delta percentage', function () {
    $user = User::factory()->create();

    StudySession::factory()->for($user, 'owner')->completed()->create(['date' => '2026-08-01', 'duration_seconds' => 3600]);
    StudySession::factory()->for($user, 'owner')->completed()->create(['date' => '2026-08-10', 'duration_seconds' => 3600]);
    StudySession::factory()->for($user, 'owner')->completed()->create(['date' => '2026-07-15', 'duration_seconds' => 3600]);

    $stats = (new CalculateDashboardStats(new CalculateQuestionStats))->handle($user);

    expect($stats['indicators']['hoursStudied']['hours'])->toBe(2.0);
    expect($stats['indicators']['hoursStudied']['deltaPercent'])->toBe(100.0);
});

test('the heatmap covers the trailing 365 days and reflects the correct hours per day', function () {
    $user = User::factory()->create();
    StudySession::factory()->for($user, 'owner')->completed()->create(['date' => '2026-08-10', 'duration_seconds' => 7200]);

    $stats = (new CalculateDashboardStats(new CalculateQuestionStats))->handle($user);

    expect($stats['heatmap'])->toHaveCount(365);
    expect($stats['heatmap'][0]['date'])->toBe('2025-08-16');
    expect($stats['heatmap'][364]['date'])->toBe('2026-08-15');

    $day = collect($stats['heatmap'])->firstWhere('date', '2026-08-10');
    expect($day['hours'])->toBe(2.0);
});

test('monthly evolution returns the last 6 months in chronological order', function () {
    $user = User::factory()->create();
    StudySession::factory()->for($user, 'owner')->completed()->create(['date' => '2026-08-05', 'duration_seconds' => 3600]);
    StudySession::factory()->for($user, 'owner')->completed()->create(['date' => '2026-03-05', 'duration_seconds' => 7200]);

    $stats = (new CalculateDashboardStats(new CalculateQuestionStats))->handle($user);

    expect($stats['monthlyEvolution'])->toHaveCount(6);
    expect(array_column($stats['monthlyEvolution'], 'month'))->toBe([
        '2026-03', '2026-04', '2026-05', '2026-06', '2026-07', '2026-08',
    ]);
    expect($stats['monthlyEvolution'][0]['hours'])->toBe(2.0);
    expect($stats['monthlyEvolution'][5]['hours'])->toBe(1.0);
});

test('period comparison contrasts the current 7-day window against the previous one', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create();

    StudySession::factory()->for($user, 'owner')->for($subject)->completed()->create(['date' => '2026-08-15', 'duration_seconds' => 3600]);
    StudySession::factory()->for($user, 'owner')->for($subject)->completed()->create(['date' => '2026-08-06', 'duration_seconds' => 3600]);

    QuestionLog::factory()->for($user, 'owner')->for($subject)->create(['date' => '2026-08-15', 'total' => 10, 'correct' => 5, 'incorrect' => 5]);
    QuestionLog::factory()->for($user, 'owner')->for($subject)->create(['date' => '2026-08-06', 'total' => 4, 'correct' => 2, 'incorrect' => 2]);

    $studyPlanTopic = StudyPlanTopic::factory()->create(['status' => StudyPlanTopicStatus::Done, 'studied_at' => '2026-08-15']);
    $studyPlanTopic->studyPlanSubject->studyPlan()->update(['owner_id' => $user->id]);
    $previousTopic = StudyPlanTopic::factory()->create(['status' => StudyPlanTopicStatus::Done, 'studied_at' => '2026-08-06']);
    $previousTopic->studyPlanSubject->studyPlan()->update(['owner_id' => $user->id]);

    $stats = (new CalculateDashboardStats(new CalculateQuestionStats))->handle($user);

    expect($stats['periodComparison'])->toBe([
        'current' => ['hours' => 1.0, 'questions' => 10, 'topics' => 1],
        'previous' => ['hours' => 1.0, 'questions' => 4, 'topics' => 1],
    ]);
});

test('a subject only counts as completed once every one of its topics is done', function () {
    $user = User::factory()->create();
    $plan = StudyPlan::factory()->for($user, 'owner')->create();
    $planSubject = StudyPlanSubject::factory()->for($plan)->create();
    StudyPlanTopic::factory()->for($planSubject)->create(['status' => StudyPlanTopicStatus::Done]);
    StudyPlanTopic::factory()->for($planSubject)->create(['status' => StudyPlanTopicStatus::Done]);

    $incompletePlanSubject = StudyPlanSubject::factory()->for($plan)->create();
    StudyPlanTopic::factory()->for($incompletePlanSubject)->create(['status' => StudyPlanTopicStatus::Done]);
    StudyPlanTopic::factory()->for($incompletePlanSubject)->create(['status' => StudyPlanTopicStatus::Pending]);

    $stats = (new CalculateDashboardStats(new CalculateQuestionStats))->handle($user);

    expect($stats['indicators']['subjectsCompleted'])->toBe(1);
    expect($stats['indicators']['topicsCompleted'])->toBe(3);
});

test('reviews are bucketed into completed, pending, and overdue', function () {
    $user = User::factory()->create();
    Review::factory()->for($user, 'owner')->completed()->create();
    Review::factory()->for($user, 'owner')->create(['scheduled_for' => today()->addDays(3)]);
    Review::factory()->for($user, 'owner')->create(['scheduled_for' => today()->subDays(3)]);

    $stats = (new CalculateDashboardStats(new CalculateQuestionStats))->handle($user);

    expect($stats['indicators']['reviews'])->toBe(['completed' => 1, 'pending' => 1, 'overdue' => 1]);
});

test('a plan is on track when completion keeps pace with elapsed time toward its deadline', function () {
    $user = User::factory()->create();

    // Created 10 days ago, deadline in 10 days (halfway through): all topics done, well ahead of the 50% expected pace.
    $onTrackPlan = StudyPlan::factory()->for($user, 'owner')->create(['deadline' => today()->addDays(10)]);
    $onTrackPlan->forceFill(['created_at' => today()->subDays(10)])->save();
    $onTrackSubject = StudyPlanSubject::factory()->for($onTrackPlan)->create();
    StudyPlanTopic::factory()->for($onTrackSubject)->create(['status' => StudyPlanTopicStatus::Done]);

    // Same timeline, but nothing has been studied yet: far behind pace.
    $behindPlan = StudyPlan::factory()->for($user, 'owner')->create(['deadline' => today()->addDays(10)]);
    $behindPlan->forceFill(['created_at' => today()->subDays(10)])->save();
    $behindSubject = StudyPlanSubject::factory()->for($behindPlan)->create();
    StudyPlanTopic::factory()->for($behindSubject)->create(['status' => StudyPlanTopicStatus::Pending]);

    $stats = (new CalculateDashboardStats(new CalculateQuestionStats))->handle($user);

    expect($stats['indicators']['goals'])->toBe(['onTrack' => 1, 'total' => 2]);
});

test('subject ranking orders subjects by total hours studied, descending', function () {
    $user = User::factory()->create();
    $constitucional = Subject::factory()->create(['name' => 'Constitucional']);
    $portugues = Subject::factory()->create(['name' => 'Português']);

    StudySession::factory()->for($user, 'owner')->for($portugues)->completed()->create(['duration_seconds' => 3600]);
    StudySession::factory()->for($user, 'owner')->for($constitucional)->completed()->create(['duration_seconds' => 7200]);

    $stats = (new CalculateDashboardStats(new CalculateQuestionStats))->handle($user);

    expect($stats['subjectRanking'])->toBe([
        ['subject_id' => $constitucional->id, 'subject' => 'Constitucional', 'hours' => 2.0],
        ['subject_id' => $portugues->id, 'subject' => 'Português', 'hours' => 1.0],
    ]);
});

test('data belonging to other users never leaks into any indicator or dataset', function () {
    $user = User::factory()->create();
    StudySession::factory()->completed()->create(['duration_seconds' => 3600]);
    QuestionLog::factory()->create();
    Review::factory()->create();

    $stats = (new CalculateDashboardStats(new CalculateQuestionStats))->handle($user);

    expect($stats['indicators']['hoursStudied']['hours'])->toBe(0.0);
    expect($stats['indicators']['reviews'])->toBe(['completed' => 0, 'pending' => 0, 'overdue' => 0]);
    expect($stats['subjectRanking'])->toBe([]);
    expect($stats['radarBySubject'])->toBe([]);
});
