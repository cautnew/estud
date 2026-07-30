import { Form, Head, Link } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import { Copy, Library, Plus, Sparkles, Trash2 } from 'lucide-react';
import { useState } from 'react';
import StudyPlanController from '@/actions/App/Http/Controllers/StudyPlanController';
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
import { index, library, show } from '@/routes/study-plans';
import { create as createImport } from '@/routes/study-plans/import';
import type { StudyPlan } from '@/types';

export default function StudyPlansIndex({
    studyPlans,
}: {
    studyPlans: StudyPlan[];
}) {
    const [createOpen, setCreateOpen] = useState(false);
    const [deletingPlan, setDeletingPlan] = useState<StudyPlan | null>(null);

    const columns: ColumnDef<StudyPlan>[] = [
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
            header: 'Concurso',
        },
        {
            accessorKey: 'deadline',
            header: 'Prazo final',
            cell: ({ row }) => row.original.deadline ?? '—',
        },
        {
            accessorKey: 'study_plan_subjects_count',
            header: 'Disciplinas',
        },
        {
            id: 'is_public',
            header: 'Visibilidade',
            cell: ({ row }) =>
                row.original.is_public ? (
                    <Badge variant="outline">Pública</Badge>
                ) : (
                    <Badge variant="secondary">Privada</Badge>
                ),
        },
        {
            id: 'actions',
            header: '',
            cell: ({ row }) => (
                <div className="flex justify-end gap-1">
                    <Form
                        {...StudyPlanController.duplicate.form(row.original.id)}
                        options={{ preserveScroll: true }}
                    >
                        {({ processing }) => (
                            <Button
                                variant="ghost"
                                size="icon"
                                type="submit"
                                disabled={processing}
                                title="Duplicar"
                            >
                                <Copy className="size-4" />
                            </Button>
                        )}
                    </Form>

                    <Button
                        variant="ghost"
                        size="icon"
                        title="Excluir"
                        onClick={() => setDeletingPlan(row.original)}
                    >
                        <Trash2 className="size-4" />
                    </Button>
                </div>
            ),
        },
    ];

    return (
        <>
            <Head title="Planos de Estudo" />

            <div className="flex items-center justify-between">
                <Heading
                    title="Planos de Estudo"
                    description="Seus planos de estudo para concursos."
                />

                <div className="flex gap-2">
                    <Button variant="outline" asChild>
                        <Link href={library()}>
                            <Library className="size-4" />
                            Biblioteca pública
                        </Link>
                    </Button>

                    <Button variant="outline" asChild>
                        <Link href={createImport()}>
                            <Sparkles className="size-4" />
                            Importar com IA
                        </Link>
                    </Button>

                    <Dialog open={createOpen} onOpenChange={setCreateOpen}>
                        <DialogTrigger asChild>
                            <Button>
                                <Plus className="size-4" />
                                Novo plano
                            </Button>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogTitle>Novo plano de estudos</DialogTitle>
                            <DialogDescription>
                                Defina os dados básicos do seu plano. Você
                                poderá adicionar disciplinas e tópicos em
                                seguida.
                            </DialogDescription>

                            <Form
                                {...StudyPlanController.store.form()}
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
                                            />
                                            <InputError message={errors.name} />
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor="exam_board">
                                                Concurso
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
                                            <Label htmlFor="goal">
                                                Objetivo
                                            </Label>
                                            <Textarea id="goal" name="goal" />
                                            <InputError message={errors.goal} />
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
                                                />
                                                <InputError
                                                    message={errors.deadline}
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
            </div>

            <div className="mt-6">
                <DataTable
                    columns={columns}
                    data={studyPlans}
                    emptyTitle="Nenhum plano de estudos"
                    emptyDescription="Crie seu primeiro plano ou importe um da biblioteca pública."
                />
            </div>

            <Dialog
                open={deletingPlan !== null}
                onOpenChange={(open) => !open && setDeletingPlan(null)}
            >
                <DialogContent>
                    <DialogTitle>Excluir plano de estudos</DialogTitle>
                    <DialogDescription>
                        Tem certeza que deseja excluir "{deletingPlan?.name}"?
                        Esta ação não pode ser desfeita.
                    </DialogDescription>

                    {deletingPlan && (
                        <Form
                            {...StudyPlanController.destroy.form(
                                deletingPlan.id,
                            )}
                            options={{ preserveScroll: true }}
                            onSuccess={() => setDeletingPlan(null)}
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

StudyPlansIndex.layout = {
    breadcrumbs: [
        {
            title: 'Planos de Estudo',
            href: index(),
        },
    ],
};
