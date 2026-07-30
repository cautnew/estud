import { Form, Head, router } from '@inertiajs/react';
import { endOfWeek, format, getDay, parse, startOfWeek } from 'date-fns';
import { ptBR } from 'date-fns/locale';
import { GripVertical, Pencil, Plus, Trash2 } from 'lucide-react';
import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { Calendar, dateFnsLocalizer, Views } from 'react-big-calendar';
import type { View } from 'react-big-calendar';
import withDragAndDropImport from 'react-big-calendar/lib/addons/dragAndDrop';

// Vite/Rolldown's CJS interop sometimes leaves an extra `.default` wrapper
// around this addon's export; unwrap it defensively so the call below
// always receives the actual `withDragAndDrop` function.
const withDragAndDrop = (
    withDragAndDropImport as unknown as {
        default?: typeof withDragAndDropImport;
    }
).default ?? withDragAndDropImport;
import { DndProvider, useDrag, useDrop } from 'react-dnd';
import { HTML5Backend } from 'react-dnd-html5-backend';
import ScheduleController from '@/actions/App/Http/Controllers/ScheduleController';
import ScheduleItemController from '@/actions/App/Http/Controllers/ScheduleItemController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { projectScheduleItemsToEvents } from '@/lib/schedule-projection';
import type { ProjectedEvent } from '@/lib/schedule-projection';
import { cn } from '@/lib/utils';
import { index } from '@/routes/schedules';
import type { Schedule, ScheduleItem, ScheduleType, Subject } from '@/types';

import 'react-big-calendar/lib/css/react-big-calendar.css';
import 'react-big-calendar/lib/addons/dragAndDrop/styles.css';

const TYPE_LABELS: Record<ScheduleType, string> = {
    daily: 'Diário',
    weekly: 'Semanal',
    biweekly: 'Quinzenal',
    monthly: 'Mensal',
    quarterly: 'Trimestral',
};

const WEEKDAY_LABELS = [
    'Domingo',
    'Segunda-feira',
    'Terça-feira',
    'Quarta-feira',
    'Quinta-feira',
    'Sexta-feira',
    'Sábado',
];

const localizer = dateFnsLocalizer({
    format,
    parse,
    startOfWeek,
    getDay,
    locales: { 'pt-BR': ptBR },
});

const DragAndDropCalendar = withDragAndDrop<ProjectedEvent>(Calendar);

function EventCell({ event }: { event: ProjectedEvent }) {
    return (
        <div className="flex items-center justify-between gap-1 truncate text-xs">
            <span className="truncate">{event.title}</span>
            <Badge variant="outline" className="shrink-0">
                P{event.resource.priority}
            </Badge>
        </div>
    );
}

function toDate(value: Date | string): Date {
    return typeof value === 'string' ? new Date(value) : value;
}

function movePayload(
    type: ScheduleType,
    start: Date,
    durationMinutes: number,
    priority: number,
): Record<string, string | number | null> {
    const payload: Record<string, string | number | null> = {
        start_time: format(start, 'HH:mm'),
        duration_minutes: durationMinutes,
        priority,
        day_of_week: null,
        day_of_month: null,
    };

    if (type === 'weekly' || type === 'biweekly') {
        payload.day_of_week = getDay(start);
    } else if (type === 'monthly' || type === 'quarterly') {
        payload.day_of_month = start.getDate();
    }

    return payload;
}

function PriorityRow({
    item,
    index: rowIndex,
    moveRow,
    onDropEnd,
}: {
    item: ScheduleItem;
    index: number;
    moveRow: (from: number, to: number) => void;
    onDropEnd: () => void;
}) {
    const ref = useRef<HTMLDivElement>(null);

    const [, drop] = useDrop<{ index: number }>({
        accept: 'priority-row',
        hover(dragged) {
            if (!ref.current || dragged.index === rowIndex) {
                return;
            }

            moveRow(dragged.index, rowIndex);
            dragged.index = rowIndex;
        },
    });

    const [{ isDragging }, drag] = useDrag({
        type: 'priority-row',
        item: () => ({ index: rowIndex }),
        end: onDropEnd,
        collect: (monitor) => ({ isDragging: monitor.isDragging() }),
    });

    useEffect(() => {
        drag(drop(ref.current));
    }, [drag, drop]);

    return (
        <div
            ref={ref}
            className={cn(
                'flex items-center gap-2 rounded-md border p-2',
                isDragging && 'opacity-40',
            )}
        >
            <GripVertical className="size-4 shrink-0 text-muted-foreground" />
            <span className="flex-1 truncate text-sm">
                {item.subject?.name}
            </span>
            <Badge variant="outline">P{rowIndex}</Badge>
        </div>
    );
}

