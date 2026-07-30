import { Form, Head } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import { Pencil, Plus } from 'lucide-react';
import { useState } from 'react';
import SubjectController from '@/actions/App/Http/Controllers/SubjectController';
import TopicController from '@/actions/App/Http/Controllers/TopicController';
import DataTable from '@/components/data-table';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
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
import { Textarea } from '@/components/ui/textarea';
import { index } from '@/routes/subjects';
import type { Subject, Topic } from '@/types';

export default function SubjectShow({
    subject,
    canManage,
}: {
    subject: Subject;
    canManage: boolean;
}) {
    const [createTopicOpen, setCreateTopicOpen] = useState(false);
    const [editingTopic, setEditingTopic] = useState<Topic | null>(null);

    const columns: ColumnDef<Topic>[] = [
        {
            accessorKey: 'name',
            header: 'Nome',
        },
        {
            accessorKey: 'description',
            header: 'Descrição',
            cell: ({ row }) => row.original.description ?? '—',
        },
        ...(canManage
            ? [
                  {
                      id: 'actions',
                      header: '',
                      cell: ({ row }: { row: { original: Topic } }) => (
                          <div className="flex justify-end">
                              <Button
                                  variant="ghost"
                                  size="icon"
                                  onClick={() => setEditingTopic(row.original)}
                              >
                                  <Pencil className="size-4" />
                              </Button>
                          </div>
                      ),
                  },
              ]
            : []),
    ];

    return (
        <>
            <Head title={subject.name} />

            <div className="space-y-8">
                <div className="flex items-center gap-3">
                    <Heading title={subject.name} />
                    {subject.owner_id === null && (
                        <Badge variant="secondary">Global</Badge>
                    )}
                </div>

                {subject.description && (
                    <p className="max-w-lg text-sm text-muted-foreground">
                        {subject.description}
                    </p>
                )}

                {canManage && (
                    <Form
                        {...SubjectController.update.form(subject.id)}
                        options={{ preserveScroll: true }}
                        className="max-w-lg space-y-4"
                    >
                        {({ processing, errors }) => (
                            <>
                                <div className="grid gap-2">
                                    <Label htmlFor="name">Nome</Label>
                                    <Input
                                        id="name"
                                        name="name"
                                        defaultValue={subject.name}
                                        required
                                    />
                                    <InputError message={errors.name} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="description">
                                        Descrição
                                    </Label>
                                    <Textarea
                                        id="description"
                                        name="description"
                                        defaultValue={subject.description ?? ''}
                                    />
                                    <InputError message={errors.description} />
                                </div>

                                <Button disabled={processing} type="submit">
                                    Salvar
                                </Button>
                            </>
                        )}
                    </Form>
                )}

                <div>
                    <div className="mb-4 flex items-center justify-between">
                        <Heading
                            variant="small"
                            title="Tópicos"
                            description="Tópicos que compõem esta disciplina."
                        />

                        {canManage && (
                            <Dialog
                                open={createTopicOpen}
                                onOpenChange={setCreateTopicOpen}
                            >
                                <DialogTrigger asChild>
                                    <Button variant="outline">
                                        <Plus className="size-4" />
                                        Novo tópico
                                    </Button>
                                </DialogTrigger>
                                <DialogContent>
                                    <DialogTitle>Novo tópico</DialogTitle>

                                    <Form
                                        {...TopicController.store.form(
                                            subject.id,
                                        )}
                                        options={{ preserveScroll: true }}
                                        onSuccess={() =>
                                            setCreateTopicOpen(false)
                                        }
                                        resetOnSuccess
                                        className="space-y-4"
                                    >
                                        {({ processing, errors }) => (
                                            <>
                                                <div className="grid gap-2">
                                                    <Label htmlFor="topic-name">
                                                        Nome
                                                    </Label>
                                                    <Input
                                                        id="topic-name"
                                                        name="name"
                                                        required
                                                    />
                                                    <InputError
                                                        message={errors.name}
                                                    />
                                                </div>

                                                <div className="grid gap-2">
                                                    <Label htmlFor="topic-description">
                                                        Descrição
                                                    </Label>
                                                    <Textarea
                                                        id="topic-description"
                                                        name="description"
                                                    />
                                                    <InputError
                                                        message={
                                                            errors.description
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
                                                        Criar
                                                    </Button>
                                                </DialogFooter>
                                            </>
                                        )}
                                    </Form>
                                </DialogContent>
                            </Dialog>
                        )}
                    </div>

                    <DataTable
                        columns={columns}
                        data={subject.topics ?? []}
                        emptyTitle="Nenhum tópico"
                        emptyDescription="Ainda não há tópicos cadastrados nesta disciplina."
                    />
                </div>
            </div>

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
                                {...TopicController.update.form(
                                    editingTopic.id,
                                )}
                                options={{ preserveScroll: true }}
                                onSuccess={() => setEditingTopic(null)}
                                className="space-y-4"
                            >
                                {({ processing, errors }) => (
                                    <>
                                        <div className="grid gap-2">
                                            <Label htmlFor="edit-topic-name">
                                                Nome
                                            </Label>
                                            <Input
                                                id="edit-topic-name"
                                                name="name"
                                                defaultValue={editingTopic.name}
                                                required
                                            />
                                            <InputError message={errors.name} />
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor="edit-topic-description">
                                                Descrição
                                            </Label>
                                            <Textarea
                                                id="edit-topic-description"
                                                name="description"
                                                defaultValue={
                                                    editingTopic.description ??
                                                    ''
                                                }
                                            />
                                            <InputError
                                                message={errors.description}
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

SubjectShow.layout = {
    breadcrumbs: [
        {
            title: 'Disciplinas',
            href: index(),
        },
    ],
};
