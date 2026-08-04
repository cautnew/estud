import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { formatDate } from '@/lib/date';

const WEEKDAY_LABELS = ['', 'Seg', '', 'Qua', '', 'Sex', ''];

function levelFor(hours: number, maxHours: number): number {
    if (hours <= 0 || maxHours <= 0) {
        return 0;
    }

    const ratio = hours / maxHours;

    if (ratio <= 0.25) {
        return 1;
    }

    if (ratio <= 0.5) {
        return 2;
    }

    if (ratio <= 0.75) {
        return 3;
    }

    return 4;
}

const LEVEL_OPACITY = [0, 0.25, 0.5, 0.75, 1];

export default function StudyHeatmap({
    data,
}: {
    data: { date: string; hours: number }[];
}) {
    if (data.length === 0) {
        return null;
    }

    const maxHours = Math.max(...data.map((day) => day.hours));
    const firstWeekday = new Date(`${data[0].date}T00:00:00`).getDay();

    const cells: ({ date: string; hours: number } | null)[] = [
        ...Array(firstWeekday).fill(null),
        ...data,
    ];

    const weeks: ({ date: string; hours: number } | null)[][] = [];

    for (let i = 0; i < cells.length; i += 7) {
        weeks.push(cells.slice(i, i + 7));
    }

    return (
        <Card>
            <CardHeader>
                <CardTitle>Frequência de estudo</CardTitle>
                <CardDescription>Últimos 365 dias</CardDescription>
            </CardHeader>
            <CardContent>
                <div className="flex gap-3 overflow-x-auto pb-2">
                    <div className="flex flex-col justify-between py-[1px] text-[10px] text-muted-foreground">
                        {WEEKDAY_LABELS.map((label, index) => (
                            <span
                                key={index}
                                className="h-[11px] leading-[11px]"
                            >
                                {label}
                            </span>
                        ))}
                    </div>

                    <div className="flex gap-[3px]">
                        {weeks.map((week, weekIndex) => (
                            <div
                                key={weekIndex}
                                className="flex flex-col gap-[3px]"
                            >
                                {week.map((day, dayIndex) => {
                                    if (!day) {
                                        return (
                                            <div
                                                key={dayIndex}
                                                className="size-[11px]"
                                            />
                                        );
                                    }

                                    const level = levelFor(day.hours, maxHours);

                                    return (
                                        <div
                                            key={dayIndex}
                                            title={`${formatDate(day.date)}: ${day.hours}h`}
                                            className="size-[11px] rounded-[2px]"
                                            style={{
                                                backgroundColor:
                                                    level === 0
                                                        ? 'var(--muted)'
                                                        : 'var(--chart-1)',
                                                opacity:
                                                    level === 0
                                                        ? 1
                                                        : LEVEL_OPACITY[level],
                                            }}
                                        />
                                    );
                                })}
                            </div>
                        ))}
                    </div>
                </div>

                <div className="mt-2 flex items-center justify-end gap-1 text-[10px] text-muted-foreground">
                    <span>Menos</span>
                    {LEVEL_OPACITY.map((opacity, level) => (
                        <div
                            key={level}
                            className="size-[11px] rounded-[2px]"
                            style={{
                                backgroundColor:
                                    level === 0
                                        ? 'var(--muted)'
                                        : 'var(--chart-1)',
                                opacity: level === 0 ? 1 : opacity,
                            }}
                        />
                    ))}
                    <span>Mais</span>
                </div>
            </CardContent>
        </Card>
    );
}
