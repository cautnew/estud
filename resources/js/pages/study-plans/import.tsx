import { Form, Head } from '@inertiajs/react';
import { Check, Copy } from 'lucide-react';
import { useState } from 'react';
import StudyPlanImportController from '@/actions/App/Http/Controllers/StudyPlanImportController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { index } from '@/routes/study-plans';
import { create } from '@/routes/study-plans/import';

const PROMPT_TEMPLATE = `Crie um plano de estudos para o concurso [NOME DO CONCURSO].

Responda APENAS com um JSON no formato abaixo, sem nenhum texto antes ou depois:

{
  "name": "Nome do plano",
  "exam_board": "Nome do concurso/banca",
  "goal": "Objetivo do plano (opcional)",
  "deadline": "AAAA-MM-DD (opcional)",
  "exam_date": "AAAA-MM-DD (opcional)",
  "subjects": [
    {
      "name": "Nome da disciplina",
      "topics": ["Tópico 1", "Tópico 2"]
    }
  ]
}`;

type ImportPreview = {
    name: string;
    exam_board: string;
    subjectCount: number;
    topicCount: number;
};

export default function StudyPlansImport() {
    const [payload, setPayload] = useState('');
    const [preview, setPreview] = useState<ImportPreview | null>(null);
    const [previewError, setPreviewError] = useState<string | null>(null);
    const [copied, setCopied] = useState(false);

    const handleCopy = () => {
        navigator.clipboard.writeText(PROMPT_TEMPLATE);
        setCopied(true);
        setTimeout(() => setCopied(false), 2000);
    };

    const handlePreview = () => {
        setPreview(null);
        setPreviewError(null);

        let data: unknown;

        try {
            data = JSON.parse(payload);
        } catch {
            setPreviewError('O texto colado não é um JSON válido.');

            return;
        }

        if (typeof data !== 'object' || data === null) {
            setPreviewError('O JSON informado é inválido.');

            return;
        }

        const record = data as Record<string, unknown>;
        const subjects = Array.isArray(record.subjects)
            ? record.subjects
            : null;

        if (
            typeof record.name !== 'string' ||
            typeof record.exam_board !== 'string' ||
            !subjects
        ) {
            setPreviewError(
                'O JSON precisa conter ao menos "name", "exam_board" e "subjects".',
            );

            return;
        }

        const topicCount = subjects.reduce((total: number, subject) => {
            const topics =
                typeof subject === 'object' &&
                subject !== null &&
                Array.isArray((subject as Record<string, unknown>).topics)
                    ? ((subject as Record<string, unknown>).topics as unknown[])
                    : [];

            return total + topics.length;
        }, 0);

        setPreview({
            name: record.name,
            exam_board: record.exam_board,
            subjectCount: subjects.length,
            topicCount,
        });
    };

    return (
        <>
            <Head title="Importar plano com IA" />

            <Heading
                title="Importar plano com IA"
                description="Peça a um chat de IA para gerar seu plano de estudos e importe o resultado aqui."
            />

            <div className="mt-6 grid gap-6 lg:grid-cols-2">
                <Card>
                    <CardContent className="space-y-4">
                        <div className="flex items-center justify-between">
                            <Label>1. Copie este prompt e envie a uma IA</Label>
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                onClick={handleCopy}
                            >
                                {copied ? (
                                    <Check className="size-4" />
                                ) : (
                                    <Copy className="size-4" />
                                )}
                                Copiar
                            </Button>
                        </div>
                        <Textarea
                            readOnly
                            value={PROMPT_TEMPLATE}
                            rows={14}
                            className="font-mono text-xs"
                        />
                    </CardContent>
                </Card>

                <Card>
                    <CardContent>
                        <Form
                            {...StudyPlanImportController.store.form()}
                            options={{ preserveScroll: true }}
                            className="space-y-4"
                        >
                            {({ processing, errors }) => (
                                <>
                                    <div className="grid gap-2">
                                        <Label htmlFor="payload">
                                            2. Cole aqui o JSON gerado pela IA
                                        </Label>
                                        <Textarea
                                            id="payload"
                                            name="payload"
                                            rows={14}
                                            className="font-mono text-xs"
                                            value={payload}
                                            onChange={(event) => {
                                                setPayload(event.target.value);
                                                setPreview(null);
                                                setPreviewError(null);
                                            }}
                                            required
                                        />
                                        <InputError message={errors.data} />
                                    </div>

                                    <Button
                                        type="button"
                                        variant="outline"
                                        onClick={handlePreview}
                                    >
                                        Pré-visualizar
                                    </Button>

                                    {previewError && (
                                        <Alert variant="destructive">
                                            <AlertTitle>
                                                Não foi possível ler o JSON
                                            </AlertTitle>
                                            <AlertDescription>
                                                {previewError}
                                            </AlertDescription>
                                        </Alert>
                                    )}

                                    {preview && (
                                        <Alert>
                                            <AlertTitle>
                                                {preview.name}
                                            </AlertTitle>
                                            <AlertDescription>
                                                {preview.exam_board} ·{' '}
                                                {preview.subjectCount}{' '}
                                                disciplina(s) ·{' '}
                                                {preview.topicCount} tópico(s)
                                            </AlertDescription>
                                        </Alert>
                                    )}

                                    <Button
                                        type="submit"
                                        disabled={processing || !preview}
                                    >
                                        Importar plano
                                    </Button>
                                </>
                            )}
                        </Form>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}

StudyPlansImport.layout = {
    breadcrumbs: [
        {
            title: 'Planos de Estudo',
            href: index(),
        },
        {
            title: 'Importar com IA',
            href: create(),
        },
    ],
};
