import { Form, Head } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import { Pencil, Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';
import StudySessionController from '@/actions/App/Http/Controllers/StudySessionController';
import DataTable from '@/components/data-table';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import StudySessionStopwatchCard from '@/components/study-session-stopwatch-card';
import { Button } from '@/components/ui/button';
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
import { Textarea } from '@/components/ui/textarea';
import { formatDate, formatDuration } from '@/lib/study-session';
import { index } from '@/routes/study-sessions';
import type { StudySession, Subject } from '@/types';

function SubjectTopicFields({
    subjects,
    defaultSubjectId,
    defaultTopicId,
    errors,
}: {
    subjects: Subject[];
    defaultSubjectId?: number;
    defaultTopicId?: number | null;
    errors: Partial<Record<'subject_id' | 'topic_id', string>>;
}) {
    const [subjectId, setSubjectId] = useState(
        defaultSubjectId ? String(defaultSubjectId) : '',
    );
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
                <Select
                    key={subjectId}
                    name="topic_id"
                    defaultValue={
                        defaultTopicId ? String(defaultTopicId) : undefined
                    }
                >
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

export default function StudySessionsIndex({
    studySessions,
    availableSubjects,
}: {
    studySessions: StudySession[];
    availableSubjects: Subject[];
}) {
    const [createOpen, setCreateOpen] = useState(false);
    const [editingSession, setEditingSession] = useState<StudySession | null>(
        null,
    );
    const [deletingSession, setDeletingSession] = useState<StudySession | null>(
        null,
    );

    const activeSession = studySessions.find(
        (session) => session.status !== 'completed',
    );
    const history = studySessions.filter(
        (session) => session.status === 'completed',
    );

    const columns: ColumnDef<StudySession>[] = [
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
            id: 'duration',
            header: 'Duração',
            cell: ({ row }) => formatDuration(row.original.duration_seconds),
        },
        {
            accessorKey: 'note',
            header: 'Observação',
            cell: ({ row }) => (
                <span className="line-clamp-2 text-sm text-muted-foreground">
                    {row.original.note}
                </span>
            ),
        },
        {
            id: 'actions',
            header: '',
            cell: ({ row }) => (
                <div className="flex justify-end gap-1">
                    <Button
                        variant="ghost"
                        size="icon"
                        title="Editar"
                        onClick={() => setEditingSession(row.original)}
                    >
                        <Pencil className="size-4" />
                    </Button>
                    <Button
                        variant="ghost"
                        size="icon"
                        title="Excluir"
                        onClick={() => setDeletingSession(row.original)}
                    >
                        <Trash2 className="size-4" />
                    </Button>
                </div>
            ),
        },
    ];

    return (
        <>
            <Head title="Sessões de estudo" />

            <div className="flex items-center justify-between">
                <Heading
                    title="Sessões de estudo"
                    description="Registre e cronometre suas sessões de estudo."
                />

                {!activeSession && (
                    <Dialog open={createOpen} onOpenChange={setCreateOpen}>
                        <DialogTrigger asChild>
                            <Button>
                                <Plus className="size-4" />
                                Iniciar sessão
                            </Button>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogTitle>Iniciar sessão de estudo</DialogTitle>
                            <DialogDescription>
                                O cronômetro começa a contar assim que a sessão
                                é criada.
                            </DialogDescription>

                            <Form
                                {...StudySessionController.store.form()}
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
                                            <Label htmlFor="note">
                                                Observação
                                            </Label>
                                            <Textarea id="note" name="note" />
                                            <InputError message={errors.note} />
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
                                                Iniciar
                                            </Button>
                                        </DialogFooter>
                                    </>
                                )}
                            </Form>
                        </DialogContent>
                    </Dialog>
                )}
            </div>

            {activeSession && (
                <div className="mt-6">
                    <StudySessionStopwatchCard session={activeSession} />
                </div>
            )}

            <div className="mt-6">
                <DataTable
                    columns={columns}
                    data={history}
                    emptyTitle="Nenhuma sessão concluída"
                    emptyDescription="Suas sessões concluídas aparecerão aqui."
                />
            </div>

            <Dialog
                open={editingSession !== null}
                onOpenChange={(open) => !open && setEditingSession(null)}
            >
                <DialogContent>
                    <DialogTitle>Editar sessão de estudo</DialogTitle>
                    <DialogDescription>
                        Ajuste a disciplina, o tópico, a data ou a observação
                        registrada.
                    </DialogDescription>

                    {editingSession && (
                        <Form
                            {...StudySessionController.update.form(
                                editingSession.id,
                            )}
                            options={{ preserveScroll: true }}
                            onSuccess={() => setEditingSession(null)}
                            className="space-y-4"
                        >
                            {({ processing, errors }) => (
                                <>
                                    <SubjectTopicFields
                                        subjects={availableSubjects}
                                        defaultSubjectId={
                                            editingSession.subject_id
                                        }
                                        defaultTopicId={editingSession.topic_id}
                                        errors={errors}
                                    />

                                    <div className="grid gap-2">
                                        <Label htmlFor="date">Data</Label>
                                        <Input
                                            id="date"
                                            name="date"
                                            type="date"
                                            defaultValue={editingSession.date.slice(
                                                0,
                                                10,
                                            )}
                                        />
                                        <InputError message={errors.date} />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="edit-note">
                                            Observação
                                        </Label>
                                        <Textarea
                                            id="edit-note"
                                            name="note"
                                            defaultValue={
                                                editingSession.note ?? ''
                                            }
                                        />
                                        <InputError message={errors.note} />
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
                                            Salvar
                                        </Button>
                                    </DialogFooter>
                                </>
                            )}
                        </Form>
                    )}
                </DialogContent>
            </Dialog>

            <Dialog
                open={deletingSession !== null}
                onOpenChange={(open) => !open && setDeletingSession(null)}
            >
                <DialogContent>
                    <DialogTitle>Excluir sessão</DialogTitle>
                    <DialogDescription>
                        Tem certeza que deseja excluir esta sessão de estudo?
                        Esta ação não pode ser desfeita.
                    </DialogDescription>

                    {deletingSession && (
                        <Form
                            {...StudySessionController.destroy.form(
                                deletingSession.id,
                            )}
                            options={{ preserveScroll: true }}
                            onSuccess={() => setDeletingSession(null)}
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

StudySessionsIndex.layout = {
    breadcrumbs: [
        {
            title: 'Sessões de estudo',
            href: index(),
        },
    ],
};
