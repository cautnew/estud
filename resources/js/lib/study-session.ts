import type { StudySession, StudySessionStatus } from '@/types';

export const STATUS_LABELS: Record<StudySessionStatus, string> = {
    in_progress: 'Em andamento',
    paused: 'Pausada',
    completed: 'Concluída',
};

export const STATUS_VARIANTS: Record<
    StudySessionStatus,
    'default' | 'secondary' | 'outline'
> = {
    in_progress: 'default',
    paused: 'secondary',
    completed: 'outline',
};

export function formatDuration(totalSeconds: number): string {
    const hours = Math.floor(totalSeconds / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);
    const seconds = Math.floor(totalSeconds % 60);

    return [hours, minutes, seconds]
        .map((value) => String(value).padStart(2, '0'))
        .join(':');
}

export function formatDate(date: string): string {
    const [year, month, day] = date.slice(0, 10).split('-');

    return `${day}/${month}/${year}`;
}

export function elapsedSeconds(session: StudySession): number {
    if (session.status === 'in_progress' && session.started_at) {
        const startedAt = new Date(session.started_at).getTime();

        return (
            session.duration_seconds +
            Math.max(0, Math.floor((Date.now() - startedAt) / 1000))
        );
    }

    return session.duration_seconds;
}