export default function ScheduleShow({
    schedule,
    availableSubjects,
}: {
    schedule: Schedule;
    availableSubjects: Subject[];
}) {
    const scheduleItems = useMemo(
        () => schedule.schedule_items ?? [],
        [schedule.schedule_items],
    );

    const [editOpen, setEditOpen] = useState(false);
    const [deleteOpen, setDeleteOpen] = useState(false);
    const [addOpen, setAddOpen] = useState(false);
    const [editingItem, setEditingItem] = useState<ScheduleItem | null>(null);

    const [date, setDate] = useState(new Date());
    const [view, setView] = useState<View>(Views.WEEK);
    const [range, setRange] = useState(() => ({
        start: startOfWeek(new Date(), { weekStartsOn: 0 }),
        end: endOfWeek(new Date(), { weekStartsOn: 0 }),
    }));

    const handleRangeChange = useCallback(
        (newRange: Date[] | { start: Date; end: Date }) => {
            if (Array.isArray(newRange)) {
                setRange({
                    start: newRange[0],
                    end: newRange[newRange.length - 1],
                });
            } else {
                setRange(newRange);
            }
        },
        [],
    );

    const events = useMemo(
        () =>
            projectScheduleItemsToEvents(
                scheduleItems,
                schedule.type,
                new Date(schedule.created_at),
                range,
            ),
        [scheduleItems, schedule.type, schedule.created_at, range],
    );

    const handleEventChange = useCallback(
        ({
            event,
            start,
            end,
        }: {
            event: ProjectedEvent;
            start: Date | string;
            end: Date | string;
        }) => {
            const newStart = toDate(start);
            const newEnd = toDate(end);
            const durationMinutes = Math.round(
                (newEnd.getTime() - newStart.getTime()) / 60_000,
            );

            router.patch(
                ScheduleItemController.move.url(event.resource.id),
                movePayload(
                    schedule.type,
                    newStart,
                    durationMinutes,
                    event.resource.priority,
                ),
                { preserveScroll: true, preserveState: true },
            );
        },
        [schedule.type],
    );

    const [priorityOrder, setPriorityOrder] = useState<ScheduleItem[]>(() =>
        [...scheduleItems].sort((a, b) => a.priority - b.priority),
    );
    const [syncedScheduleItems, setSyncedScheduleItems] =
        useState(scheduleItems);

    if (scheduleItems !== syncedScheduleItems) {
        setSyncedScheduleItems(scheduleItems);
        setPriorityOrder(
            [...scheduleItems].sort((a, b) => a.priority - b.priority),
        );
    }

    const moveRow = useCallback((from: number, to: number) => {
        setPriorityOrder((current) => {
            const updated = [...current];
            const [moved] = updated.splice(from, 1);
            updated.splice(to, 0, moved);

            return updated;
        });
    }, []);

    const persistPriorityOrder = useCallback(() => {
        priorityOrder.forEach((item, itemIndex) => {
            if (item.priority === itemIndex) {
                return;
            }

            router.patch(
                ScheduleItemController.move.url(item.id),
                {
                    day_of_week: item.day_of_week,
                    day_of_month: item.day_of_month,
                    start_time: item.start_time,
                    duration_minutes: item.duration_minutes,
                    priority: itemIndex,
                },
                { preserveScroll: true, preserveState: true },
            );
        });
    }, [priorityOrder]);

    const needsDayOfWeek =
        schedule.type === 'weekly' || schedule.type === 'biweekly';
    const needsDayOfMonth =
        schedule.type === 'monthly' || schedule.type === 'quarterly';

    return (
        <>
            <Head title={schedule.name} />

            <div className="space-y-8">
                <div className="flex items-start justify-between">
                    <div className="flex items-center gap-3">
                        <Heading title={schedule.name} />
                        <Badge variant="secondary">
                            {TYPE_LABELS[schedule.type]}
                        </Badge>
                    </div>

                    <div className="flex gap-2">
                        <Dialog open={editOpen} onOpenChange={setEditOpen}>
                            <DialogTrigger asChild>
                                <Button variant="outline">
                                    <Pencil className="size-4" />
                                    Editar
                                </Button>
                            </DialogTrigger>
                            <DialogContent>
                                <DialogTitle>Editar cronograma</DialogTitle>

                                <Form
                                    {...ScheduleController.update.form(
                                        schedule.id,
                                    )}
                                    options={{ preserveScroll: true }}
                                    onSuccess={() => setEditOpen(false)}
                                    className="space-y-4"
                                >
                                    {({ processing, errors }) => (
                                        <>
                                            <div className="grid gap-2">
                                                <Label htmlFor="name">
                                                    Nome
                                                </Label>
                                                <Input
                                                    id="name"
                                                    name="name"
                                                    defaultValue={schedule.name}
                                                    required
                                                />
                                                <InputError
                                                    message={errors.name}
                                                />
                                            </div>

                                            <div className="grid gap-2">
                                                <Label htmlFor="type">
                                                    Frequência
                                                </Label>
                                                <Select
                                                    name="type"
                                                    defaultValue={schedule.type}
                                                >
                                                    <SelectTrigger
                                                        id="type"
                                                        className="w-full"
                                                    >
                                                        <SelectValue />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        {(
                                                            Object.entries(
                                                                TYPE_LABELS,
                                                            ) as [
                                                                ScheduleType,
                                                                string,
                                                            ][]
                                                        ).map(
                                                            ([
                                                                value,
                                                                label,
                                                            ]) => (
                                                                <SelectItem
                                                                    key={value}
                                                                    value={
                                                                        value
                                                                    }
                                                                >
                                                                    {label}
                                                                </SelectItem>
                                                            ),
                                                        )}
                                                    </SelectContent>
                                                </Select>
                                                <InputError
                                                    message={errors.type}
                                                />
                                            </div>

                                            <DialogFooter className="gap-2">
                                                <DialogClose asChild>
                                                    <Button variant="secondary">
                                                        Cancelar
                                                    </Button>
                                                </DialogClose>

                                                <Button
                                                    disabled={processing}
                                                    type="submit"
                                                >
                                                    Salvar
                                                </Button>
                                            </DialogFooter>
                                        </>
                                    )}
                                </Form>
                            </DialogContent>
                        </Dialog>

                        <Dialog open={deleteOpen} onOpenChange={setDeleteOpen}>
                            <DialogTrigger asChild>
                                <Button variant="outline">
                                    <Trash2 className="size-4" />
                                    Excluir
                                </Button>
                            </DialogTrigger>
                            <DialogContent>
                                <DialogTitle>Excluir cronograma</DialogTitle>
                                <DialogDescription>
                                    Tem certeza que deseja excluir "
                                    {schedule.name}"? Esta ação não pode ser
                                    desfeita.
                                </DialogDescription>

                                <Form
                                    {...ScheduleController.destroy.form(
                                        schedule.id,
                                    )}
                                    options={{ preserveScroll: true }}
                                >
                                    {({ processing }) => (
                                        <DialogFooter className="gap-2">
                                            <DialogClose asChild>
                                                <Button variant="secondary">
                                                    Cancelar
                                                </Button>
                                            </DialogClose>

                                            <Button
                                                variant="destructive"
                                                disabled={processing}
                                                type="submit"
                                            >
                                                Excluir
                                            </Button>
                                        </DialogFooter>
                                    )}
                                </Form>
                            </DialogContent>
                        </Dialog>
                    </div>
                </div>

                <div className="flex items-center justify-between">
                    <Heading
                        variant="small"
                        title="Calendário"
                        description="Arraste os itens para reagendar."
                    />

                    <Dialog open={addOpen} onOpenChange={setAddOpen}>
                        <DialogTrigger asChild>
                            <Button variant="outline">
                                <Plus className="size-4" />
                                Adicionar item
                            </Button>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogTitle>Adicionar item</DialogTitle>

                            <Form
                                {...ScheduleItemController.store.form(
                                    schedule.id,
                                )}
                                options={{ preserveScroll: true }}
                                onSuccess={() => setAddOpen(false)}
                                resetOnSuccess
                                className="space-y-4"
                            >
                                {({ processing, errors }) => (
                                    <>
                                        <div className="grid gap-2">
                                            <Label htmlFor="subject_id">
                                                Disciplina
                                            </Label>
                                            <Select name="subject_id">
                                                <SelectTrigger
                                                    id="subject_id"
                                                    className="w-full"
                                                >
                                                    <SelectValue placeholder="Selecione uma disciplina" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {availableSubjects.map(
                                                        (subject) => (
                                                            <SelectItem
                                                                key={subject.id}
                                                                value={subject.id.toString()}
                                                            >
                                                                {subject.name}
                                                            </SelectItem>
                                                        ),
                                                    )}
                                                </SelectContent>
                                            </Select>
                                            <InputError
                                                message={errors.subject_id}
                                            />
                                        </div>

                                        {needsDayOfWeek && (
                                            <div className="grid gap-2">
                                                <Label htmlFor="day_of_week">
                                                    Dia da semana
                                                </Label>
                                                <Select name="day_of_week">
                                                    <SelectTrigger
                                                        id="day_of_week"
                                                        className="w-full"
                                                    >
                                                        <SelectValue placeholder="Selecione um dia" />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        {WEEKDAY_LABELS.map(
                                                            (label, value) => (
                                                                <SelectItem
                                                                    key={value}
                                                                    value={value.toString()}
                                                                >
                                                                    {label}
                                                                </SelectItem>
                                                            ),
                                                        )}
                                                    </SelectContent>
                                                </Select>
                                                <InputError
                                                    message={errors.day_of_week}
                                                />
                                            </div>
                                        )}

                                        {needsDayOfMonth && (
                                            <div className="grid gap-2">
                                                <Label htmlFor="day_of_month">
                                                    Dia do mês
                                                </Label>
                                                <Input
                                                    id="day_of_month"
                                                    name="day_of_month"
                                                    type="number"
                                                    min={1}
                                                    max={31}
                                                    required
                                                />
                                                <InputError
                                                    message={
                                                        errors.day_of_month
                                                    }
                                                />
                                            </div>
                                        )}

                                        <div className="grid grid-cols-2 gap-4">
                                            <div className="grid gap-2">
                                                <Label htmlFor="start_time">
                                                    Horário
                                                </Label>
                                                <Input
                                                    id="start_time"
                                                    name="start_time"
                                                    type="time"
                                                    required
                                                />
                                                <InputError
                                                    message={errors.start_time}
                                                />
                                            </div>

                                            <div className="grid gap-2">
                                                <Label htmlFor="duration_minutes">
                                                    Duração (min)
                                                </Label>
                                                <Input
                                                    id="duration_minutes"
                                                    name="duration_minutes"
                                                    type="number"
                                                    min={5}
                                                    max={1440}
                                                    required
                                                />
                                                <InputError
                                                    message={
                                                        errors.duration_minutes
                                                    }
                                                />
                                            </div>
                                        </div>

                                        <DialogFooter className="gap-2">
                                            <DialogClose asChild>
                                                <Button variant="secondary">
                                                    Cancelar
                                                </Button>
                                            </DialogClose>

                                            <Button
                                                disabled={processing}
                                                type="submit"
                                            >
                                                Adicionar
                                            </Button>
                                        </DialogFooter>
                                    </>
                                )}
                            </Form>
                        </DialogContent>
                    </Dialog>
                </div>

                <DragAndDropCalendar
                    localizer={localizer}
                    culture="pt-BR"
                    events={events}
                    date={date}
                    view={view}
                    views={[Views.WEEK, Views.DAY]}
                    onNavigate={setDate}
                    onView={setView}
                    onRangeChange={handleRangeChange}
                    onEventDrop={handleEventChange}
                    onEventResize={handleEventChange}
                    onSelectEvent={(event) => setEditingItem(event.resource)}
                    resizable
                    components={{ event: EventCell }}
                    style={{ height: 600 }}
                />

                <div className="space-y-4">
                    <Heading
                        variant="small"
                        title="Prioridade das disciplinas"
                        description="Arraste para reordenar a importância de cada item."
                    />

                    {priorityOrder.length === 0 && (
                        <p className="text-sm text-muted-foreground">
                            Nenhum item adicionado a este cronograma ainda.
                        </p>
                    )}

                    <DndProvider backend={HTML5Backend}>
                        <div className="max-w-md space-y-2">
                            {priorityOrder.map((item, itemIndex) => (
                                <PriorityRow
                                    key={item.id}
                                    item={item}
                                    index={itemIndex}
                                    moveRow={moveRow}
                                    onDropEnd={persistPriorityOrder}
                                />
                            ))}
                        </div>
                    </DndProvider>
                </div>
            </div>

            <Dialog
                open={editingItem !== null}
                onOpenChange={(open) => !open && setEditingItem(null)}
            >
                <DialogContent>
                    <DialogTitle>Editar item</DialogTitle>

                    {editingItem && (
                        <Card>
                            <CardHeader>
                                <CardTitle>
                                    {editingItem.subject?.name}
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <Form
                                    key={editingItem.id}
                                    {...ScheduleItemController.update.form(
                                        editingItem.id,
                                    )}
                                    options={{ preserveScroll: true }}
                                    onSuccess={() => setEditingItem(null)}
                                    className="space-y-4"
                                >
                                    {({ processing, errors }) => (
                                        <>
                                            {needsDayOfWeek && (
                                                <div className="grid gap-2">
                                                    <Label htmlFor="edit_day_of_week">
                                                        Dia da semana
                                                    </Label>
                                                    <Select
                                                        name="day_of_week"
                                                        defaultValue={String(
                                                            editingItem.day_of_week,
                                                        )}
                                                    >
                                                        <SelectTrigger
                                                            id="edit_day_of_week"
                                                            className="w-full"
                                                        >
                                                            <SelectValue />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            {WEEKDAY_LABELS.map(
                                                                (
                                                                    label,
                                                                    value,
                                                                ) => (
                                                                    <SelectItem
                                                                        key={
                                                                            value
                                                                        }
                                                                        value={value.toString()}
                                                                    >
                                                                        {label}
                                                                    </SelectItem>
                                                                ),
                                                            )}
                                                        </SelectContent>
                                                    </Select>
                                                    <InputError
                                                        message={
                                                            errors.day_of_week
                                                        }
                                                    />
                                                </div>
                                            )}

                                            {needsDayOfMonth && (
                                                <div className="grid gap-2">
                                                    <Label htmlFor="edit_day_of_month">
                                                        Dia do mês
                                                    </Label>
                                                    <Input
                                                        id="edit_day_of_month"
                                                        name="day_of_month"
                                                        type="number"
                                                        min={1}
                                                        max={31}
                                                        defaultValue={
                                                            editingItem.day_of_month ??
                                                            undefined
                                                        }
                                                        required
                                                    />
                                                    <InputError
                                                        message={
                                                            errors.day_of_month
                                                        }
                                                    />
                                                </div>
                                            )}

                                            <div className="grid grid-cols-2 gap-4">
                                                <div className="grid gap-2">
                                                    <Label htmlFor="edit_start_time">
                                                        Horário
                                                    </Label>
                                                    <Input
                                                        id="edit_start_time"
                                                        name="start_time"
                                                        type="time"
                                                        defaultValue={
                                                            editingItem.start_time
                                                        }
                                                        required
                                                    />
                                                    <InputError
                                                        message={
                                                            errors.start_time
                                                        }
                                                    />
                                                </div>

                                                <div className="grid gap-2">
                                                    <Label htmlFor="edit_duration_minutes">
                                                        Duração (min)
                                                    </Label>
                                                    <Input
                                                        id="edit_duration_minutes"
                                                        name="duration_minutes"
                                                        type="number"
                                                        min={5}
                                                        max={1440}
                                                        defaultValue={
                                                            editingItem.duration_minutes
                                                        }
                                                        required
                                                    />
                                                    <InputError
                                                        message={
                                                            errors.duration_minutes
                                                        }
                                                    />
                                                </div>
                                            </div>

                                            <DialogFooter className="gap-2">
                                                <DialogClose asChild>
                                                    <Button variant="secondary">
                                                        Cancelar
                                                    </Button>
                                                </DialogClose>

                                                <Button
                                                    disabled={processing}
                                                    type="submit"
                                                >
                                                    Salvar
                                                </Button>
                                            </DialogFooter>
                                        </>
                                    )}
                                </Form>

                                <Form
                                    {...ScheduleItemController.destroy.form(
                                        editingItem.id,
                                    )}
                                    options={{ preserveScroll: true }}
                                    onSuccess={() => setEditingItem(null)}
                                    className="mt-4"
                                >
                                    {({ processing }) => (
                                        <Button
                                            variant="destructive"
                                            size="sm"
                                            type="submit"
                                            disabled={processing}
                                        >
                                            <Trash2 className="size-4" />
                                            Remover item
                                        </Button>
                                    )}
                                </Form>
                            </CardContent>
                        </Card>
                    )}
                </DialogContent>
            </Dialog>
        </>
    );
}

ScheduleShow.layout = {
    breadcrumbs: [
        {
            title: 'Cronogramas',
            href: index(),
        },
    ],
};
