import { Head, Link } from '@inertiajs/react';
import { CheckCircle2, Clock, Flame, Plus } from 'lucide-react';
import StudySessionStopwatchCard from '@/components/study-session-stopwatch-card';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    EmptyState,
    EmptyStateDescription,
    EmptyStateTitle,
} from '@/components/ui/empty-state';
import { Progress } from '@/components/ui/progress';
import { formatDuration } from '@/lib/study-session';
import { dashboard } from '@/routes';
import { index as studySessionsIndex } from '@/routes/study-sessions';
import type { StudySession } from '@/types';

type DashboardSummary = {
    todaySeconds: number;
    weekSeconds: number;
    completedThisWeek: number;
    bySubject: { subject: string; seconds: number }[];
};

export default function Dashboard({
    summary,
    activeSession,
}: {
    summary: DashboardSummary;
    activeSession: StudySession | null;
}) {
    const topSubjectSeconds = summary.bySubject[0]?.seconds ?? 0;

    return (
        <>
            <Head title="Dashboard" />
            <div className="flex flex-1 flex-col gap-6 p-4">
                {activeSession ? (
                    <StudySessionStopwatchCard session={activeSession} />
                ) : (
                    <Card>
                        <CardContent className="flex items-center justify-between py-4">
                            <div>
                                <p className="font-medium">
                                    Nenhuma sessão em andamento
                                </p>
                                <p className="text-sm text-muted-foreground">
                                    Inicie o cronômetro para registrar seu tempo
                                    de estudo.
                                </p>
                            </div>
                            <Button asChild>
                                <Link href={studySessionsIndex()}>
                                    <Plus className="size-4" />
                                    Iniciar sessão
                                </Link>
                            </Button>
                        </CardContent>
                    </Card>
                )}

                <div className="grid gap-4 md:grid-cols-3">
                    <Card>
                        <CardHeader>
                            <CardDescription className="flex items-center gap-1.5">
                                <Clock className="size-4" />
                                Hoje
                            </CardDescription>
                            <CardTitle className="font-mono text-2xl tabular-nums">
                                {formatDuration(summary.todaySeconds)}
                            </CardTitle>
                        </CardHeader>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardDescription className="flex items-center gap-1.5">
                                <Flame className="size-4" />
                                Últimos 7 dias
                            </CardDescription>
                            <CardTitle className="font-mono text-2xl tabular-nums">
                                {formatDuration(summary.weekSeconds)}
                            </CardTitle>
                        </CardHeader>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardDescription className="flex items-center gap-1.5">
                                <CheckCircle2 className="size-4" />
                                Sessões concluídas
                            </CardDescription>
                            <CardTitle className="text-2xl">
                                {summary.completedThisWeek}
                            </CardTitle>
                        </CardHeader>
                    </Card>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Tempo por disciplina</CardTitle>
                        <CardDescription>Últimos 7 dias</CardDescription>
                    </CardHeader>
                    <CardContent>
                        {summary.bySubject.length === 0 ? (
                            <EmptyState>
                                <EmptyStateTitle>
                                    Nenhuma sessão registrada
                                </EmptyStateTitle>
                                <EmptyStateDescription>
                                    Suas sessões de estudo da semana aparecerão
                                    aqui.
                                </EmptyStateDescription>
                            </EmptyState>
                        ) : (
                            <div className="space-y-4">
                                {summary.bySubject.map((entry) => (
                                    <div
                                        key={entry.subject}
                                        className="space-y-1.5"
                                    >
                                        <div className="flex items-center justify-between text-sm">
                                            <span className="font-medium">
                                                {entry.subject}
                                            </span>
                                            <span className="font-mono text-muted-foreground tabular-nums">
                                                {formatDuration(entry.seconds)}
                                            </span>
                                        </div>
                                        <Progress
                                            value={
                                                topSubjectSeconds > 0
                                                    ? (entry.seconds /
                                                          topSubjectSeconds) *
                                                      100
                                                    : 0
                                            }
                                        />
                                    </div>
                                ))}
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
    ],
};
