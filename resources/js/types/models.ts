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
