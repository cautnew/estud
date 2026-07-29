import { Form, Head, usePage } from '@inertiajs/react';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import DeleteUser from '@/components/delete-user';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { edit } from '@/routes/profile';
import type { Auth } from '@/types';

type PageProps = {
    auth: Auth;
    timezones: string[];
};

export default function Profile() {
    const { auth, timezones } = usePage<PageProps>().props;

    return (
        <>
            <Head title="Configurações de perfil" />

            <h1 className="sr-only">Configurações de perfil</h1>

            <div className="space-y-6">
                <Heading
                    variant="small"
                    title="Perfil"
                    description="Atualize seu nome, e-mail, foto e fuso horário"
                />

                <Form
                    {...ProfileController.update.form()}
                    options={{
                        preserveScroll: true,
                    }}
                    className="space-y-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="avatar">Foto de perfil</Label>

                                <div className="flex items-center gap-4">
                                    <Avatar className="size-16">
                                        <AvatarImage
                                            src={auth.user.avatar}
                                            alt={auth.user.name}
                                        />
                                        <AvatarFallback>
                                            {auth.user.name
                                                .charAt(0)
                                                .toUpperCase()}
                                        </AvatarFallback>
                                    </Avatar>

                                    <Input
                                        id="avatar"
                                        type="file"
                                        className="mt-1 block w-full"
                                        name="avatar"
                                        accept="image/*"
                                    />
                                </div>

                                <InputError
                                    className="mt-2"
                                    message={errors.avatar}
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="name">Nome</Label>

                                <Input
                                    id="name"
                                    className="mt-1 block w-full"
                                    defaultValue={auth.user.name}
                                    name="name"
                                    required
                                    autoComplete="name"
                                    placeholder="Nome completo"
                                />

                                <InputError
                                    className="mt-2"
                                    message={errors.name}
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="email">
                                    Endereço de e-mail
                                </Label>

                                <Input
                                    id="email"
                                    type="email"
                                    className="mt-1 block w-full"
                                    defaultValue={auth.user.email}
                                    name="email"
                                    required
                                    autoComplete="username"
                                    placeholder="Endereço de e-mail"
                                />

                                <InputError
                                    className="mt-2"
                                    message={errors.email}
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="timezone">Fuso horário</Label>

                                <Select
                                    name="timezone"
                                    defaultValue={auth.user.timezone}
                                >
                                    <SelectTrigger
                                        id="timezone"
                                        className="w-full"
                                    >
                                        <SelectValue placeholder="Selecione um fuso horário" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {timezones.map((timezone) => (
                                            <SelectItem
                                                key={timezone}
                                                value={timezone}
                                            >
                                                {timezone}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>

                                <InputError
                                    className="mt-2"
                                    message={errors.timezone}
                                />
                            </div>

                            <div className="flex items-center gap-4">
                                <Button
                                    disabled={processing}
                                    data-test="update-profile-button"
                                >
                                    Salvar
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>

            <DeleteUser />
        </>
    );
}

Profile.layout = {
    breadcrumbs: [
        {
            title: 'Configurações de perfil',
            href: edit(),
        },
    ],
};
