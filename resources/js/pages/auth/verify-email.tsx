import { Form, Head } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { logout } from '@/routes';
import { send } from '@/routes/verification';

export default function VerifyEmail({ status }: { status?: string }) {
    return (
        <>
            <Head title="Verificação de e-mail" />

            <div className="mb-4 text-center text-sm text-muted-foreground">
                Antes de continuar, verifique seu endereço de e-mail clicando no
                link que acabamos de enviar. Caso não tenha recebido o e-mail,
                ficaremos felizes em enviar outro.
            </div>

            {status === 'verification-link-sent' && (
                <div className="mb-4 text-center text-sm font-medium text-green-600">
                    Um novo link de verificação foi enviado para o endereço de
                    e-mail informado no seu cadastro.
                </div>
            )}

            <Form {...send.form()} className="space-y-6 text-center">
                {({ processing }) => (
                    <Button disabled={processing}>
                        {processing && (
                            <LoaderCircle className="h-4 w-4 animate-spin" />
                        )}
                        Reenviar e-mail de verificação
                    </Button>
                )}
            </Form>

            <TextLink
                href={logout()}
                as="button"
                className="mx-auto block text-sm"
            >
                Sair
            </TextLink>
        </>
    );
}

VerifyEmail.layout = {
    title: 'Verifique seu e-mail',
    description: 'Confirme seu endereço de e-mail para continuar',
};
