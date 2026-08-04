import { Head, Link } from '@inertiajs/react';
import {
    BookOpenCheck,
    CheckCircle2,
    Clock,
    Plus,
    RotateCcw,
    Target,
    TrendingDown,
    TrendingUp,
} from 'lucide-react';
import MonthlyEvolutionChart from '@/components/dashboard/monthly-evolution-chart';
import PeriodComparisonChart from '@/components/dashboard/period-comparison-chart';
import StudyHeatmap from '@/components/dashboard/study-heatmap';
import SubjectRadarChart from '@/components/dashboard/subject-radar-chart';
import SubjectRankingChart from '@/components/dashboard/subject-ranking-chart';
import StudySessionStopwatchCard from '@/components/study-session-stopwatch-card';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { dashboard } from '@/routes';
import { index as studySessionsIndex } from '@/routes/study-sessions';
import type { DashboardStats, StudySession } from '@/types';

function DeltaBadge({ deltaPercent }: { deltaPercent: number }) {
    if (deltaPercent === 0) {
        return null;
    }

    const isPositive = deltaPercent > 0;

    return (
        <span
            className={
                isPositive
                    ? 'flex items-center gap-0.5 text-sm font-medium text-emerald-600 dark:text-emerald-400'
                    : 'flex items-center gap-0.5 text-sm font-medium text-red-600 dark:text-red-500'
            }
        >
            {isPositive ? (
                <TrendingUp className="size-3.5" />
            ) : (
                <TrendingDown className="size-3.5" />
            )}
            {Math.abs(deltaPercent)}%
        </span>
    );
}

function IndicatorTiles({
    indicators,
}: {
    indicators: DashboardStats['indicators'];
}) {
    return (
        <div className="grid gap-4 md:grid-cols-3 xl:grid-cols-5">
            <Card>
                <CardHeader>
                    <CardDescription className="flex items-center gap-1.5">
                        <Clock className="size-4" />
                        Horas estudadas (mês)
                    </CardDescription>
                    <div className="flex items-baseline gap-2">
                        <CardTitle className="text-2xl">
                            {indicators.hoursStudied.hours}h
                        </CardTitle>
                        <DeltaBadge
                            deltaPercent={indicators.hoursStudied.deltaPercent}
                        />
                    </div>
                </CardHeader>
            </Card>

            <Card>
                <CardHeader>
                    <CardDescription className="flex items-center gap-1.5">
                        <BookOpenCheck className="size-4" />
                        Disciplinas concluídas
                    </CardDescription>
                    <CardTitle className="text-2xl">
                        {indicators.subjectsCompleted}
                    </CardTitle>
                </CardHeader>
            </Card>

            <Card>
                <CardHeader>
                    <CardDescription className="flex items-center gap-1.5">
                        <CheckCircle2 className="size-4" />
                        Tópicos concluídos
                    </CardDescription>
                    <CardTitle className="text-2xl">
                        {indicators.topicsCompleted}
                    </CardTitle>
                </CardHeader>
            </Card>

            <Card>
                <CardHeader>
                    <CardDescription className="flex items-center gap-1.5">
                        <RotateCcw className="size-4" />
                        Revisões
                    </CardDescription>
                    <CardTitle className="text-2xl">
                        {indicators.reviews.completed}
                    </CardTitle>
                    <p className="text-xs text-muted-foreground">
                        {indicators.reviews.pending} pendentes ·{' '}
                        {indicators.reviews.overdue} atrasadas
                    </p>
                </CardHeader>
            </Card>

            <Card>
                <CardHeader>
                    <CardDescription className="flex items-center gap-1.5">
                        <Target className="size-4" />
                        Metas no prazo
                    </CardDescription>
                    <CardTitle className="text-2xl">
                        {indicators.goals.onTrack}/{indicators.goals.total}
                    </CardTitle>
                </CardHeader>
            </Card>
        </div>
    );
}

export default function Dashboard({
    stats,
    activeSession,
}: {
    stats: DashboardStats;
    activeSession: StudySession | null;
}) {
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

                <IndicatorTiles indicators={stats.indicators} />

                <StudyHeatmap data={stats.heatmap} />

                <div className="grid gap-4 lg:grid-cols-2">
                    <MonthlyEvolutionChart data={stats.monthlyEvolution} />
                    <SubjectRankingChart data={stats.subjectRanking} />
                </div>

                <div className="grid gap-4 lg:grid-cols-2">
                    <SubjectRadarChart data={stats.radarBySubject} />
                    <PeriodComparisonChart
                        comparison={stats.periodComparison}
                    />
                </div>
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
