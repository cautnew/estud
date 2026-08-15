import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Progress } from '@/components/ui/progress';
import type { HealthScore } from '@/types';

const COMPONENT_LABELS: Record<keyof HealthScore['components'], string> = {
    consistency: 'Constância',
    goals: 'Metas',
    reviews: 'Revisões',
    schedule: 'Cronograma',
};

function scoreColor(score: number): string {
    if (score >= 80) {
        return 'text-emerald-600 dark:text-emerald-400';
    }

    if (score >= 60) {
        return 'text-lime-600 dark:text-lime-400';
    }

    if (score >= 40) {
        return 'text-amber-600 dark:text-amber-400';
    }

    if (score >= 20) {
        return 'text-orange-600 dark:text-orange-400';
    }

    return 'text-red-600 dark:text-red-500';
}

export default function HealthScoreCard({
    healthScore,
}: {
    healthScore: HealthScore;
}) {
    return (
        <Card>
            <CardHeader>
                <CardTitle>Health Score</CardTitle>
            </CardHeader>
            <CardContent className="space-y-6">
                <div className="flex items-baseline gap-3">
                    <span
                        className={`text-4xl font-bold ${scoreColor(healthScore.score)}`}
                    >
                        {healthScore.score}
                    </span>
                    <span className="text-sm text-muted-foreground">
                        / 100 · {healthScore.label}
                    </span>
                </div>

                <div className="space-y-3">
                    {(
                        Object.entries(healthScore.components) as [
                            keyof HealthScore['components'],
                            HealthScore['components'][keyof HealthScore['components']],
                        ][]
                    ).map(([key, component]) => (
                        <div key={key}>
                            <div className="mb-1 flex items-center justify-between text-sm">
                                <span>{COMPONENT_LABELS[key]}</span>
                                <span className="text-muted-foreground">
                                    {component.score}/{component.weight} pts
                                </span>
                            </div>
                            <Progress
                                value={
                                    (component.score / component.weight) * 100
                                }
                            />
                        </div>
                    ))}
                </div>
            </CardContent>
        </Card>
    );
}
