import { Pencil, Trash2 } from 'lucide-react';
import { Bar, BarChart, Cell, ResponsiveContainer, Tooltip } from 'recharts';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Progress } from '@/components/ui/progress';
import { CHART_COLOR, TOOLTIP_STYLE } from '@/lib/chart-theme';
import { formatDate } from '@/lib/date';
import {
    GOAL_PERIOD_LABELS,
    GOAL_TYPE_ICONS,
    GOAL_TYPE_LABELS,
    formatGoalValue,
} from '@/lib/goal';
import type { Goal } from '@/types';

const NOT_ACHIEVED_OPACITY = 0.3;

export default function GoalCard({
    goal,
    onEdit,
    onDelete,
}: {
    goal: Goal;
    onEdit: () => void;
    onDelete: () => void;
}) {
    const history = goal.goal_progress ?? [];
    const current = history.at(-1) ?? null;
    const percentage = current
        ? Math.min(100, Math.round((current.current_value / goal.target) * 100))
        : 0;
    const Icon = GOAL_TYPE_ICONS[goal.type];

    return (
        <Card>
            <CardHeader>
                <div className="flex items-start justify-between gap-2">
                    <CardTitle className="flex items-center gap-1.5 text-base">
                        <Icon className="size-4" />
                        {GOAL_TYPE_LABELS[goal.type]}
                    </CardTitle>
                    <div className="flex gap-1">
                        <Button
                            variant="ghost"
                            size="icon"
                            title="Editar"
                            onClick={onEdit}
                        >
                            <Pencil className="size-4" />
                        </Button>
                        <Button
                            variant="ghost"
                            size="icon"
                            title="Excluir"
                            onClick={onDelete}
                        >
                            <Trash2 className="size-4" />
                        </Button>
                    </div>
                </div>
                <div className="flex flex-wrap gap-1.5">
                    <Badge variant="outline">
                        {GOAL_PERIOD_LABELS[goal.period]}
                    </Badge>
                    {goal.subject && (
                        <Badge variant="outline">{goal.subject.name}</Badge>
                    )}
                    {current?.achieved && <Badge>Atingida</Badge>}
                </div>
            </CardHeader>
            <CardContent className="space-y-4">
                <div>
                    <div className="mb-1.5 flex items-baseline justify-between text-sm">
                        <span className="font-medium">
                            {formatGoalValue(
                                goal.type,
                                current?.current_value ?? 0,
                            )}
                        </span>
                        <span className="text-muted-foreground">
                            de {formatGoalValue(goal.type, goal.target)}
                        </span>
                    </div>
                    <Progress value={percentage} />
                </div>

                {history.length > 1 && (
                    <ResponsiveContainer width="100%" height={60}>
                        <BarChart data={history.slice(-8)}>
                            <Tooltip
                                contentStyle={TOOLTIP_STYLE}
                                labelFormatter={(_, payload) =>
                                    payload?.[0]
                                        ? formatDate(
                                              payload[0].payload.period_start,
                                          )
                                        : ''
                                }
                                formatter={(value) => [
                                    formatGoalValue(goal.type, Number(value)),
                                    GOAL_TYPE_LABELS[goal.type],
                                ]}
                            />
                            <Bar
                                dataKey="current_value"
                                radius={[2, 2, 0, 0]}
                                maxBarSize={16}
                            >
                                {history.slice(-8).map((entry) => (
                                    <Cell
                                        key={entry.id}
                                        fill={CHART_COLOR}
                                        fillOpacity={
                                            entry.achieved
                                                ? 1
                                                : NOT_ACHIEVED_OPACITY
                                        }
                                    />
                                ))}
                            </Bar>
                        </BarChart>
                    </ResponsiveContainer>
                )}
            </CardContent>
        </Card>
    );
}
