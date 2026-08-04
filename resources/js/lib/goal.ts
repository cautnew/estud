import {
    BarChart3,
    BookOpenCheck,
    CheckCircle2,
    Clock,
    RotateCcw,
} from 'lucide-react';
import type { GoalPeriod, GoalType } from '@/types';

export const GOAL_TYPE_LABELS: Record<GoalType, string> = {
    hours: 'Horas de estudo',
    subjects: 'Disciplinas concluídas',
    topics: 'Tópicos concluídos',
    reviews: 'Revisões concluídas',
    questions: 'Questões resolvidas',
};

export const GOAL_TYPE_ICONS: Record<GoalType, typeof Clock> = {
    hours: Clock,
    subjects: BookOpenCheck,
    topics: CheckCircle2,
    reviews: RotateCcw,
    questions: BarChart3,
};

export const GOAL_TYPE_UNITS: Record<GoalType, string> = {
    hours: 'h',
    subjects: '',
    topics: '',
    reviews: '',
    questions: '',
};

export const GOAL_PERIOD_LABELS: Record<GoalPeriod, string> = {
    daily: 'Diária',
    weekly: 'Semanal',
    monthly: 'Mensal',
};

export function formatGoalValue(type: GoalType, value: number): string {
    const unit = GOAL_TYPE_UNITS[type];

    return type === 'hours' ? `${value}${unit}` : `${Math.round(value)}${unit}`;
}
