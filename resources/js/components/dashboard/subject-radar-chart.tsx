import {
    PolarAngleAxis,
    PolarGrid,
    PolarRadiusAxis,
    Radar,
    RadarChart,
    ResponsiveContainer,
    Tooltip,
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
import { CHART_COLOR, GRID_COLOR, TOOLTIP_STYLE } from '@/lib/chart-theme';
import type { DashboardStats } from '@/types';

export default function SubjectRadarChart({
    data,
}: {
    data: DashboardStats['radarBySubject'];
}) {
    return (
        <Card>
            <CardHeader>
                <CardTitle>Radar por disciplina</CardTitle>
                <CardDescription>
                    Taxa de acerto (%) por disciplina
                </CardDescription>
            </CardHeader>
            <CardContent>
                {data.length === 0 ? (
                    <EmptyState>
                        <EmptyStateTitle>Sem dados suficientes</EmptyStateTitle>
                        <EmptyStateDescription>
                            Registre questões por disciplina para ver o radar.
                        </EmptyStateDescription>
                    </EmptyState>
                ) : (
                    <ResponsiveContainer width="100%" height={280}>
                        <RadarChart data={data}>
                            <PolarGrid stroke={GRID_COLOR} />
                            <PolarAngleAxis
                                dataKey="subject"
                                tick={{
                                    fill: 'var(--muted-foreground)',
                                    fontSize: 12,
                                }}
                            />
                            <PolarRadiusAxis
                                domain={[0, 100]}
                                tick={{
                                    fill: 'var(--muted-foreground)',
                                    fontSize: 10,
                                }}
                                axisLine={false}
                            />
                            <Tooltip
                                contentStyle={TOOLTIP_STYLE}
                                formatter={(value) => [
                                    `${value}%`,
                                    'Taxa de acerto',
                                ]}
                            />
                            <Radar
                                dataKey="accuracy_rate"
                                stroke={CHART_COLOR}
                                fill={CHART_COLOR}
                                fillOpacity={0.25}
                                strokeWidth={2}
                            />
                        </RadarChart>
                    </ResponsiveContainer>
                )}
            </CardContent>
        </Card>
    );
}
