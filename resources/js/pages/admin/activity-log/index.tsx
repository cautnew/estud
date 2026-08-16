import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogTitle,
} from '@/components/ui/dialog';
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
import { dashboard } from '@/routes/admin';
import { index } from '@/routes/admin/activity-log';
import type {
    ActivityLogEntry,
    ActivityLogSubjectTypeOption,
    Paginated,
} from '@/types';

const ALL_EVENTS = 'all';
const ALL_SUBJECT_TYPES = 'all';
const ALL_CAUSERS = 'all';

type CauserOption = { id: number; name: string };

const EVENT_LABELS: Record<string, string> = {
    created: 'Criado',
    updated: 'Atualizado',
    deleted: 'Excluído',
};

function eventBadgeVariant(
    event: string,
): 'default' | 'secondary' | 'destructive' {
    if (event === 'created') {
        return 'default';
    }

    if (event === 'deleted') {
        return 'destructive';
    }

    return 'secondary';
}

export default function AdminActivityLogIndex({
    activities,
    filters,
    subjectTypes,
    causers,
}: {
    activities: Paginated<ActivityLogEntry>;
    filters: {
        event?: string;
        subject_type?: string;
        causer_id?: string;
        date_from?: string;
        date_to?: string;
    };
    subjectTypes: ActivityLogSubjectTypeOption[];
    causers: CauserOption[];
}) {
    const [event, setEvent] = useState(filters.event ?? ALL_EVENTS);
    const [subjectType, setSubjectType] = useState(
        filters.subject_type ?? ALL_SUBJECT_TYPES,
    );
    const [causerId, setCauserId] = useState(filters.causer_id ?? ALL_CAUSERS);
    const [dateFrom, setDateFrom] = useState(filters.date_from ?? '');
    const [dateTo, setDateTo] = useState(filters.date_to ?? '');
    const [inspecting, setInspecting] = useState<ActivityLogEntry | null>(null);

    function applyFilters() {
        router.get(
            index.url(),
            {
                event: event === ALL_EVENTS ? undefined : event,
                subject_type:
                    subjectType === ALL_SUBJECT_TYPES ? undefined : subjectType,
                causer_id: causerId === ALL_CAUSERS ? undefined : causerId,
                date_from: dateFrom || undefined,
                date_to: dateTo || undefined,
            },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    }

    return (
        <>
            <Head title="Log de atividades" />

            <div className="flex flex-1 flex-col gap-6 p-4">
                <Heading
                    title="Log de atividades"
                    description="Auditoria de criação, atualização e exclusão dos registros do sistema."
                />

                <Card>
                    <CardHeader>
                        <CardTitle>Filtros</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                            <div className="grid gap-2">
                                <Label htmlFor="event">Evento</Label>
                                <Select value={event} onValueChange={setEvent}>
                                    <SelectTrigger id="event">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value={ALL_EVENTS}>
                                            Todos os eventos
                                        </SelectItem>
                                        <SelectItem value="created">
                                            Criado
                                        </SelectItem>
                                        <SelectItem value="updated">
                                            Atualizado
                                        </SelectItem>
                                        <SelectItem value="deleted">
                                            Excluído
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="subject_type">Tipo</Label>
                                <Select
                                    value={subjectType}
                                    onValueChange={setSubjectType}
                                >
                                    <SelectTrigger id="subject_type">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value={ALL_SUBJECT_TYPES}>
                                            Todos os tipos
                                        </SelectItem>
                                        {subjectTypes.map((type) => (
                                            <SelectItem
                                                key={type.value}
                                                value={type.value}
                                            >
                                                {type.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="causer_id">Usuário</Label>
                                <Select
                                    value={causerId}
                                    onValueChange={setCauserId}
                                >
                                    <SelectTrigger id="causer_id">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value={ALL_CAUSERS}>
                                            Todos os usuários
                                        </SelectItem>
                                        {causers.map((causer) => (
                                            <SelectItem
                                                key={causer.id}
                                                value={String(causer.id)}
                                            >
                                                {causer.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>

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
                        </div>

                        <div className="mt-4">
                            <Button onClick={applyFilters}>
                                Aplicar filtros
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent className="space-y-4">
                        {activities.data.length === 0 ? (
                            <EmptyState className="border-none p-4">
                                <EmptyStateTitle>
                                    Nenhuma atividade encontrada
                                </EmptyStateTitle>
                                <EmptyStateDescription>
                                    Ajuste os filtros para ver o histórico de
                                    alterações.
                                </EmptyStateDescription>
                            </EmptyState>
                        ) : (
                            <>
                                <div className="overflow-x-auto rounded-md border">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>Data/Hora</TableHead>
                                                <TableHead>Evento</TableHead>
                                                <TableHead>Tipo</TableHead>
                                                <TableHead>Registro</TableHead>
                                                <TableHead>Usuário</TableHead>
                                                <TableHead />
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {activities.data.map((entry) => (
                                                <TableRow key={entry.id}>
                                                    <TableCell>
                                                        {entry.created_at
                                                            ? new Date(
                                                                  entry.created_at,
                                                              ).toLocaleString(
                                                                  'pt-BR',
                                                              )
                                                            : '—'}
                                                    </TableCell>
                                                    <TableCell>
                                                        <Badge
                                                            variant={eventBadgeVariant(
                                                                entry.event,
                                                            )}
                                                        >
                                                            {EVENT_LABELS[
                                                                entry.event
                                                            ] ?? entry.event}
                                                        </Badge>
                                                    </TableCell>
                                                    <TableCell>
                                                        {entry.subject_type ??
                                                            '—'}
                                                    </TableCell>
                                                    <TableCell>
                                                        #{entry.subject_id}
                                                    </TableCell>
                                                    <TableCell>
                                                        {entry.causer?.name ??
                                                            'Sistema'}
                                                    </TableCell>
                                                    <TableCell>
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            onClick={() =>
                                                                setInspecting(
                                                                    entry,
                                                                )
                                                            }
                                                        >
                                                            Ver alterações
                                                        </Button>
                                                    </TableCell>
                                                </TableRow>
                                            ))}
                                        </TableBody>
                                    </Table>
                                </div>

                                <div className="flex flex-wrap items-center justify-between gap-2 text-sm text-muted-foreground">
                                    <span>
                                        {activities.total} registro(s) · página{' '}
                                        {activities.current_page} de{' '}
                                        {activities.last_page}
                                    </span>

                                    <div className="flex flex-wrap gap-1">
                                        {activities.links.map(
                                            (link, linkIndex) => (
                                                <Button
                                                    key={linkIndex}
                                                    variant={
                                                        link.active
                                                            ? 'default'
                                                            : 'outline'
                                                    }
                                                    size="sm"
                                                    disabled={link.url === null}
                                                    onClick={() =>
                                                        link.url &&
                                                        router.get(
                                                            link.url,
                                                            {},
                                                            {
                                                                preserveState: true,
                                                                preserveScroll: true,
                                                            },
                                                        )
                                                    }
                                                    dangerouslySetInnerHTML={{
                                                        __html: link.label,
                                                    }}
                                                />
                                            ),
                                        )}
                                    </div>
                                </div>
                            </>
                        )}
                    </CardContent>
                </Card>
            </div>

            <Dialog
                open={inspecting !== null}
                onOpenChange={(open) => !open && setInspecting(null)}
            >
                <DialogContent>
                    <DialogTitle>Alterações</DialogTitle>
                    <DialogDescription>
                        {inspecting?.subject_type} #{inspecting?.subject_id}
                    </DialogDescription>

                    <pre className="max-h-96 overflow-auto rounded-md bg-muted p-4 text-xs">
                        {JSON.stringify(inspecting?.changes, null, 2)}
                    </pre>
                </DialogContent>
            </Dialog>
        </>
    );
}

AdminActivityLogIndex.layout = {
    breadcrumbs: [
        {
            title: 'Administração',
            href: dashboard(),
        },
        {
            title: 'Log de atividades',
            href: index(),
        },
    ],
};
