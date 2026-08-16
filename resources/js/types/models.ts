export type Owner = {
    id: number;
    name: string;
};

export type Topic = {
    id: number;
    subject_id: number;
    name: string;
    description: string | null;
    created_at: string;
    updated_at: string;
};

export type Subject = {
    id: number;
    owner_id: number | null;
    name: string;
    description: string | null;
    topics_count?: number;
    topics?: Topic[];
    owner?: Owner | null;
    created_at: string;
    updated_at: string;
};

export type StudyPlanTopicStatus = 'pending' | 'in_progress' | 'done';

export type StudyPlanTopic = {
    id: number;
    study_plan_subject_id: number;
    topic_id: number;
    order: number;
    status: StudyPlanTopicStatus;
    studied_at: string | null;
    notes: string | null;
    topic?: Topic;
    created_at: string;
    updated_at: string;
};

export type StudyPlanSubject = {
    id: number;
    study_plan_id: number;
    subject_id: number;
    order: number;
    subject?: Subject;
    study_plan_topics?: StudyPlanTopic[];
    created_at: string;
    updated_at: string;
};

export type StudyPlan = {
    id: number;
    owner_id: number;
    name: string;
    exam_board: string;
    goal: string | null;
    deadline: string | null;
    exam_date: string | null;
    is_public: boolean;
    study_plan_subjects_count?: number;
    study_plan_subjects?: StudyPlanSubject[];
    owner?: Owner;
    created_at: string;
    updated_at: string;
};

export type ScheduleType =
    'daily' | 'weekly' | 'biweekly' | 'monthly' | 'quarterly';

export type ScheduleItem = {
    id: number;
    schedule_id: number;
    subject_id: number;
    day_of_week: number | null;
    day_of_month: number | null;
    start_time: string;
    duration_minutes: number;
    priority: number;
    subject?: Subject;
    created_at: string;
    updated_at: string;
};

export type Schedule = {
    id: number;
    owner_id: number;
    name: string;
    type: ScheduleType;
    schedule_items_count?: number;
    schedule_items?: ScheduleItem[];
    owner?: Owner;
    created_at: string;
    updated_at: string;
};

export type StudySessionStatus = 'in_progress' | 'paused' | 'completed';

export type StudySession = {
    id: number;
    owner_id: number;
    subject_id: number;
    topic_id: number | null;
    date: string;
    status: StudySessionStatus;
    started_at: string | null;
    duration_seconds: number;
    note: string | null;
    completed_at: string | null;
    subject?: Subject;
    topic?: Topic | null;
    created_at: string;
    updated_at: string;
};

export type ReviewStatus = 'pending' | 'completed' | 'skipped';

export type Review = {
    id: number;
    owner_id: number;
    study_plan_topic_id: number;
    scheduled_for: string;
    interval_days: number;
    status: ReviewStatus;
    completed_at: string | null;
    notified_at: string | null;
    study_plan_topic?: StudyPlanTopic & {
        study_plan_subject?: StudyPlanSubject;
    };
    created_at: string;
    updated_at: string;
};

export type QuestionLog = {
    id: number;
    owner_id: number;
    subject_id: number;
    topic_id: number | null;
    date: string;
    total: number;
    correct: number;
    incorrect: number;
    subject?: Subject;
    topic?: Topic | null;
    created_at: string;
    updated_at: string;
};

export type QuestionStats = {
    overall: {
        total: number;
        correct: number;
        incorrect: number;
        accuracy_rate: number;
    };
    bySubject: {
        subject_id: number;
        subject: string;
        total: number;
        correct: number;
        accuracy_rate: number;
    }[];
    history: {
        date: string;
        total: number;
        correct: number;
        accuracy_rate: number;
    }[];
};

export type DashboardStats = {
    indicators: {
        hoursStudied: { hours: number; deltaPercent: number };
        subjectsCompleted: number;
        topicsCompleted: number;
        reviews: { completed: number; pending: number; overdue: number };
        goals: { onTrack: number; total: number };
    };
    heatmap: { date: string; hours: number }[];
    monthlyEvolution: { month: string; hours: number }[];
    periodComparison: {
        current: { hours: number; questions: number; topics: number };
        previous: { hours: number; questions: number; topics: number };
    };
    radarBySubject: {
        subject_id: number;
        subject: string;
        total: number;
        correct: number;
        accuracy_rate: number;
    }[];
    subjectRanking: { subject_id: number; subject: string; hours: number }[];
};

export type GoalType =
    'hours' | 'subjects' | 'topics' | 'reviews' | 'questions';

export type GoalPeriod = 'daily' | 'weekly' | 'monthly';

export type GoalProgress = {
    id: number;
    goal_id: number;
    period_start: string;
    period_end: string;
    current_value: number;
    achieved: boolean;
    created_at: string;
    updated_at: string;
};

export type Goal = {
    id: number;
    owner_id: number;
    subject_id: number | null;
    type: GoalType;
    period: GoalPeriod;
    target: number;
    subject?: Subject | null;
    goal_progress?: GoalProgress[];
    created_at: string;
    updated_at: string;
};

export type ExamTopic = {
    id: number;
    exam_subject_id: number;
    topic_id: number;
    order: number;
    topic?: Topic;
    created_at: string;
    updated_at: string;
};

export type ExamSubject = {
    id: number;
    exam_notice_id: number;
    subject_id: number;
    order: number;
    subject?: Subject;
    exam_topics?: ExamTopic[];
    created_at: string;
    updated_at: string;
};

export type ExamNotice = {
    id: number;
    owner_id: number;
    name: string;
    exam_board: string;
    exam_date: string | null;
    exam_subjects_count?: number;
    exam_subjects?: ExamSubject[];
    owner?: Owner;
    created_at: string;
    updated_at: string;
};

export type ExamNoticeProgress = {
    studiedTopics: number;
    totalTopics: number;
    studiedPercentage: number;
    remainingPercentage: number;
};

export type StudyRecommendationSource = 'schedule' | 'cycle' | 'plan';

export type StudyRecommendation = {
    source: StudyRecommendationSource;
    subject_id: number;
    subject: string;
    topic_id: number | null;
    topic: string | null;
    label: string;
};

export type ReviewRecommendation = {
    id: number;
    subject: string;
    topic: string;
    scheduled_for: string;
    interval_days: number;
    days_overdue: number;
};

export type HealthScoreComponent = {
    score: number;
    weight: number;
    details: Record<string, number>;
};

export type HealthScore = {
    score: number;
    label: string;
    components: {
        consistency: HealthScoreComponent;
        goals: HealthScoreComponent;
        reviews: HealthScoreComponent;
        schedule: HealthScoreComponent;
    };
};

export type ReportType =
    'productivity' | 'evolution' | 'reviews' | 'performance' | 'goals';

export type ReportTypeOption = {
    value: ReportType;
    label: string;
};

export type Report = {
    title: string;
    filters_description: string;
    summary: { label: string; value: string }[];
    columns: string[];
    rows: string[][];
};

export type ReportFilters = {
    date_from: string;
    date_to: string;
    subject_id: number | null;
    study_plan_id: number | null;
};

export type ActivityLogEvent = 'created' | 'updated' | 'deleted';

export type ActivityLogEntry = {
    id: number;
    event: ActivityLogEvent;
    subject_type: string | null;
    subject_id: number | null;
    causer: { id: number; name: string } | null;
    changes: {
        old: Record<string, unknown>;
        attributes: Record<string, unknown>;
    };
    created_at: string | null;
};

export type ActivityLogSubjectTypeOption = { value: string; label: string };

export type Paginated<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: { url: string | null; label: string; active: boolean }[];
};
