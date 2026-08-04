import { Form, Head } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { useState } from 'react';
import GoalController from '@/actions/App/Http/Controllers/GoalController';
import GoalCard from '@/components/goals/goal-card';
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
import { GOAL_PERIOD_LABELS, GOAL_TYPE_LABELS } from '@/lib/goal';
import { index } from '@/routes/goals';
import type { Goal, GoalPeriod, GoalType, Subject } from '@/types';

function GoalFormFields({
    subjects,
    defaults,
    errors,
}: {
    subjects: Subject[];
    defaults?: Goal;
    errors: Partial<
        Record<'type' | 'period' | 'target' | 'subject_id', string>
    >;
}) {
    return (
        <>
            <div className="grid gap-2">
                <Label htmlFor="type">Tipo</Label>
                <Select name="type" defaultValue={defaults?.type ?? 'hours'}>
                    <SelectTrigger id="type" className="w-full">
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        {(
                            Object.entries(GOAL_TYPE_LABELS) as [
                                GoalType,
                                string,
                            ][]
                        ).map(([value, label]) => (
                            <SelectItem key={value} value={value}>
                                {label}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                <InputError message={errors.type} />
            </div>

            <div className="grid gap-2">
                <Label htmlFor="period">Período</Label>
                <Select
                    name="period"
                    defaultValue={defaults?.period ?? 'weekly'}
                >
                    <SelectTrigger id="period" className="w-full">
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        {(
                            Object.entries(GOAL_PERIOD_LABELS) as [
                                GoalPeriod,
                                string,
                            ][]
                        ).map(([value, label]) => (
                            <SelectItem key={value} value={value}>
                                {label}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                <InputError message={errors.period} />
            </div>

            <div className="grid gap-2">
                <Label htmlFor="target">Meta</Label>
                <Input
                    id="target"
                    name="target"
                    type="number"
                    step="0.5"
                    min="0.5"
                    defaultValue={defaults?.target}
                    required
                />
                <InputError message={errors.target} />
            </div>

            <div className="grid gap-2">
                <Label htmlFor="subject_id">Disciplina (opcional)</Label>
                <Select
                    name="subject_id"
                    defaultValue={
                        defaults?.subject_id
                            ? String(defaults.subject_id)
                            : undefined
                    }
                >
                    <SelectTrigger id="subject_id" className="w-full">
                        <SelectValue placeholder="Todas as disciplinas" />
                    </SelectTrigger>
                    <SelectContent>
                        {subjects.map((subject) => (
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
        </>
    );
}

export default function GoalsIndex({
    goals,
    availableSubjects,
}: {
    goals: Goal[];
    availableSubjects: Subject[];
}) {
    const [createOpen, setCreateOpen] = useState(false);
    const [editingGoal, setEditingGoal] = useState<Goal | null>(null);
    const [deletingGoal, setDeletingGoal] = useState<Goal | null>(null);

    return (
        <>
            <Head title="Metas" />

            <div className="flex items-center justify-between">
                <Heading
                    title="Metas"
                    description="Defina metas e acompanhe seu progresso automaticamente."
                />

                <Dialog open={createOpen} onOpenChange={setCreateOpen}>
                    <DialogTrigger asChild>
                        <Button>
                            <Plus className="size-4" />
                            Nova meta
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogTitle>Nova meta</DialogTitle>
                        <DialogDescription>
                            O progresso é calculado automaticamente a partir dos
                            seus registros.
                        </DialogDescription>

                        <Form
                            {...GoalController.store.form()}
                            options={{ preserveScroll: true }}
                            onSuccess={() => setCreateOpen(false)}
                            resetOnSuccess
                            className="space-y-4"
                        >
                            {({ processing, errors }) => (
                                <>
                                    <GoalFormFields
                                        subjects={availableSubjects}
                                        errors={errors}
                                    />

                                    <DialogFooter className="gap-2">
                                        <DialogClose asChild>
                                            <Button variant="secondary">
                                                Cancelar
                                            </Button>
                                        </DialogClose>
                                        <Button
                                            type="submit"
                                            disabled={processing}
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
                {goals.length === 0 ? (
                    <EmptyState>
                        <EmptyStateTitle>Nenhuma meta criada</EmptyStateTitle>
                        <EmptyStateDescription>
                            Crie sua primeira meta para acompanhar seu
                            progresso.
                        </EmptyStateDescription>
                    </EmptyState>
                ) : (
                    <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        {goals.map((goal) => (
                            <GoalCard
                                key={goal.id}
                                goal={goal}
                                onEdit={() => setEditingGoal(goal)}
                                onDelete={() => setDeletingGoal(goal)}
                            />
                        ))}
                    </div>
                )}
            </div>

            <Dialog
                open={editingGoal !== null}
                onOpenChange={(open) => !open && setEditingGoal(null)}
            >
                <DialogContent>
                    <DialogTitle>Editar meta</DialogTitle>
                    <DialogDescription>
                        Ajustar o alvo recalcula o progresso do período atual.
                    </DialogDescription>

                    {editingGoal && (
                        <Form
                            {...GoalController.update.form(editingGoal.id)}
                            options={{ preserveScroll: true }}
                            onSuccess={() => setEditingGoal(null)}
                            className="space-y-4"
                        >
                            {({ processing, errors }) => (
                                <>
                                    <GoalFormFields
                                        subjects={availableSubjects}
                                        defaults={editingGoal}
                                        errors={errors}
                                    />

                                    <DialogFooter className="gap-2">
                                        <DialogClose asChild>
                                            <Button variant="secondary">
                                                Cancelar
                                            </Button>
                                        </DialogClose>
                                        <Button
                                            type="submit"
                                            disabled={processing}
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

            <Dialog
                open={deletingGoal !== null}
                onOpenChange={(open) => !open && setDeletingGoal(null)}
            >
                <DialogContent>
                    <DialogTitle>Excluir meta</DialogTitle>
                    <DialogDescription>
                        Tem certeza que deseja excluir esta meta? Todo o
                        histórico de progresso será perdido.
                    </DialogDescription>

                    {deletingGoal && (
                        <Form
                            {...GoalController.destroy.form(deletingGoal.id)}
                            options={{ preserveScroll: true }}
                            onSuccess={() => setDeletingGoal(null)}
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

GoalsIndex.layout = {
    breadcrumbs: [
        {
            title: 'Metas',
            href: index(),
        },
    ],
};
