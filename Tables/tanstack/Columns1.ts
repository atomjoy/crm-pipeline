// resources/js/Pages/Users/Columns.ts
import { h } from 'vue';
import { router } from '@inertiajs/vue3';
import { createColumnHelper } from '@tanstack/vue-table';
import { User } from '../types/table';
import { Button } from '@/components/ui/button';
import { MoreHorizontal } from 'lucide-vue-next';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';

const columnHelper = createColumnHelper<User>();

export const columns = (t: Function, basePath: string) => [
    columnHelper.accessor('id', {
        header: () => t('users.id', 'ID'),
    }),
    columnHelper.accessor('name', {
        header: () => t('users.name', 'Imię i nazwisko'),
    }),
    columnHelper.accessor('email', {
        header: () => t('users.email', 'Email'),
    }),
    columnHelper.display({
        id: 'actions',
        // Shadcn DropdownMenu automatycznie obsługuje teleport, zamykanie na ESC i click-outside!
        cell: ({ row }) => {
            const user = row.original;
            return h(DropdownMenu, {}, () => [
                h(DropdownMenuTrigger, { asChild: true }, () => h(Button, { variant: 'ghost', class: 'h-8 w-8 p-0' }, () => [h('span', { class: 'sr-only' }, 'Otwórz menu'), h(MoreHorizontal, { class: 'h-4 w-4' })])),
                h(DropdownMenuContent, { align: 'end' }, () => [
                    h(DropdownMenuLabel, {}, () => t('global.actions.title', 'Akcje')),
                    h(DropdownMenuSeparator),
                    h(
                        DropdownMenuItem,
                        {
                            onClick: () => router.get(`${basePath}/${user.id}/edit`),
                        },
                        () => t('global.actions.edit', 'Edytuj'),
                    ),
                    h(
                        DropdownMenuItem,
                        {
                            class: 'text-red-600',
                            onClick: () => {
                                if (confirm(t('global.actions.confirm_delete', { name: user.name }))) {
                                    router.delete(`${basePath}/${user.id}`);
                                }
                            },
                        },
                        () => t('global.actions.delete', 'Usuń'),
                    ),
                ]),
            ]);
        },
    }),
];
