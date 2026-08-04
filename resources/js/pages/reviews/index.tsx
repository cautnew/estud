import { Form, Head, useForm } from '@inertiajs/react';
import { Check, Settings, X } from 'lucide-react';
import { useState } from 'react';
import type { FormEvent } from 'react';
import ReviewController from '@/actions/App/Http/Controllers/ReviewController';
import ReviewPreferenceController from '@/actions/App/Http/Controllers/ReviewPreferenceController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
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
import { formatDate, todayDateString } from '@/lib/date';
import { index } from '@/routes/reviews';
import type { Review } from '@/types';

function ReviewRow({ review }: { review: Review }) {
    const subject = review.study_plan_topic?.study_plan_subject?.subject;
    const topic = review.study_plan_topic?.topic;

    return (
        <div className="flex items-center justify-between gap-4 border-b py-3 last:border-b-0">
            <div>
                <div className="font-medium">{subject?.name}</div>
                {topic && (
                    <div className="text-sm text-muted-foreground">
                        {topic.name}
                    </div>
                )}
            </div>

            <div className="flex items-center gap-3">
                <Badge variant="outline">D+{review.interval_days}</Badge>
                <span className="text-sm text-muted-foreground">
                    {formatDate(review.scheduled_for)}
                </span>

                <Form
                    {...ReviewController.complete.form(review.id)}
                    options={{ preserveScroll: true }}
                >
                    {({ processing }) => (
                        <Button
                            type="submit"
                            size="icon"
                            variant="ghost"
                            title="Concluir"
                            disabled={processing}
                        >
                            <Check className="size-4" />
                        </Button>
                    )}
                </Form>

                <Form
                    {...ReviewController.skip.form(review.id)}
                    options={{ preserveScroll: true }}
                >
                    {({ processing }) => (
                        <Button
                            type="submit"
                            size="icon"
                            variant="ghost"
                            title="Pular"
                            disabled={processing}
                        >
                            <X className="size-4" />
                        </Button>
                    )}
                </Form>
            </div>
        </div>
    );
}

function ReviewSection({
    title,
    reviews,
}: {
    title: string;
    reviews: Review[];
}) {
    if (reviews.length === 0) {
        return null;
    }

    return (
        <Card>
            <CardHeader>
                <CardTitle>
                    {title} ({reviews.length})
                </CardTitle>
            </CardHeader>
            <CardContent>
                {reviews.map((review) => (
                    <ReviewRow key={review.id} review={review} />
                ))}
            </CardContent>
        </Card>
    );
}

function IntervalsDialog({ intervals }: { intervals: number[] }) {
    const [open, setOpen] = useState(false);
    const [text, setText] = useState(intervals.join(', '));
    const { data, setData, patch, processing, errors } = useForm({
        intervals,
    });

    function handleTextChange(value: string) {
        setText(value);
        setData(
            'intervals',
            value
                .split(',')
                .map((entry) => parseInt(entry.trim(), 10))
                .filter((entry) => !Number.isNaN(entry)),
        );
    }

    function submit(event: FormEvent) {
        event.preventDefault();
        patch(ReviewPreferenceController.update.url(), {
            preserveScroll: true,
            onSuccess: () => setOpen(false),
        });
    }

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>
                <Button variant="secondary">
                    <Settings className="size-4" />
                    Configurar intervalos
                </Button>
            </DialogTrigger>
            <DialogContent>
                <DialogTitle>Intervalos de revisão</DialogTitle>
                <DialogDescription>
                    Dias após o estudo em que uma revisão será agendada (ex: 1,
                    7, 15, 30, 60, 90). Vale apenas para tópicos marcados como
                    concluídos a partir de agora.
                </DialogDescription>

                <form onSubmit={submit} className="space-y-4">
                    <div className="grid gap-2">
                        <Label htmlFor="intervals">Dias (D+)</Label>
                        <Input
                            id="intervals"
                            value={text}
                            onChange={(event) =>
                                handleTextChange(event.target.value)
                            }
                            placeholder="1, 7, 15, 30, 60, 90"
                        />
                        <InputError message={errors.intervals} />
                    </div>

                    <DialogFooter className="gap-2">
                        <DialogClose asChild>
                            <Button type="button" variant="secondary">
                                Cancelar
                            </Button>
                        </DialogClose>
                        <Button
                            type="submit"
                            disabled={processing || data.intervals.length === 0}
                        >
                            Salvar
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

export default function ReviewsIndex({
    reviews,
    intervals,
}: {
    reviews: Review[];
    intervals: number[];
}) {
    const today = todayDateString();
    const overdue = reviews.filter((review) => review.scheduled_for < today);
    const dueToday = reviews.filter((review) => review.scheduled_for === today);
    const upcoming = reviews.filter((review) => review.scheduled_for > today);

    return (
        <>
            <Head title="Revisões" />

            <div className="flex items-center justify-between">
                <Heading
                    title="Revisões"
                    description="Sua agenda de revisões espaçadas."
                />
                <IntervalsDialog intervals={intervals} />
            </div>

            <div className="mt-6 space-y-6">
                {reviews.length === 0 ? (
                    <EmptyState>
                        <EmptyStateTitle>
                            Nenhuma revisão agendada
                        </EmptyStateTitle>
                        <EmptyStateDescription>
                            Marque um tópico como concluído em um plano de
                            estudos para agendar sua revisão automaticamente.
                        </EmptyStateDescription>
                    </EmptyState>
                ) : (
                    <>
                        <ReviewSection title="Atrasadas" reviews={overdue} />
                        <ReviewSection title="Hoje" reviews={dueToday} />
                        <ReviewSection title="Próximas" reviews={upcoming} />
                    </>
                )}
            </div>
        </>
    );
}

ReviewsIndex.layout = {
    breadcrumbs: [
        {
            title: 'Revisões',
            href: index(),
        },
    ],
};
