// resources/js/Pages/Users/Columns.ts
import { h } from 'vue';
import { createColumnHelper } from '@tanstack/vue-table';
import { User } from './table';
import { Checkbox } from '@/components/ui/checkbox'; // Import Checkboxa z Shadcn
import { Button } from '@/components/ui/button';
import { MoreHorizontal } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';

const columnHelper = createColumnHelper<User>();

export const columns = (t: Function, basePath: string) => [
    // Checkbox masowego wyboru
    columnHelper.display({
        id: 'select',
        // Nagłówek: Checkbox "Zaznacz wszystkie"
        header: ({ table }) =>
            h(Checkbox, {
                checked: table.getIsAllPageRowsSelected() || (table.getIsSomePageRowsSelected() && 'indeterminate'),
                'onUpdate:checked': (value: boolean) => table.toggleAllPageRowsSelected(!!value),
                ariaLabel: 'Select all',
            }),
        // Komórka: Checkbox dla pojedynczego wiersza
        cell: ({ row }) =>
            h(Checkbox, {
                checked: row.getIsSelected(),
                'onUpdate:checked': (value: boolean) => row.toggleSelected(!!value),
                ariaLabel: 'Select row',
                // Zapobiegamy triggerowaniu akcji wiersza (np. jeśli kliknięcie w wiersz miałoby otwierać podgląd)
                onClick: (e: MouseEvent) => e.stopPropagation(),
            }),
        enableSorting: false,
        enableHiding: false,
    }),

    // Dotychczasowe kolumny...
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
        cell: ({ row }) => {
            const user = row.original;
            return h(DropdownMenu, {}, () => [
                h(DropdownMenuTrigger, { asChild: true }, () => h(Button, { variant: 'ghost', class: 'h-8 w-8 p-0' }, () => [h(MoreHorizontal, { class: 'h-4 w-4' })])),
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
                ]),
            ]);
        },
    }),
];
