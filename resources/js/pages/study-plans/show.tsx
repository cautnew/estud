import { Form, Head } from '@inertiajs/react';
import { Copy, Pencil, Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';
import StudyPlanController from '@/actions/App/Http/Controllers/StudyPlanController';
import StudyPlanSubjectController from '@/actions/App/Http/Controllers/StudyPlanSubjectController';
import StudyPlanTopicController from '@/actions/App/Http/Controllers/StudyPlanTopicController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
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
import { Progress } from '@/components/ui/progress';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Textarea } from '@/components/ui/textarea';
import { index } from '@/routes/study-plans';
import type {
    StudyPlan,
    StudyPlanSubject,
    StudyPlanTopic,
    Subject,
} from '@/types';

const STATUS_LABELS: Record<string, string> = {
    pending: 'Pendente',
    in_progress: 'Em andamento',
    done: 'Concluído',
};

function subjectProgress(studyPlanSubject: StudyPlanSubject): {
    done: number;
    total: number;
} {
    const topics = studyPlanSubject.study_plan_topics ?? [];

    return {
        done: topics.filter((topic) => topic.status === 'done').length,
        total: topics.length,
    };
}

export default function StudyPlanShow({
    studyPlan,
    availableSubjects,
    canManage,
}: {
    studyPlan: StudyPlan;
    availableSubjects: Subject[];
    canManage: boolean;
}) {
    const [editOpen, setEditOpen] = useState(false);
    const [deleteOpen, setDeleteOpen] = useState(false);
    const [addSubjectOpen, setAddSubjectOpen] = useState(false);
    const [addTopicSubject, setAddTopicSubject] =
        useState<StudyPlanSubject | null>(null);
    const [editingTopic, setEditingTopic] = useState<StudyPlanTopic | null>(
        null,
    );

    const studyPlanSubjects = studyPlan.study_plan_subjects ?? [];

    return (
        <>
            <Head title={studyPlan.name} />

            <div className="space-y-8">
                <div className="flex items-start justify-between">
                    <div className="flex items-center gap-3">
                        <Heading title={studyPlan.name} />
                        {studyPlan.is_public && (
                            <Badge variant="outline">Pública</Badge>
                        )}
                    </div>

                    <div className="flex gap-2">
                        <Form
                            {...StudyPlanController.duplicate.form(
                                studyPlan.id,
                            )}
                            options={{ preserveScroll: true }}
                        >
                            {({ processing }) => (
                                <Button
                                    variant="outline"
                                    type="submit"
                                    disabled={processing}
                                >
                                    <Copy className="size-4" />
                                    Duplicar
                                </Button>
                            )}
                        </Form>

                        {canManage && (
                            <>
                                <Dialog
                                    open={editOpen}
                                    onOpenChange={setEditOpen}
                                >
                                    <DialogTrigger asChild>
                                        <Button variant="outline">
                                            <Pencil className="size-4" />
                                            Editar
                                        </Button>
                                    </DialogTrigger>
                                    <DialogContent>
                                        <DialogTitle>
                                            Editar plano de estudos
                                        </DialogTitle>

                                        <Form
                                            {...StudyPlanController.update.form(
                                                studyPlan.id,
                                            )}
                                            options={{
                                                preserveScroll: true,
                                            }}
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
                                                            defaultValue={
                                                                studyPlan.name
                                                            }
                                                            required
                                                        />
                                                        <InputError
                                                            message={
                                                                errors.name
                                                            }
                                                        />
                                                    </div>

                                                    <div className="grid gap-2">
                                                        <Label htmlFor="exam_board">
                                                            Concurso
                                                        </Label>
                                                        <Input
                                                            id="exam_board"
                                                            name="exam_board"
                                                            defaultValue={
                                                                studyPlan.exam_board
                                                            }
                                                            required
                                                        />
                                                        <InputError
                                                            message={
                                                                errors.exam_board
                                                            }
                                                        />
                                                    </div>

                                                    <div className="grid gap-2">
                                                        <Label htmlFor="goal">
                                                            Objetivo
                                                        </Label>
                                                        <Textarea
                                                            id="goal"
                                                            name="goal"
                                                            defaultValue={
                                                                studyPlan.goal ??
                                                                ''
                                                            }
                                                        />
                                                        <InputError
                                                            message={
                                                                errors.goal
                                                            }
                                                        />
                                                    </div>

                                                    <div className="grid grid-cols-2 gap-4">
                                                        <div className="grid gap-2">
                                                            <Label htmlFor="deadline">
                                                                Prazo final
                                                            </Label>
                                                            <Input
                                                                id="deadline"
                                                                name="deadline"
                                                                type="date"
                                                                defaultValue={
                                                                    studyPlan.deadline ??
                                                                    ''
                                                                }
                                                            />
                                                            <InputError
                                                                message={
                                                                    errors.deadline
                                                                }
                                                            />
                                                        </div>

                                                        <div className="grid gap-2">
                                                            <Label htmlFor="exam_date">
                                                                Data da prova
                                                            </Label>
                                                            <Input
                                                                id="exam_date"
                                                                name="exam_date"
                                                                type="date"
                                                                defaultValue={
                                                                    studyPlan.exam_date ??
                                                                    ''
                                                                }
                                                            />
                                                            <InputError
                                                                message={
                                                                    errors.exam_date
                                                                }
                                                            />
                                                        </div>
                                                    </div>

                                                    <div className="flex items-center gap-2">
                                                        <Checkbox
                                                            id="is_public"
                                                            name="is_public"
                                                            value="1"
                                                            defaultChecked={
                                                                studyPlan.is_public
                                                            }
                                                        />
                                                        <Label htmlFor="is_public">
                                                            Tornar este plano
                                                            público
                                                        </Label>
                                                    </div>

                                                    <DialogFooter className="gap-2">
                                                        <DialogClose asChild>
                                                            <Button variant="secondary">
                                                                Cancelar
                                                            </Button>
                                                        </DialogClose>

                                                        <Button
                                                            disabled={
                                                                processing
                                                            }
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

                                <Dialog
                                    open={deleteOpen}
                                    onOpenChange={setDeleteOpen}
                                >
                                    <DialogTrigger asChild>
                                        <Button variant="outline">
                                            <Trash2 className="size-4" />
                                            Excluir
                                        </Button>
                                    </DialogTrigger>
                                    <DialogContent>
                                        <DialogTitle>
                                            Excluir plano de estudos
                                        </DialogTitle>
                                        <DialogDescription>
                                            Tem certeza que deseja excluir "
                                            {studyPlan.name}"? Esta ação não
                                            pode ser desfeita.
                                        </DialogDescription>

                                        <Form
                                            {...StudyPlanController.destroy.form(
                                                studyPlan.id,
                                            )}
                                            options={{
                                                preserveScroll: true,
                                            }}
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
                            </>
                        )}
                    </div>
                </div>

                <div className="grid max-w-lg gap-1 text-sm text-muted-foreground">
                    <p>Concurso: {studyPlan.exam_board}</p>
                    {studyPlan.goal && <p>Objetivo: {studyPlan.goal}</p>}
                    {studyPlan.deadline && (
                        <p>Prazo final: {studyPlan.deadline}</p>
                    )}
                    {studyPlan.exam_date && (
                        <p>Data da prova: {studyPlan.exam_date}</p>
                    )}
                </div>

                <div className="space-y-4">
                    <div className="flex items-center justify-between">
                        <Heading
                            variant="small"
                            title="Disciplinas"
                            description="Disciplinas e tópicos deste plano."
                        />

                        {canManage && (
                            <Dialog
                                open={addSubjectOpen}
                                onOpenChange={setAddSubjectOpen}
                            >
                                <DialogTrigger asChild>
                                    <Button variant="outline">
                                        <Plus className="size-4" />
                                        Adicionar disciplina
                                    </Button>
                                </DialogTrigger>
                                <DialogContent>
                                    <DialogTitle>
                                        Adicionar disciplina
                                    </DialogTitle>

                                    <Form
                                        {...StudyPlanSubjectController.store.form(
                                            studyPlan.id,
                                        )}
                                        options={{ preserveScroll: true }}
                                        onSuccess={() =>
                                            setAddSubjectOpen(false)
                                        }
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
                                                                        key={
                                                                            subject.id
                                                                        }
                                                                        value={subject.id.toString()}
                                                                    >
                                                                        {
                                                                            subject.name
                                                                        }
                                                                    </SelectItem>
                                                                ),
                                                            )}
                                                        </SelectContent>
                                                    </Select>
                                                    <InputError
                                                        message={
                                                            errors.subject_id
                                                        }
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
                                                        Adicionar
                                                    </Button>
                                                </DialogFooter>
                                            </>
                                        )}
                                    </Form>
                                </DialogContent>
                            </Dialog>
                        )}
                    </div>

                    {studyPlanSubjects.length === 0 && (
                        <p className="text-sm text-muted-foreground">
                            Nenhuma disciplina adicionada a este plano ainda.
                        </p>
                    )}

                    {studyPlanSubjects.map((studyPlanSubject) => {
                        const { done, total } =
                            subjectProgress(studyPlanSubject);

                        return (
                            <Card key={studyPlanSubject.id}>
                                <CardHeader className="flex-row items-center justify-between">
                                    <div className="space-y-1">
                                        <CardTitle>
                                            {studyPlanSubject.subject?.name}
                                        </CardTitle>
                                        <Progress
                                            value={
                                                total > 0
                                                    ? (done / total) * 100
                                                    : 0
                                            }
                                            className="w-48"
                                        />
                                        <p className="text-xs text-muted-foreground">
                                            {done}/{total} tópicos concluídos
                                        </p>
                                    </div>

                                    {canManage && (
                                        <Form
                                            {...StudyPlanSubjectController.destroy.form(
                                                studyPlanSubject.id,
                                            )}
                                            options={{
                                                preserveScroll: true,
                                            }}
                                        >
                                            {({ processing }) => (
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    type="submit"
                                                    disabled={processing}
                                                    title="Remover disciplina"
                                                >
                                                    <Trash2 className="size-4" />
                                                </Button>
                                            )}
                                        </Form>
                                    )}
                                </CardHeader>

                                <CardContent className="space-y-4">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>Tópico</TableHead>
                                                <TableHead>Status</TableHead>
                                                {canManage && (
                                                    <TableHead className="text-right">
                                                        &nbsp;
                                                    </TableHead>
                                                )}
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {(
                                                studyPlanSubject.study_plan_topics ??
                                                []
                                            ).map((topic) => (
                                                <TableRow key={topic.id}>
                                                    <TableCell>
                                                        {topic.topic?.name}
                                                    </TableCell>
                                                    <TableCell>
                                                        {canManage ? (
                                                            <Form
                                                                {...StudyPlanTopicController.update.form(
                                                                    topic.id,
                                                                )}
                                                                options={{
                                                                    preserveScroll: true,
                                                                }}
                                                            >
                                                                {({
                                                                    processing,
                                                                }) => (
                                                                    <select
                                                                        name="status"
                                                                        defaultValue={
                                                                            topic.status
                                                                        }
                                                                        disabled={
                                                                            processing
                                                                        }
                                                                        onChange={(
                                                                            event,
                                                                        ) =>
                                                                            event.currentTarget.form?.requestSubmit()
                                                                        }
                                                                        className="rounded-md border border-input bg-transparent px-2 py-1 text-sm"
                                                                    >
                                                                        <option value="pending">
                                                                            Pendente
                                                                        </option>
                                                                        <option value="in_progress">
                                                                            Em
                                                                            andamento
                                                                        </option>
                                                                        <option value="done">
                                                                            Concluído
                                                                        </option>
                                                                    </select>
                                                                )}
                                                            </Form>
                                                        ) : (
                                                            STATUS_LABELS[
                                                                topic.status
                                                            ]
                                                        )}
                                                    </TableCell>
                                                    {canManage && (
                                                        <TableCell className="flex justify-end gap-1">
                                                            <Button
                                                                variant="ghost"
                                                                size="icon"
                                                                onClick={() =>
                                                                    setEditingTopic(
                                                                        topic,
                                                                    )
                                                                }
                                                            >
                                                                <Pencil className="size-4" />
                                                            </Button>

                                                            <Form
                                                                {...StudyPlanTopicController.destroy.form(
                                                                    topic.id,
                                                                )}
                                                                options={{
                                                                    preserveScroll: true,
                                                                }}
                                                            >
                                                                {({
                                                                    processing,
                                                                }) => (
                                                                    <Button
                                                                        variant="ghost"
                                                                        size="icon"
                                                                        type="submit"
                                                                        disabled={
                                                                            processing
                                                                        }
                                                                        title="Remover tópico"
                                                                    >
                                                                        <Trash2 className="size-4" />
                                                                    </Button>
                                                                )}
                                                            </Form>
                                                        </TableCell>
                                                    )}
                                                </TableRow>
                                            ))}
                                        </TableBody>
                                    </Table>

                                    {canManage && (
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            onClick={() =>
                                                setAddTopicSubject(
                                                    studyPlanSubject,
                                                )
                                            }
                                        >
                                            <Plus className="size-4" />
                                            Adicionar tópico
                                        </Button>
                                    )}
                                </CardContent>
                            </Card>
                        );
                    })}
                </div>
            </div>

            {canManage && (
                <Dialog
                    open={addTopicSubject !== null}
                    onOpenChange={(open) => !open && setAddTopicSubject(null)}
                >
                    <DialogContent>
                        <DialogTitle>Adicionar tópico</DialogTitle>

                        {addTopicSubject && (
                            <Form
                                key={addTopicSubject.id}
                                {...StudyPlanTopicController.store.form(
                                    addTopicSubject.id,
                                )}
                                options={{ preserveScroll: true }}
                                onSuccess={() => setAddTopicSubject(null)}
                                className="space-y-4"
                            >
                                {({ processing, errors }) => (
                                    <>
                                        <div className="grid gap-2">
                                            <Label htmlFor="topic_id">
                                                Tópico
                                            </Label>
                                            <Select name="topic_id">
                                                <SelectTrigger
                                                    id="topic_id"
                                                    className="w-full"
                                                >
                                                    <SelectValue placeholder="Selecione um tópico" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {(
                                                        addTopicSubject.subject
                                                            ?.topics ?? []
                                                    )
                                                        .filter(
                                                            (topic) =>
                                                                !(
                                                                    addTopicSubject.study_plan_topics ??
                                                                    []
                                                                ).some(
                                                                    (
                                                                        planTopic,
                                                                    ) =>
                                                                        planTopic.topic_id ===
                                                                        topic.id,
                                                                ),
                                                        )
                                                        .map((topic) => (
                                                            <SelectItem
                                                                key={topic.id}
                                                                value={topic.id.toString()}
                                                            >
                                                                {topic.name}
                                                            </SelectItem>
                                                        ))}
                                                </SelectContent>
                                            </Select>
                                            <InputError
                                                message={errors.topic_id}
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
                                                Adicionar
                                            </Button>
                                        </DialogFooter>
                                    </>
                                )}
                            </Form>
                        )}
                    </DialogContent>
                </Dialog>
            )}

            {canManage && (
                <Dialog
                    open={editingTopic !== null}
                    onOpenChange={(open) => !open && setEditingTopic(null)}
                >
                    <DialogContent>
                        <DialogTitle>Editar tópico</DialogTitle>

                        {editingTopic && (
                            <Form
                                key={editingTopic.id}
                                {...StudyPlanTopicController.update.form(
                                    editingTopic.id,
                                )}
                                options={{ preserveScroll: true }}
                                onSuccess={() => setEditingTopic(null)}
                                className="space-y-4"
                            >
                                {({ processing, errors }) => (
                                    <>
                                        <div className="grid gap-2">
                                            <Label htmlFor="studied_at">
                                                Estudado em
                                            </Label>
                                            <Input
                                                id="studied_at"
                                                name="studied_at"
                                                type="date"
                                                defaultValue={
                                                    editingTopic.studied_at?.slice(
                                                        0,
                                                        10,
                                                    ) ?? ''
                                                }
                                            />
                                            <InputError
                                                message={errors.studied_at}
                                            />
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor="notes">
                                                Anotações
                                            </Label>
                                            <Textarea
                                                id="notes"
                                                name="notes"
                                                defaultValue={
                                                    editingTopic.notes ?? ''
                                                }
                                            />
                                            <InputError
                                                message={errors.notes}
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
                        )}
                    </DialogContent>
                </Dialog>
            )}
        </>
    );
}

StudyPlanShow.layout = {
    breadcrumbs: [
        {
            title: 'Planos de Estudo',
            href: index(),
        },
    ],
};
