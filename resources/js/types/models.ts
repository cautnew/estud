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
