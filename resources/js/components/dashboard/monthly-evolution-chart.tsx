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
    AXIS_TICK,
    CHART_COLOR,
    GRID_COLOR,
    TOOLTIP_STYLE,
} from '@/lib/chart-theme';

const MONTH_LABELS = [
    'jan',
    'fev',
    'mar',
    'abr',
    'mai',
    'jun',
    'jul',
    'ago',
    'set',
    'out',
    'nov',
    'dez',
];

function formatMonth(month: string): string {
    const [year, monthNumber] = month.split('-');

    return `${MONTH_LABELS[Number(monthNumber) - 1]}/${year.slice(2)}`;
}

export default function MonthlyEvolutionChart({
    data,
}: {
    data: { month: string; hours: number }[];
}) {
    return (
        <Card>
            <CardHeader>
                <CardTitle>Evolução mensal</CardTitle>
                <CardDescription>Horas estudadas por mês</CardDescription>
            </CardHeader>
            <CardContent>
                <ResponsiveContainer width="100%" height={260}>
                    <BarChart data={data}>
                        <CartesianGrid stroke={GRID_COLOR} vertical={false} />
                        <XAxis
                            dataKey="month"
                            tickFormatter={formatMonth}
                            tick={AXIS_TICK}
                            axisLine={{ stroke: GRID_COLOR }}
                            tickLine={false}
                        />
                        <YAxis
                            tick={AXIS_TICK}
                            axisLine={false}
                            tickLine={false}
                            width={32}
                        />
                        <Tooltip
                            contentStyle={TOOLTIP_STYLE}
                            labelFormatter={(value) =>
                                formatMonth(String(value))
                            }
                            formatter={(value) => [
                                `${value}h`,
                                'Horas estudadas',
                            ]}
                        />
                        <Bar
                            dataKey="hours"
                            fill={CHART_COLOR}
                            radius={[4, 4, 0, 0]}
                            maxBarSize={32}
                        />
                    </BarChart>
                </ResponsiveContainer>
            </CardContent>
        </Card>
    );
}
