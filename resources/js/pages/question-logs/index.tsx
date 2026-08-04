import { Form, Head } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import { Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';
import {
    CartesianGrid,
    Line,
    LineChart,
    Bar,
    BarChart,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from 'recharts';
import QuestionLogController from '@/actions/App/Http/Controllers/QuestionLogController';
import DataTable from '@/components/data-table';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import {
    EmptyState,
    EmptyStateDescription,
    EmptyStateTitle,
} from '@/components/ui/empty-state';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    AXIS_TICK,
    CHART_COLOR,
    GRID_COLOR,
    TOOLTIP_STYLE,
} from '@/lib/chart-theme';
import { formatDate } from '@/lib/date';
import { index } from '@/routes/question-logs';
import type { QuestionLog, QuestionStats, Subject } from '@/types';

function SubjectTopicFields({
    subjects,
    errors,
}: {
    subjects: Subject[];
    errors: Partial<Record<'subject_id' | 'topic_id', string>>;
}) {
    const [subjectId, setSubjectId] = useState('');
    const topics =
        subjects.find((subject) => subject.id === Number(subjectId))?.topics ??
        [];

    return (
        <>
            <div className="grid gap-2">
                <Label htmlFor="subject_id">Disciplina</Label>
                <Select
                    name="subject_id"
                    value={subjectId}
                    onValueChange={setSubjectId}
                >
                    <SelectTrigger id="subject_id" className="w-full">
                        <SelectValue placeholder="Selecione a disciplina" />
                    </SelectTrigger>
                    <SelectContent>
                        {subjects.map((subject) => (
                            <SelectItem
                                key={subject.id}
                                value={String(subject.id)}
                            >
                                {subject.name}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                <InputError message={errors.subject_id} />
            </div>

            <div className="grid gap-2">
                <Label htmlFor="topic_id">Tópico</Label>
                <Select key={subjectId} name="topic_id">
                    <SelectTrigger id="topic_id" className="w-full">
                        <SelectValue placeholder="Sem tópico específico" />
                    </SelectTrigger>
                    <SelectContent>
                        {topics.map((topic) => (
                            <SelectItem key={topic.id} value={String(topic.id)}>
                                {topic.name}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                <InputError message={errors.topic_id} />
            </div>
        </>
    );
}

export default function QuestionLogsIndex({
    questionLogs,
    availableSubjects,
    stats,
}: {
    questionLogs: QuestionLog[];
    availableSubjects: Subject[];
    stats: QuestionStats;
}) {
    const [createOpen, setCreateOpen] = useState(false);
    const [deletingLog, setDeletingLog] = useState<QuestionLog | null>(null);

    const columns: ColumnDef<QuestionLog>[] = [
        {
            accessorKey: 'date',
            header: 'Data',
            cell: ({ row }) => formatDate(row.original.date),
        },
        {
            id: 'subject',
            header: 'Disciplina / Tópico',
            cell: ({ row }) => (
                <div>
                    <div className="font-medium">
                        {row.original.subject?.name}
                    </div>
                    {row.original.topic && (
                        <div className="text-sm text-muted-foreground">
                            {row.original.topic.name}
                        </div>
                    )}
                </div>
            ),
        },
        {
            accessorKey: 'total',
            header: 'Resolvidas',
        },
        {
            accessorKey: 'correct',
            header: 'Corretas',
        },
        {
            accessorKey: 'incorrect',
            header: 'Incorretas',
        },
        {
            id: 'accuracy',
            header: 'Aproveitamento',
            cell: ({ row }) =>
                row.original.total > 0
                    ? `${Math.round((row.original.correct / row.original.total) * 100)}%`
                    : '—',
        },
        {
            id: 'actions',
            header: '',
            cell: ({ row }) => (
                <div className="flex justify-end">
                    <Button
                        variant="ghost"
                        size="icon"
                        title="Excluir"
                        onClick={() => setDeletingLog(row.original)}
                    >
                        <Trash2 className="size-4" />
                    </Button>
                </div>
            ),
        },
    ];

    return (
        <>
            <Head title="Questões" />

            <div className="flex items-center justify-between">
                <Heading
                    title="Questões"
                    description="Acompanhe seu desempenho em questões resolvidas."
                />

                <Dialog open={createOpen} onOpenChange={setCreateOpen}>
                    <DialogTrigger asChild>
                        <Button>
                            <Plus className="size-4" />
                            Registrar questões
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogTitle>Registrar questões</DialogTitle>
                        <DialogDescription>
                            Informe quantas questões você resolveu e quantas
                            acertou.
                        </DialogDescription>

                        <Form
                            {...QuestionLogController.store.form()}
                            options={{ preserveScroll: true }}
                            onSuccess={() => setCreateOpen(false)}
                            resetOnSuccess
                            className="space-y-4"
                        >
                            {({ processing, errors }) => (
                                <>
                                    <SubjectTopicFields
                                        subjects={availableSubjects}
                                        errors={errors}
                                    />

                                    <div className="grid gap-2">
                                        <Label htmlFor="date">Data</Label>
                                        <Input
                                            id="date"
                                            name="date"
                                            type="date"
                                            defaultValue={new Date()
                                                .toISOString()
                                                .slice(0, 10)}
                                            required
                                        />
                                        <InputError message={errors.date} />
                                    </div>

                                    <div className="grid grid-cols-3 gap-2">
                                        <div className="grid gap-2">
                                            <Label htmlFor="total">
                                                Resolvidas
                                            </Label>
                                            <Input
                                                id="total"
                                                name="total"
                                                type="number"
                                                min={1}
                                                required
                                            />
                                            <InputError
                                                message={errors.total}
                                            />
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor="correct">
                                                Corretas
                                            </Label>
                                            <Input
                                                id="correct"
                                                name="correct"
                                                type="number"
                                                min={0}
                                                required
                                            />
                                            <InputError
                                                message={errors.correct}
                                            />
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor="incorrect">
                                                Incorretas
                                            </Label>
                                            <Input
                                                id="incorrect"
                                                name="incorrect"
                                                type="number"
                                                min={0}
                                                required
                                            />
                                            <InputError
                                                message={errors.incorrect}
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
                                            type="submit"
                                            disabled={processing}
                                        >
                                            Registrar
                                        </Button>
                                    </DialogFooter>
                                </>
                            )}
                        </Form>
                    </DialogContent>
                </Dialog>
            </div>

            <div className="mt-6 grid gap-4 md:grid-cols-3">
                <Card>
                    <CardHeader>
                        <CardDescription>Taxa de acerto geral</CardDescription>
                        <CardTitle className="text-3xl">
                            {stats.overall.accuracy_rate}%
                        </CardTitle>
                    </CardHeader>
                </Card>

                <Card>
                    <CardHeader>
                        <CardDescription>Questões resolvidas</CardDescription>
                        <CardTitle className="text-3xl">
                            {stats.overall.total}
                        </CardTitle>
                    </CardHeader>
                </Card>

                <Card>
                    <CardHeader>
                        <CardDescription>Corretas / Incorretas</CardDescription>
                        <CardTitle className="text-3xl">
                            {stats.overall.correct} / {stats.overall.incorrect}
                        </CardTitle>
                    </CardHeader>
                </Card>
            </div>

            <div className="mt-6 grid gap-4 lg:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>Evolução histórica</CardTitle>
                        <CardDescription>
                            Taxa de acerto (%) por dia
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {stats.history.length === 0 ? (
                            <EmptyState>
                                <EmptyStateTitle>
                                    Sem dados suficientes
                                </EmptyStateTitle>
                                <EmptyStateDescription>
                                    Registre questões em dias diferentes para
                                    ver sua evolução.
                                </EmptyStateDescription>
                            </EmptyState>
                        ) : (
                            <ResponsiveContainer width="100%" height={260}>
                                <LineChart data={stats.history}>
                                    <CartesianGrid
                                        stroke={GRID_COLOR}
                                        vertical={false}
                                    />
                                    <XAxis
                                        dataKey="date"
                                        tickFormatter={formatDate}
                                        tick={AXIS_TICK}
                                        axisLine={{ stroke: GRID_COLOR }}
                                        tickLine={false}
                                    />
                                    <YAxis
                                        domain={[0, 100]}
                                        tick={AXIS_TICK}
                                        axisLine={false}
                                        tickLine={false}
                                        width={36}
                                    />
                                    <Tooltip
                                        contentStyle={TOOLTIP_STYLE}
                                        labelFormatter={(value) =>
                                            formatDate(String(value))
                                        }
                                        formatter={(value) => [
                                            `${value}%`,
                                            'Taxa de acerto',
                                        ]}
                                    />
                                    <Line
                                        type="monotone"
                                        dataKey="accuracy_rate"
                                        stroke={CHART_COLOR}
                                        strokeWidth={2}
                                        dot={{ r: 4, fill: CHART_COLOR }}
                                    />
                                </LineChart>
                            </ResponsiveContainer>
                        )}
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Média por disciplina</CardTitle>
                        <CardDescription>
                            Taxa de acerto (%) por disciplina
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {stats.bySubject.length === 0 ? (
                            <EmptyState>
                                <EmptyStateTitle>
                                    Nenhuma disciplina registrada
                                </EmptyStateTitle>
                                <EmptyStateDescription>
                                    Registre questões para ver a média por
                                    disciplina.
                                </EmptyStateDescription>
                            </EmptyState>
                        ) : (
                            <ResponsiveContainer width="100%" height={260}>
                                <BarChart data={stats.bySubject}>
                                    <CartesianGrid
                                        stroke={GRID_COLOR}
                                        vertical={false}
                                    />
                                    <XAxis
                                        dataKey="subject"
                                        tick={AXIS_TICK}
                                        axisLine={{ stroke: GRID_COLOR }}
                                        tickLine={false}
                                    />
                                    <YAxis
                                        domain={[0, 100]}
                                        tick={AXIS_TICK}
                                        axisLine={false}
                                        tickLine={false}
                                        width={36}
                                    />
                                    <Tooltip
                                        contentStyle={TOOLTIP_STYLE}
                                        formatter={(value) => [
                                            `${value}%`,
                                            'Taxa de acerto',
                                        ]}
                                    />
                                    <Bar
                                        dataKey="accuracy_rate"
                                        fill={CHART_COLOR}
                                        radius={[4, 4, 0, 0]}
                                        maxBarSize={24}
                                    />
                                </BarChart>
                            </ResponsiveContainer>
                        )}
                    </CardContent>
                </Card>
            </div>

            <div className="mt-6">
                <DataTable
                    columns={columns}
                    data={questionLogs}
                    emptyTitle="Nenhum registro"
                    emptyDescription="Registre suas primeiras questões resolvidas."
                />
            </div>

            <Dialog
                open={deletingLog !== null}
                onOpenChange={(open) => !open && setDeletingLog(null)}
            >
                <DialogContent>
                    <DialogTitle>Excluir registro</DialogTitle>
                    <DialogDescription>
                        Tem certeza que deseja excluir este registro? Esta ação
                        não pode ser desfeita.
                    </DialogDescription>

                    {deletingLog && (
                        <Form
                            {...QuestionLogController.destroy.form(
                                deletingLog.id,
                            )}
                            options={{ preserveScroll: true }}
                            onSuccess={() => setDeletingLog(null)}
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
                    )}
                </DialogContent>
            </Dialog>
        </>
    );
}

QuestionLogsIndex.layout = {
    breadcrumbs: [
        {
            title: 'Questões',
            href: index(),
        },
    ],
};
