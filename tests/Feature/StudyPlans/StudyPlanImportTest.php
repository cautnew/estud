<?php

use App\Models\StudyPlan;
use App\Models\Subject;
use App\Models\User;

test('a user can import a plan from a valid JSON payload', function () {
    $user = User::factory()->create();
    $user->assignRole('User');

    $payload = json_encode([
        'name' => 'Plano importado',
        'exam_board' => 'Concurso X',
        'goal' => 'Passar',
        'deadline' => '2026-12-01',
        'exam_date' => '2026-12-15',
        'subjects' => [
            [
                'name' => 'Direito Administrativo',
                'topics' => ['Atos administrativos', 'Licitações'],
            ],
        ],
    ]);

    $response = $this->actingAs($user)->post(route('study-plans.import.store'), [
        'payload' => $payload,
    ]);

    $response->assertSessionHasNoErrors();

    $plan = StudyPlan::sole();
    expect($plan->owner_id)->toBe($user->id);
    expect($plan->is_public)->toBeFalse();
    expect($plan->name)->toBe('Plano importado');

    $studyPlanSubject = $plan->studyPlanSubjects()->sole();
    expect($studyPlanSubject->subject->name)->toBe('Direito Administrativo');
    expect($studyPlanSubject->studyPlanTopics()->count())->toBe(2);
});

test('importing matches existing catalog subjects and topics case-insensitively', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $subject = Subject::factory()->create(['name' => 'Direito Constitucional']);
    $topic = $subject->topics()->create(['name' => 'Controle de constitucionalidade']);

    $payload = json_encode([
        'name' => 'Plano importado',
        'exam_board' => 'Concurso X',
        'subjects' => [
            [
                'name' => 'direito constitucional',
                'topics' => ['CONTROLE DE CONSTITUCIONALIDADE'],
            ],
        ],
    ]);

    $this->actingAs($user)->post(route('study-plans.import.store'), ['payload' => $payload])
        ->assertSessionHasNoErrors();

    expect(Subject::count())->toBe(1);
    expect($subject->topics()->count())->toBe(1);

    $plan = StudyPlan::sole();
    $studyPlanSubject = $plan->studyPlanSubjects()->sole();
    expect($studyPlanSubject->subject_id)->toBe($subject->id);
    expect($studyPlanSubject->studyPlanTopics()->sole()->topic_id)->toBe($topic->id);
});

test('importing creates owner-scoped subjects and topics when no catalog match exists', function () {
    $user = User::factory()->create();
    $user->assignRole('User');

    $payload = json_encode([
        'name' => 'Plano importado',
        'exam_board' => 'Concurso X',
        'subjects' => [
            ['name' => 'Nova disciplina', 'topics' => ['Novo tópico']],
        ],
    ]);

    $this->actingAs($user)->post(route('study-plans.import.store'), ['payload' => $payload])
        ->assertSessionHasNoErrors();

    $subject = Subject::sole();
    expect($subject->owner_id)->toBe($user->id);
    expect($subject->name)->toBe('Nova disciplina');
});

test('importing a malformed JSON payload fails validation', function () {
    $user = User::factory()->create();
    $user->assignRole('User');

    $response = $this->actingAs($user)->post(route('study-plans.import.store'), [
        'payload' => '{not valid json',
    ]);

    $response->assertSessionHasErrors('data');
    expect(StudyPlan::count())->toBe(0);
});

test('importing a payload missing required fields fails validation', function () {
    $user = User::factory()->create();
    $user->assignRole('User');

    $payload = json_encode(['name' => 'Plano incompleto']);

    $response = $this->actingAs($user)->post(route('study-plans.import.store'), [
        'payload' => $payload,
    ]);

    $response->assertSessionHasErrors(['data.exam_board', 'data.subjects']);
});
