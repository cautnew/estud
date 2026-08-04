import { Form, useHttp } from '@inertiajs/react';
import { Pause, Play, Square } from 'lucide-react';
import { useEffect, useState } from 'react';
import StudySessionController from '@/actions/App/Http/Controllers/StudySessionController';
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
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    elapsedSeconds,
    formatDuration,
    STATUS_LABELS,
    STATUS_VARIANTS,
} from '@/lib/study-session';
import type { StudySession } from '@/types';

const AUTO_SAVE_INTERVAL_MS = 30_000;

export default function StudySessionStopwatchCard({
    session,
}: {
    session: StudySession;
}) {
    const [, forceTick] = useState(0);
    const [completeOpen, setCompleteOpen] = useState(false);
    const sync = useHttp();
    const seconds = elapsedSeconds(session);

    useEffect(() => {
        if (session.status !== 'in_progress') {
            return;
        }

        const tick = setInterval(() => forceTick((value) => value + 1), 1000);

        return () => clearInterval(tick);
    }, [session.status]);

    useEffect(() => {
        if (session.status !== 'in_progress') {
            return;
        }

        const autoSave = setInterval(() => {
            sync.patch(StudySessionController.sync.url(session.id));
        }, AUTO_SAVE_INTERVAL_MS);

        return () => clearInterval(autoSave);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [session.id, session.status]);

    return (
        <Card>
            <CardHeader>
                <CardTitle className="flex items-center justify-between">
                    <span>
                        {session.subject?.name}
                        {session.topic ? ` · ${session.topic.name}` : ''}
                    </span>
                    <Badge variant={STATUS_VARIANTS[session.status]}>
                        {STATUS_LABELS[session.status]}
                    </Badge>
                </CardTitle>
            </CardHeader>
            <CardContent className="flex flex-col gap-4">
                <div className="font-mono text-4xl tabular-nums">
                    {formatDuration(seconds)}
                </div>

                <div className="flex gap-2">
                    {session.status === 'in_progress' && (
                        <Form
                            {...StudySessionController.pause.form(session.id)}
                            options={{ preserveScroll: true }}
                        >
                            {({ processing }) => (
                                <Button
                                    type="submit"
                                    variant="secondary"
                                    disabled={processing}
                                >
                                    <Pause className="size-4" />
                                    Pausar
                                </Button>
                            )}
                        </Form>
                    )}

                    {session.status === 'paused' && (
                        <Form
                            {...StudySessionController.resume.form(session.id)}
                            options={{ preserveScroll: true }}
                        >
                            {({ processing }) => (
                                <Button
                                    type="submit"
                                    variant="secondary"
                                    disabled={processing}
                                >
                                    <Play className="size-4" />
                                    Continuar
                                </Button>
                            )}
                        </Form>
                    )}

                    <Dialog open={completeOpen} onOpenChange={setCompleteOpen}>
                        <DialogTrigger asChild>
                            <Button type="button">
                                <Square className="size-4" />
                                Concluir
                            </Button>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogTitle>Concluir sessão</DialogTitle>
                            <DialogDescription>
                                Registre uma observação sobre o que foi
                                estudado, se quiser.
                            </DialogDescription>

                            <Form
                                {...StudySessionController.complete.form(
                                    session.id,
                                )}
                                options={{ preserveScroll: true }}
                                onSuccess={() => setCompleteOpen(false)}
                                className="space-y-4"
                            >
                                {({ processing, errors }) => (
                                    <>
                                        <div className="grid gap-2">
                                            <Label htmlFor="note">
                                                Observação
                                            </Label>
                                            <Textarea id="note" name="note" />
                                            <InputError message={errors.note} />
                                        </div>

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
                                                Concluir
                                            </Button>
                                        </DialogFooter>
                                    </>
                                )}
                            </Form>
                        </DialogContent>
                    </Dialog>
                </div>
            </CardContent>
        </Card>
    );
}
