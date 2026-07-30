import { Form, Head, Link, usePage } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import { Plus } from 'lucide-react';
import { useState } from 'react';
import SubjectController from '@/actions/App/Http/Controllers/SubjectController';
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
import { index, show } from '@/routes/subjects';
import type { Auth, Subject } from '@/types';

type PageProps = {
    auth: Auth;
};

export default function SubjectsIndex({ subjects }: { subjects: Subject[] }) {
    const { auth } = usePage<PageProps>().props;
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
            header: 'Origem',
            cell: ({ row }) =>
                row.original.owner_id === null ? (
                    <Badge variant="secondary">Global</Badge>
                ) : row.original.owner_id === auth.user.id ? (
                    <Badge variant="outline">Minha disciplina</Badge>
                ) : (
                    '—'
                ),
        },
        {
            accessorKey: 'topics_count',
            header: 'Tópicos',
        },
    ];

    return (
        <>
            <Head title="Disciplinas" />

            <div className="flex items-center justify-between">
                <Heading
                    title="Disciplinas"
                    description="Disciplinas globais e as que você criou."
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
                            A disciplina ficará visível apenas para você.
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
                                        <Input id="name" name="name" required />
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
                    emptyDescription="Crie sua primeira disciplina para começar a organizar seus estudos."
                />
            </div>
        </>
    );
}

SubjectsIndex.layout = {
    breadcrumbs: [
        {
            title: 'Disciplinas',
            href: index(),
        },
    ],
};
