import {
    differenceInCalendarMonths,
    differenceInCalendarWeeks,
    eachDayOfInterval,
    getDate,
    getDay,
    getDaysInMonth,
} from 'date-fns';
import type { ScheduleItem, ScheduleType } from '@/types';

export type ProjectedEvent = {
    id: string;
    title: string;
    start: Date;
    end: Date;
    resource: ScheduleItem;
};

/**
 * Expands each ScheduleItem into concrete dated calendar events for the
 * visible range, based on the schedule's recurrence `type`. Biweekly and
 * quarterly cadences are anchored to `anchor` (the schedule's creation
 * date) to decide which weeks/months are "on".
 */
export function projectScheduleItemsToEvents(
    items: ScheduleItem[],
    type: ScheduleType,
    anchor: Date,
    range: { start: Date; end: Date },
): ProjectedEvent[] {
    const days = eachDayOfInterval(range);
    const events: ProjectedEvent[] = [];

    for (const item of items) {
        for (const day of days) {
            if (!isDayEligible(item, type, anchor, day)) {
                continue;
            }

            const start = withTime(day, item.start_time);
            const end = new Date(
                start.getTime() + item.duration_minutes * 60_000,
            );

            events.push({
                id: `${item.id}-${start.toISOString()}`,
                title: item.subject?.name ?? '',
                start,
                end,
                resource: item,
            });
        }
    }

    return events;
}

function withTime(day: Date, time: string): Date {
    const [hours, minutes] = time.split(':').map(Number);
    const result = new Date(day);
    result.setHours(hours, minutes, 0, 0);

    return result;
}

function isDayEligible(
    item: ScheduleItem,
    type: ScheduleType,
    anchor: Date,
    day: Date,
): boolean {
    switch (type) {
        case 'daily':
            return true;
        case 'weekly':
            return getDay(day) === item.day_of_week;
        case 'biweekly':
            return (
                getDay(day) === item.day_of_week &&
                differenceInCalendarWeeks(day, anchor, { weekStartsOn: 0 }) %
                    2 ===
                    0
            );
        case 'monthly':
            return getDate(day) === clampedDayOfMonth(item.day_of_month, day);
        case 'quarterly':
            return (
                getDate(day) === clampedDayOfMonth(item.day_of_month, day) &&
                differenceInCalendarMonths(day, anchor) % 3 === 0
            );
        default:
            return false;
    }
}

function clampedDayOfMonth(
    dayOfMonth: number | null,
    referenceDay: Date,
): number {
    if (dayOfMonth === null) {
        return -1;
    }

    return Math.min(dayOfMonth, getDaysInMonth(referenceDay));
}
