import {
    Bar,
    BarChart,
    Cell,
    ResponsiveContainer,
    Tooltip,
    XAxis,
} from 'recharts';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { CHART_COLOR, TOOLTIP_STYLE } from '@/lib/chart-theme';
import type { DashboardStats } from '@/types';

const PREVIOUS_OPACITY = 0.35;

function Metric({
    label,
    unit,
    previous,
    current,
}: {
    label: string;
    unit: string;
    previous: number;
    current: number;
}) {
    const data = [
        { period: 'Semana passada', value: previous },
        { period: 'Esta semana', value: current },
    ];

    return (
        <div>
            <p className="mb-1 text-sm font-medium text-muted-foreground">
                {label}
            </p>
            <ResponsiveContainer width="100%" height={140}>
                <BarChart data={data}>
                    <XAxis
                        dataKey="period"
                        tick={{ fill: 'var(--muted-foreground)', fontSize: 11 }}
                        axisLine={false}
                        tickLine={false}
                    />
                    <Tooltip
                        contentStyle={TOOLTIP_STYLE}
                        formatter={(value) => [`${value}${unit}`, label]}
                    />
                    <Bar dataKey="value" radius={[4, 4, 0, 0]} maxBarSize={40}>
                        {data.map((entry, index) => (
                            <Cell
                                key={entry.period}
                                fill={CHART_COLOR}
                                fillOpacity={index === 0 ? PREVIOUS_OPACITY : 1}
                            />
                        ))}
                    </Bar>
                </BarChart>
            </ResponsiveContainer>
        </div>
    );
}

export default function PeriodComparisonChart({
    comparison,
}: {
    comparison: DashboardStats['periodComparison'];
}) {
    return (
        <Card>
            <CardHeader>
                <CardTitle>Comparação entre períodos</CardTitle>
                <CardDescription>
                    Esta semana vs. semana passada
                </CardDescription>
            </CardHeader>
            <CardContent className="grid gap-4 sm:grid-cols-3">
                <Metric
                    label="Horas estudadas"
                    unit="h"
                    previous={comparison.previous.hours}
                    current={comparison.current.hours}
                />
                <Metric
                    label="Questões resolvidas"
                    unit=""
                    previous={comparison.previous.questions}
                    current={comparison.current.questions}
                />
                <Metric
                    label="Tópicos concluídos"
                    unit=""
                    previous={comparison.previous.topics}
                    current={comparison.current.topics}
                />
            </CardContent>
        </Card>
    );
}
