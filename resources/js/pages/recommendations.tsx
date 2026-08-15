import { Form, Head } from '@inertiajs/react';
import { AlertTriangle, BookOpen, Check, RotateCcw, X } from 'lucide-react';
import ReviewController from '@/actions/App/Http/Controllers/ReviewController';
import Heading from '@/components/heading';
import HealthScoreCard from '@/components/recommendations/health-score-card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    EmptyState,
    EmptyStateDescription,
    EmptyStateTitle,
} from '@/components/ui/empty-state';
import { formatDate } from '@/lib/date';
import { index as recommendationsIndex } from '@/routes/recommendations';
import type {
    HealthScore,
    ReviewRecommendation,
    StudyRecommendation,
    StudyRecommendationSource,
} from '@/types';

const SOURCE_LABELS: Record<StudyRecommendationSource, string> = {
    schedule: 'Cronograma',
    cycle: 'Ciclo de estudos',
    plan: 'Plano de estudos',
};

function StudyRecommendationRow({ item }: { item: StudyRecommendation }) {
    return (
        <div className="flex items-center justify-between gap-4 border-b py-3 last:border-b-0">
            <div>
                <div className="font-medium">{item.subject}</div>
                {item.topic && (
                    <div className="text-sm text-muted-foreground">
                        {item.topic}
                    </div>
                )}
            </div>

            <div className="flex items-center gap-3">
                <span className="text-sm text-muted-foreground">
                    {item.label}
                </span>
                <Badge variant="outline">{SOURCE_LABELS[item.source]}</Badge>
            </div>
        </div>
    );
}

function ReviewRecommendationRow({
    review,
    showDaysOverdue = false,
}: {
    review: ReviewRecommendation;
    showDaysOverdue?: boolean;
}) {
    return (
        <div className="flex items-center justify-between gap-4 border-b py-3 last:border-b-0">
            <div>
                <div className="font-medium">{review.subject}</div>
                <div className="text-sm text-muted-foreground">
                    {review.topic}
                </div>
            </div>

            <div className="flex items-center gap-3">
                <Badge variant="outline">D+{review.interval_days}</Badge>
                <span className="text-sm text-muted-foreground">
                    {showDaysOverdue
                        ? `${review.days_overdue} dia(s) atrasada`
                        : formatDate(review.scheduled_for)}
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

export default function Recommendations({
    healthScore,
    studyToday,
    reviewToday,
    overdue,
}: {
    healthScore: HealthScore;
    studyToday: StudyRecommendation[];
    reviewToday: ReviewRecommendation[];
    overdue: ReviewRecommendation[];
}) {
    return (
        <>
            <Head title="Recomendações" />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <Heading
                    title="Recomendações"
                    description="O motor de recomendações do dia, com base no seu cronograma, ciclo de estudos, metas e revisões."
                />

                <HealthScoreCard healthScore={healthScore} />

                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <BookOpen className="size-4" />
                            <span>O que estudar hoje</span>
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        {studyToday.length === 0 ? (
                            <EmptyState className="border-none p-4">
                                <EmptyStateTitle>
                                    Nada agendado para hoje
                                </EmptyStateTitle>
                                <EmptyStateDescription>
                                    Configure um cronograma, um ciclo de estudos
                                    ou um plano de estudos para receber
                                    recomendações aqui.
                                </EmptyStateDescription>
                            </EmptyState>
                        ) : (
                            studyToday.map((item, index) => (
                                <StudyRecommendationRow
                                    key={`${item.source}-${item.subject_id}-${item.topic_id ?? index}`}
                                    item={item}
                                />
                            ))
                        )}
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <RotateCcw className="size-4" />
                            <span>O que revisar hoje</span>
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        {reviewToday.length === 0 ? (
                            <EmptyState className="border-none p-4">
                                <EmptyStateTitle>
                                    Nenhuma revisão para hoje
                                </EmptyStateTitle>
                                <EmptyStateDescription>
                                    Você está em dia com suas revisões de hoje.
                                </EmptyStateDescription>
                            </EmptyState>
                        ) : (
                            reviewToday.map((review) => (
                                <ReviewRecommendationRow
                                    key={review.id}
                                    review={review}
                                />
                            ))
                        )}
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <AlertTriangle className="size-4" />
                            <span>O que está atrasado</span>
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        {overdue.length === 0 ? (
                            <EmptyState className="border-none p-4">
                                <EmptyStateTitle>Nada atrasado</EmptyStateTitle>
                                <EmptyStateDescription>
                                    Nenhuma revisão pendente venceu ainda.
                                </EmptyStateDescription>
                            </EmptyState>
                        ) : (
                            overdue.map((review) => (
                                <ReviewRecommendationRow
                                    key={review.id}
                                    review={review}
                                    showDaysOverdue
                                />
                            ))
                        )}
                    </CardContent>
                </Card>
            </div>
        </>
    );
}

Recommendations.layout = {
    breadcrumbs: [
        {
            title: 'Recomendações',
            href: recommendationsIndex(),
        },
    ],
};
