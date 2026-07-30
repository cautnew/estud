import { Form, Head, Link } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import { Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';
import SubjectController from '@/actions/App/Http/Controllers/Admin/SubjectController';
import DataTable from '@/components/data-table';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
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
import { Textarea } from '@/components/ui/textarea';
import { dashboard } from '@/routes/admin';
import { index as subjectsIndex, show } from '@/routes/admin/subjects';
import type { Subject } from '@/types';

type PageProps = {
    subjects: Subject[];
};

export default function AdminSubjectsIndex({
    subjects,
}: {
    subjects: PageProps['subjects'];
}) {
    const [createOpen, setCreateOpen] = useState(false);

    const columns: ColumnDef<Subject>[] = [
        {
            accessorKey: 'name',
            header: 'Nome',
            cell: ({ row }) => (
                <Link
                    href={show(row.original.id)}
                    className="font-medium hover:underline"
                >
                    {row.original.name}
                </Link>
            ),
        },
        {
            id: 'owner',
            header: 'Dono',
            cell: ({ row }) =>
                row.original.owner_id === null ? (
                    <Badge variant="secondary">Global</Badge>
                ) : (
                    (row.original.owner?.name ?? '—')
                ),
        },
        {
            accessorKey: 'topics_count',
            header: 'Tópicos',
        },
        {
            id: 'actions',
            header: '',
            cell: ({ row }) => {
                const subject = row.original;
                const deletable =
                    subject.owner_id !== null &&
                    (subject.topics_count ?? 0) === 0;

                return (
                    <Dialog>
                        <DialogTrigger asChild>
                            <Button
                                variant="ghost"
                                size="icon"
                                disabled={!deletable}
                                title={
                                    subject.owner_id === null
                                        ? 'Disciplinas globais não podem ser excluídas'
                                        : !deletable
                                          ? 'Remova os tópicos antes de excluir'
                                          : 'Excluir disciplina'
                                }
                            >
                                <Trash2 className="size-4" />
                            </Button>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogTitle>Excluir disciplina?</DialogTitle>
                            <DialogDescription>
                                Tem certeza de que deseja excluir a disciplina
                                &quot;{subject.name}&quot;? Esta ação não pode
                                ser desfeita.
                            </DialogDescription>

                            <Form
                                {...SubjectController.destroy.form(subject.id)}
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
                                            asChild
                                        >
                                            <button type="submit">
                                                Excluir
                                            </button>
                                        </Button>
                                    </DialogFooter>
                                )}
                            </Form>
                        </DialogContent>
                    </Dialog>
                );
            },
        },
    ];

    return (
        <>
            <Head title="Disciplinas" />

            <div className="flex items-center justify-between">
                <Heading
                    title="Disciplinas"
                    description="Gerencie as disciplinas globais e as criadas por usuários."
                />

                <Dialog open={createOpen} onOpenChange={setCreateOpen}>
                    <DialogTrigger asChild>
                        <Button>
                            <Plus className="size-4" />
                            Nova disciplina
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogTitle>Nova disciplina</DialogTitle>
                        <DialogDescription>
                            Disciplinas criadas por aqui ficam associadas à sua
                            conta de administrador.
                        </DialogDescription>

                        <Form
                            {...SubjectController.store.form()}
                            options={{ preserveScroll: true }}
                            onSuccess={() => setCreateOpen(false)}
                            resetOnSuccess
                            className="space-y-4"
                        >
                            {({ processing, errors }) => (
                                <>
                                    <div className="grid gap-2">
                                        <Label htmlFor="name">Nome</Label>
                                        <Input
                                            id="name"
                                            name="name"
                                            required
                                            placeholder="Direito Constitucional"
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
                                            Criar
                                        </Button>
                                    </DialogFooter>
                                </>
                            )}
                        </Form>
                    </DialogContent>
                </Dialog>
            </div>

            <div className="mt-6">
                <DataTable
                    columns={columns}
                    data={subjects}
                    emptyTitle="Nenhuma disciplina"
                    emptyDescription="Crie a primeira disciplina para começar."
                />
            </div>
        </>
    );
}

AdminSubjectsIndex.layout = {
    breadcrumbs: [
        {
            title: 'Administração',
            href: dashboard(),
        },
        {
            title: 'Disciplinas',
            href: subjectsIndex(),
        },
    ],
};
