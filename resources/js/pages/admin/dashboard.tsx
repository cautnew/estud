import { Head, Link } from '@inertiajs/react';
import { Activity, Gauge } from 'lucide-react';
import Heading from '@/components/heading';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard } from '@/routes/admin';
import { index as activityLogIndex } from '@/routes/admin/activity-log';

export default function AdminDashboard() {
    return (
        <>
            <Head title="Painel do administrador" />

            <Heading
                title="Painel do administrador"
                description="Área restrita a usuários com a role Admin."
            />

            <div className="mt-6 grid gap-4 sm:grid-cols-2">
                <Link href={activityLogIndex()}>
                    <Card className="transition-colors hover:bg-accent">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Activity className="size-4" />
                                Log de atividades
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="text-sm text-muted-foreground">
                            Auditoria de criação, atualização e exclusão de
                            registros.
                        </CardContent>
                    </Card>
                </Link>

                <a href="/pulse">
                    <Card className="transition-colors hover:bg-accent">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Gauge className="size-4" />
                                Monitoramento (Pulse)
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="text-sm text-muted-foreground">
                            Requisições lentas, exceções, filas e uso de cache.
                        </CardContent>
                    </Card>
                </a>
            </div>
        </>
    );
}

AdminDashboard.layout = {
    breadcrumbs: [
        {
            title: 'Administração',
            href: dashboard(),
        },
    ],
};
