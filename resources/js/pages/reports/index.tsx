import { Head, router } from '@inertiajs/react';
import { FileSpreadsheet, FileText } from 'lucide-react';
import { useState } from 'react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    EmptyState,
    EmptyStateDescription,
    EmptyStateTitle,
} from '@/components/ui/empty-state';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
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
import { index } from '@/routes/reports';
import {
    excel as exportExcel,
    pdf as exportPdf,
} from '@/routes/reports/export';
import type {
    Report,
    ReportFilters,
    ReportType,
    ReportTypeOption,
    Subject,
} from '@/types';

const ALL_SUBJECTS = 'all';
const ALL_PLANS = 'all';

type StudyPlanOption = { id: number; name: string };

export default function ReportsIndex({
    report,
    reportTypes,
    selectedType,
    filters,
    availableSubjects,
    availableStudyPlans,
}: {
    report: Report;
    reportTypes: ReportTypeOption[];
    selectedType: ReportType;
    filters: ReportFilters;
    availableSubjects: Subject[];
    availableStudyPlans: StudyPlanOption[];
}) {
    const [dateFrom, setDateFrom] = useState(filters.date_from);
    const [dateTo, setDateTo] = useState(filters.date_to);
    const [subjectId, setSubjectId] = useState(
        filters.subject_id ? String(filters.subject_id) : ALL_SUBJECTS,
    );
    const [studyPlanId, setStudyPlanId] = useState(
        filters.study_plan_id ? String(filters.study_plan_id) : ALL_PLANS,
    );

    const currentQuery = {
        type: selectedType,
        date_from: dateFrom,
        date_to: dateTo,
        subject_id: subjectId === ALL_SUBJECTS ? undefined : subjectId,
        study_plan_id: studyPlanId === ALL_PLANS ? undefined : studyPlanId,
    };

    function navigate(overrides: Partial<typeof currentQuery> = {}) {
        router.get(
            index.url(),
            { ...currentQuery, ...overrides },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    }

    return (
        <>
            <Head title="Relatórios" />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <Heading
                    title="Relatórios"
                    description="Relatórios completos com filtros avançados e exportação em PDF ou Excel."
                />

                <div className="flex flex-wrap gap-2">
                    {reportTypes.map((type) => (
                        <Button
                            key={type.value}
                            variant={
                                type.value === selectedType
                                    ? 'default'
                                    : 'outline'
                            }
                            onClick={() => navigate({ type: type.value })}
                        >
                            {type.label}
                        </Button>
                    ))}
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Filtros avançados</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            <div className="grid gap-2">
                                <Label htmlFor="date_from">De</Label>
                                <Input
                                    id="date_from"
                                    type="date"
                                    value={dateFrom}
                                    onChange={(e) =>
                                        setDateFrom(e.target.value)
                                    }
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="date_to">Até</Label>
                                <Input
                                    id="date_to"
                                    type="date"
                                    value={dateTo}
                                    onChange={(e) => setDateTo(e.target.value)}
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="subject_id">Disciplina</Label>
                                <Select
                                    value={subjectId}
                                    onValueChange={setSubjectId}
                                >
                                    <SelectTrigger id="subject_id">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value={ALL_SUBJECTS}>
                                            Todas as disciplinas
                                        </SelectItem>
                                        {availableSubjects.map((subject) => (
                                            <SelectItem
                                                key={subject.id}
                                                value={String(subject.id)}
                                            >
                                                {subject.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="study_plan_id">
                                    Plano de estudos
                                </Label>
                                <Select
                                    value={studyPlanId}
                                    onValueChange={setStudyPlanId}
                                >
                                    <SelectTrigger id="study_plan_id">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value={ALL_PLANS}>
                                            Todos os planos
                                        </SelectItem>
                                        {availableStudyPlans.map((plan) => (
                                            <SelectItem
                                                key={plan.id}
                                                value={String(plan.id)}
                                            >
                                                {plan.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div className="mt-4 flex flex-wrap items-center gap-2">
                            <Button onClick={() => navigate()}>
                                Aplicar filtros
                            </Button>

                            <Button variant="outline" asChild>
                                <a
                                    href={exportPdf.url({
                                        query: currentQuery,
                                    })}
                                >
                                    <FileText className="size-4" />
                                    Exportar PDF
                                </a>
                            </Button>

                            <Button variant="outline" asChild>
                                <a
                                    href={exportExcel.url({
                                        query: currentQuery,
                                    })}
                                >
                                    <FileSpreadsheet className="size-4" />
                                    Exportar Excel
                                </a>
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>{report.title}</CardTitle>
                        <p className="text-sm text-muted-foreground">
                            {report.filters_description}
                        </p>
                    </CardHeader>
                    <CardContent className="space-y-6">
                        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            {report.summary.map((item) => (
                                <div
                                    key={item.label}
                                    className="rounded-lg border p-4"
                                >
                                    <div className="text-sm text-muted-foreground">
                                        {item.label}
                                    </div>
                                    <div className="text-2xl font-semibold">
                                        {item.value}
                                    </div>
                                </div>
                            ))}
                        </div>

                        {report.rows.length === 0 ? (
                            <EmptyState className="border-none p-4">
                                <EmptyStateTitle>
                                    Nenhum dado encontrado
                                </EmptyStateTitle>
                                <EmptyStateDescription>
                                    Ajuste os filtros para ver dados neste
                                    relatório.
                                </EmptyStateDescription>
                            </EmptyState>
                        ) : (
                            <div className="overflow-x-auto rounded-md border">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            {report.columns.map((column) => (
                                                <TableHead key={column}>
                                                    {column}
                                                </TableHead>
                                            ))}
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {report.rows.map((row, rowIndex) => (
                                            <TableRow key={rowIndex}>
                                                {row.map((cell, cellIndex) => (
                                                    <TableCell key={cellIndex}>
                                                        {cell}
                                                    </TableCell>
                                                ))}
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </>
    );
}

ReportsIndex.layout = {
    breadcrumbs: [
        {
            title: 'Relatórios',
            href: index(),
        },
    ],
};
