import { Form, Head, Link } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import { Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';
import ScheduleController from '@/actions/App/Http/Controllers/ScheduleController';
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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { index, show } from '@/routes/schedules';
import type { Schedule, ScheduleType } from '@/types';

const TYPE_LABELS: Record<ScheduleType, string> = {
    daily: 'Diário',
    weekly: 'Semanal',
    biweekly: 'Quinzenal',
    monthly: 'Mensal',
    quarterly: 'Trimestral',
};

export default function SchedulesIndex({
    schedules,
}: {
    schedules: Schedule[];
}) {
    const [createOpen, setCreateOpen] = useState(false);
    const [deletingSchedule, setDeletingSchedule] = useState<Schedule | null>(
        null,
    );

    const columns: ColumnDef<Schedule>[] = [
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
            accessorKey: 'type',
            header: 'Frequência',
            cell: ({ row }) => TYPE_LABELS[row.original.type],
        },
        {
            accessorKey: 'schedule_items_count',
            header: 'Itens',
        },
        {
            id: 'actions',
            header: '',
            cell: ({ row }) => (
                <div className="flex justify-end gap-1">
                    <Button
                        variant="ghost"
                        size="icon"
                        title="Excluir"
                        onClick={() => setDeletingSchedule(row.original)}
                    >
                        <Trash2 className="size-4" />
                    </Button>
                </div>
            ),
        },
    ];

    return (
        <>
            <Head title="Cronogramas" />

            <div className="flex items-center justify-between">
                <Heading
                    title="Cronogramas"
                    description="Seus cronogramas de estudo."
                />

                <Dialog open={createOpen} onOpenChange={setCreateOpen}>
                    <DialogTrigger asChild>
                        <Button>
                            <Plus className="size-4" />
                            Novo cronograma
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogTitle>Novo cronograma</DialogTitle>
                        <DialogDescription>
                            Defina o nome e a frequência do cronograma. Você
                            poderá adicionar itens em seguida.
                        </DialogDescription>

                        <Form
                            {...ScheduleController.store.form()}
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
                                        <Label htmlFor="type">Frequência</Label>
                                        <Select
                                            name="type"
                                            defaultValue="weekly"
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
                                                ).map(([value, label]) => (
                                                    <SelectItem
                                                        key={value}
                                                        value={value}
                                                    >
                                                        {label}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        <InputError message={errors.type} />
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
                    data={schedules}
                    emptyTitle="Nenhum cronograma"
                    emptyDescription="Crie seu primeiro cronograma de estudos."
                />
            </div>

            <Dialog
                open={deletingSchedule !== null}
                onOpenChange={(open) => !open && setDeletingSchedule(null)}
            >
                <DialogContent>
                    <DialogTitle>Excluir cronograma</DialogTitle>
                    <DialogDescription>
                        Tem certeza que deseja excluir "{deletingSchedule?.name}
                        "? Esta ação não pode ser desfeita.
                    </DialogDescription>

                    {deletingSchedule && (
                        <Form
                            {...ScheduleController.destroy.form(
                                deletingSchedule.id,
                            )}
                            options={{ preserveScroll: true }}
                            onSuccess={() => setDeletingSchedule(null)}
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

SchedulesIndex.layout = {
    breadcrumbs: [
        {
            title: 'Cronogramas',
            href: index(),
        },
    ],
};
