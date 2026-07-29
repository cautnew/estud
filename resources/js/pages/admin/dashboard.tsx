import { Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import { dashboard } from '@/routes/admin';

export default function AdminDashboard() {
    return (
        <>
            <Head title="Painel do administrador" />

            <Heading
                title="Painel do administrador"
                description="Área restrita a usuários com a role Admin."
            />
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
