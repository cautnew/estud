import { Form, Head, Link } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import { Copy } from 'lucide-react';
import StudyPlanController from '@/actions/App/Http/Controllers/StudyPlanController';
import DataTable from '@/components/data-table';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { index, library, show } from '@/routes/study-plans';
import type { StudyPlan } from '@/types';

export default function StudyPlansLibrary({
    studyPlans,
}: {
    studyPlans: StudyPlan[];
}) {
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
            id: 'owner',
            header: 'Criado por',
            cell: ({ row }) => row.original.owner?.name ?? '—',
        },
        {
            accessorKey: 'study_plan_subjects_count',
            header: 'Disciplinas',
        },
        {
            id: 'actions',
            header: '',
            cell: ({ row }) => (
                <div className="flex justify-end">
                    <Form
                        {...StudyPlanController.duplicate.form(row.original.id)}
                        options={{ preserveScroll: true }}
                    >
                        {({ processing }) => (
                            <Button
                                variant="outline"
                                size="sm"
                                type="submit"
                                disabled={processing}
                            >
                                <Copy className="size-4" />
                                Clonar
                            </Button>
                        )}
                    </Form>
                </div>
            ),
        },
    ];

    return (
        <>
            <Head title="Biblioteca pública" />

            <Heading
                title="Biblioteca pública"
                description="Planos de estudo publicados por outros usuários. Clone um plano para criar sua própria cópia."
            />

            <div className="mt-6">
                <DataTable
                    columns={columns}
                    data={studyPlans}
                    emptyTitle="Nenhum plano público"
                    emptyDescription="Ainda não há planos de estudo publicados na biblioteca."
                />
            </div>
        </>
    );
}

StudyPlansLibrary.layout = {
    breadcrumbs: [
        {
            title: 'Planos de Estudo',
            href: index(),
        },
        {
            title: 'Biblioteca pública',
            href: library(),
        },
    ],
};
