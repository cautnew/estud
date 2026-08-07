import { Form, Head } from '@inertiajs/react';
import { Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';
import ExamNoticeController from '@/actions/App/Http/Controllers/Admin/ExamNoticeController';
import ExamSubjectController from '@/actions/App/Http/Controllers/Admin/ExamSubjectController';
import ExamTopicController from '@/actions/App/Http/Controllers/Admin/ExamTopicController';
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
import { dashboard } from '@/routes/admin';
import { index } from '@/routes/admin/exam-notices';
import type { ExamNotice, ExamNoticeProgress, Subject } from '@/types';

function AddSubjectDialog({
    examNotice,
    availableSubjects,
}: {
    examNotice: ExamNotice;
    availableSubjects: Subject[];
}) {
    const [open, setOpen] = useState(false);
    const usedSubjectIds = new Set(
        (examNotice.exam_subjects ?? []).map((es) => es.subject_id),
    );
    const options = availableSubjects.filter(
        (subject) => !usedSubjectIds.has(subject.id),
    );

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>
                <Button variant="outline">
                    <Plus className="size-4" />
                    Adicionar disciplina
                </Button>
            </DialogTrigger>
            <DialogContent>
                <DialogTitle>Adicionar disciplina</DialogTitle>

                <Form
                    {...ExamSubjectController.store.form(examNotice.id)}
                    options={{ preserveScroll: true }}
                    onSuccess={() => setOpen(false)}
                    className="space-y-4"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="subject_id">Disciplina</Label>
                                <Select name="subject_id">
                                    <SelectTrigger
                                        id="subject_id"
                                        className="w-full"
                                    >
                                        <SelectValue placeholder="Selecione a disciplina" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {options.map((subject) => (
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

                            <DialogFooter className="gap-2">
                                <DialogClose asChild>
                                    <Button variant="secondary">
                                        Cancelar
                                    </Button>
                                </DialogClose>
                                <Button type="submit" disabled={processing}>
                                    Adicionar
                                </Button>
                            </DialogFooter>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}

function AddTopicDialog({
    examSubjectId,
    catalogSubject,
    usedTopicIds,
}: {
    examSubjectId: number;
    catalogSubject: Subject | undefined;
    usedTopicIds: Set<number>;
}) {
    const [open, setOpen] = useState(false);
    const options = (catalogSubject?.topics ?? []).filter(
        (topic) => !usedTopicIds.has(topic.id),
    );

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>
                <Button variant="ghost" size="sm">
                    <Plus className="size-4" />
                    Tópico
                </Button>
            </DialogTrigger>
            <DialogContent>
                <DialogTitle>Adicionar tópico</DialogTitle>

                <Form
                    {...ExamTopicController.store.form(examSubjectId)}
                    options={{ preserveScroll: true }}
                    onSuccess={() => setOpen(false)}
                    className="space-y-4"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor={`topic_id-${examSubjectId}`}>
                                    Tópico
                                </Label>
                                <Select name="topic_id">
                                    <SelectTrigger
                                        id={`topic_id-${examSubjectId}`}
                                        className="w-full"
                                    >
                                        <SelectValue placeholder="Selecione o tópico" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {options.map((topic) => (
                                            <SelectItem
                                                key={topic.id}
                                                value={String(topic.id)}
                                            >
                                                {topic.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.topic_id} />
                            </div>

                            <DialogFooter className="gap-2">
                                <DialogClose asChild>
                                    <Button variant="secondary">
                                        Cancelar
                                    </Button>
                                </DialogClose>
                                <Button type="submit" disabled={processing}>
                                    Adicionar
                                </Button>
                            </DialogFooter>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}

export default function AdminExamNoticeShow({
    examNotice,
    progress,
    availableSubjects,
}: {
    examNotice: ExamNotice;
    progress: ExamNoticeProgress;
    availableSubjects: Subject[];
}) {
    return (
        <>
            <Head title={examNotice.name} />

            <div className="space-y-8">
                <Heading
                    title={examNotice.name}
                    description={examNotice.exam_board}
                />

                <div className="grid gap-6 lg:grid-cols-2">
                    <Form
                        {...ExamNoticeController.update.form(examNotice.id)}
                        options={{ preserveScroll: true }}
                        className="space-y-4"
                    >
                        {({ processing, errors }) => (
                            <>
                                <div className="grid gap-2">
                                    <Label htmlFor="name">Nome</Label>
                                    <Input
                                        id="name"
                                        name="name"
                                        defaultValue={examNotice.name}
                                        required
                                    />
                                    <InputError message={errors.name} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="exam_board">Banca</Label>
                                    <Input
                                        id="exam_board"
                                        name="exam_board"
                                        defaultValue={examNotice.exam_board}
                                        required
                                    />
                                    <InputError message={errors.exam_board} />
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
                                            examNotice.exam_date?.slice(
                                                0,
                                                10,
                                            ) ?? ''
                                        }
                                    />
                                    <InputError message={errors.exam_date} />
                                </div>

                                <Button disabled={processing} type="submit">
                                    Salvar
                                </Button>
                            </>
                        )}
                    </Form>

                    <Card>
                        <CardHeader>
                            <CardTitle>Seu progresso</CardTitle>
                            <CardDescription>
                                Percentual do conteúdo deste edital já estudado
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-2">
                            <div className="flex items-baseline justify-between text-sm">
                                <span className="font-medium">
                                    {progress.studiedPercentage}% estudado
                                </span>
                                <span className="text-muted-foreground">
                                    {progress.remainingPercentage}% restante
                                </span>
                            </div>
                            <Progress value={progress.studiedPercentage} />
                            <p className="text-xs text-muted-foreground">
                                {progress.studiedTopics} de{' '}
                                {progress.totalTopics} tópicos estudados
                            </p>
                        </CardContent>
                    </Card>
                </div>

                <div>
                    <div className="mb-4 flex items-center justify-between">
                        <Heading
                            variant="small"
                            title="Disciplinas"
                            description="Disciplinas e tópicos cobrados neste edital."
                        />
                        <AddSubjectDialog
                            examNotice={examNotice}
                            availableSubjects={availableSubjects}
                        />
                    </div>

                    <div className="space-y-4">
                        {(examNotice.exam_subjects ?? []).map((examSubject) => {
                            const catalogSubject = availableSubjects.find(
                                (subject) =>
                                    subject.id === examSubject.subject_id,
                            );
                            const usedTopicIds = new Set(
                                (examSubject.exam_topics ?? []).map(
                                    (et) => et.topic_id,
                                ),
                            );

                            return (
                                <Card key={examSubject.id}>
                                    <CardHeader>
                                        <div className="flex items-center justify-between">
                                            <CardTitle className="text-base">
                                                {examSubject.subject?.name}
                                            </CardTitle>
                                            <div className="flex items-center gap-1">
                                                <AddTopicDialog
                                                    examSubjectId={
                                                        examSubject.id
                                                    }
                                                    catalogSubject={
                                                        catalogSubject
                                                    }
                                                    usedTopicIds={usedTopicIds}
                                                />
                                                <Form
                                                    {...ExamSubjectController.destroy.form(
                                                        examSubject.id,
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
                                                            title="Remover disciplina"
                                                            disabled={
                                                                processing
                                                            }
                                                        >
                                                            <Trash2 className="size-4" />
                                                        </Button>
                                                    )}
                                                </Form>
                                            </div>
                                        </div>
                                    </CardHeader>
                                    <CardContent>
                                        {(examSubject.exam_topics ?? [])
                                            .length === 0 ? (
                                            <p className="text-sm text-muted-foreground">
                                                Nenhum tópico adicionado.
                                            </p>
                                        ) : (
                                            <ul className="divide-y">
                                                {(
                                                    examSubject.exam_topics ??
                                                    []
                                                ).map((examTopic) => (
                                                    <li
                                                        key={examTopic.id}
                                                        className="flex items-center justify-between py-2 text-sm"
                                                    >
                                                        {examTopic.topic?.name}
                                                        <Form
                                                            {...ExamTopicController.destroy.form(
                                                                examTopic.id,
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
                                                                    title="Remover tópico"
                                                                    disabled={
                                                                        processing
                                                                    }
                                                                >
                                                                    <Trash2 className="size-4" />
                                                                </Button>
                                                            )}
                                                        </Form>
                                                    </li>
                                                ))}
                                            </ul>
                                        )}
                                    </CardContent>
                                </Card>
                            );
                        })}
                    </div>
                </div>
            </div>
        </>
    );
}

AdminExamNoticeShow.layout = {
    breadcrumbs: [
        {
            title: 'Administração',
            href: dashboard(),
        },
        {
            title: 'Editais',
            href: index(),
        },
    ],
};
