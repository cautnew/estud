import {
    Bar,
    BarChart,
    CartesianGrid,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from 'recharts';
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
import {
    AXIS_TICK,
    CHART_COLOR,
    GRID_COLOR,
    TOOLTIP_STYLE,
} from '@/lib/chart-theme';
import type { DashboardStats } from '@/types';

export default function SubjectRankingChart({
    data,
}: {
    data: DashboardStats['subjectRanking'];
}) {
    return (
        <Card>
            <CardHeader>
                <CardTitle>Ranking de disciplinas</CardTitle>
                <CardDescription>
                    Horas estudadas por disciplina
                </CardDescription>
            </CardHeader>
            <CardContent>
                {data.length === 0 ? (
                    <EmptyState>
                        <EmptyStateTitle>Sem dados suficientes</EmptyStateTitle>
                        <EmptyStateDescription>
                            Registre sessões de estudo para ver o ranking.
                        </EmptyStateDescription>
                    </EmptyState>
                ) : (
                    <ResponsiveContainer
                        width="100%"
                        height={Math.max(160, data.length * 36)}
                    >
                        <BarChart data={data} layout="vertical">
                            <CartesianGrid
                                stroke={GRID_COLOR}
                                horizontal={false}
                            />
                            <XAxis
                                type="number"
                                tick={AXIS_TICK}
                                axisLine={{ stroke: GRID_COLOR }}
                                tickLine={false}
                            />
                            <YAxis
                                type="category"
                                dataKey="subject"
                                tick={AXIS_TICK}
                                axisLine={false}
                                tickLine={false}
                                width={110}
                            />
                            <Tooltip
                                contentStyle={TOOLTIP_STYLE}
                                formatter={(value) => [
                                    `${value}h`,
                                    'Horas estudadas',
                                ]}
                            />
                            <Bar
                                dataKey="hours"
                                fill={CHART_COLOR}
                                radius={[0, 4, 4, 0]}
                                maxBarSize={24}
                            />
                        </BarChart>
                    </ResponsiveContainer>
                )}
            </CardContent>
        </Card>
    );
}
