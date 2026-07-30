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
