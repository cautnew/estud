import { Form, Head, Link } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import { Copy, FileUp, Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';
import ExamNoticeController from '@/actions/App/Http/Controllers/Admin/ExamNoticeController';
import DataTable from '@/components/data-table';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
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
import { formatDate } from '@/lib/date';
import { dashboard } from '@/routes/admin';
import { index as examNoticesIndex, show } from '@/routes/admin/exam-notices';
import { create as importCreate } from '@/routes/admin/exam-notices/import';
import type { ExamNotice } from '@/types';

export default function AdminExamNoticesIndex({
    examNotices,
}: {
    examNotices: ExamNotice[];
}) {
    const [createOpen, setCreateOpen] = useState(false);
    const [deletingNotice, setDeletingNotice] = useState<ExamNotice | null>(
        null,
    );

    const columns: ColumnDef<ExamNotice>[] = [
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
            accessorKey: 'exam_board',
            header: 'Banca',
        },
        {
            id: 'exam_date',
            header: 'Data da prova',
            cell: ({ row }) =>
                row.original.exam_date
                    ? formatDate(row.original.exam_date)
                    : '—',
        },
        {
            accessorKey: 'exam_subjects_count',
            header: 'Disciplinas',
        },
        {
            id: 'owner',
            header: 'Cadastrado por',
            cell: ({ row }) => row.original.owner?.name ?? '—',
        },
        {
            id: 'actions',
            header: '',
            cell: ({ row }) => {
                const notice = row.original;

                return (
                    <div className="flex justify-end gap-1">
                        <Form
                            {...ExamNoticeController.duplicate.form(notice.id)}
                            options={{ preserveScroll: true }}
                        >
                            {({ processing }) => (
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    type="submit"
                                    title="Clonar"
                                    disabled={processing}
                                >
                                    <Copy className="size-4" />
                                </Button>
                            )}
                        </Form>

                        <Button
                            variant="ghost"
                            size="icon"
                            title="Excluir"
                            onClick={() => setDeletingNotice(notice)}
                        >
                            <Trash2 className="size-4" />
                        </Button>
                    </div>
                );
            },
        },
    ];

    return (
        <>
            <Head title="Editais" />

            <div className="flex items-center justify-between">
                <Heading
                    title="Editais"
                    description="Cadastre e gerencie os editais de concursos disponíveis."
                />

                <div className="flex gap-2">
                    <Button variant="outline" asChild>
                        <Link href={importCreate()}>
                            <FileUp className="size-4" />
                            Importar com IA
                        </Link>
                    </Button>

                    <Dialog open={createOpen} onOpenChange={setCreateOpen}>
                        <DialogTrigger asChild>
                            <Button>
                                <Plus className="size-4" />
                                Novo edital
                            </Button>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogTitle>Novo edital</DialogTitle>
                            <DialogDescription>
                                Você poderá adicionar disciplinas e tópicos em
                                seguida.
                            </DialogDescription>

                            <Form
                                {...ExamNoticeController.store.form()}
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
                                                placeholder="Concurso TRF 2026"
                                            />
                                            <InputError message={errors.name} />
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor="exam_board">
                                                Banca
                                            </Label>
                                            <Input
                                                id="exam_board"
                                                name="exam_board"
                                                required
                                            />
                                            <InputError
                                                message={errors.exam_board}
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
                                            />
                                            <InputError
                                                message={errors.exam_date}
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
                                                Cadastrar
                                            </Button>
                                        </DialogFooter>
                                    </>
                                )}
                            </Form>
                        </DialogContent>
                    </Dialog>
                </div>
            </div>

            <div className="mt-6">
                <DataTable
                    columns={columns}
                    data={examNotices}
                    emptyTitle="Nenhum edital"
                    emptyDescription="Cadastre o primeiro edital para começar."
                />
            </div>

            <Dialog
                open={deletingNotice !== null}
                onOpenChange={(open) => !open && setDeletingNotice(null)}
            >
                <DialogContent>
                    <DialogTitle>Excluir edital?</DialogTitle>
                    <DialogDescription>
                        Tem certeza de que deseja excluir o edital &quot;
                        {deletingNotice?.name}&quot;? Esta ação não pode ser
                        desfeita.
                    </DialogDescription>

                    {deletingNotice && (
                        <Form
                            {...ExamNoticeController.destroy.form(
                                deletingNotice.id,
                            )}
                            options={{ preserveScroll: true }}
                            onSuccess={() => setDeletingNotice(null)}
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

AdminExamNoticesIndex.layout = {
    breadcrumbs: [
        {
            title: 'Administração',
            href: dashboard(),
        },
        {
            title: 'Editais',
            href: examNoticesIndex(),
        },
    ],
};
