// resources/js/Pages/Users/Columns.ts
import { h } from 'vue';
import { Composer } from 'vue-i18n'; // Import typu dla funkcji t
import { TableColumn, User } from './table';
import UserStatusBadge from '../TableUserStatusBadge.vue';
import TableActionsDropdown from '../TableActionsDropdown.vue';

interface ColumnOptions {
    t: Composer['t']; // Otypowanie przekazanej funkcji t
    onEdit: (user: User) => void;
    onDelete?: (user: User) => void;
}

interface DropdownItem {
    label: string;
    onClick: () => void;
    danger?: boolean;
}

export const getUserColumns = (options: ColumnOptions): TableColumn<User>[] => [
    {
        key: 'id',
        label: options.t('table.users.id'),
    },
    {
        key: 'name',
        label: options.t('table.users.name'),
    },
    {
        key: 'email',
        label: options.t('table.users.email'),
    },
    {
        key: 'is_active',
        label: options.t('table.users.status'),
        render: (value: boolean) => h(UserStatusBadge, { active: value }),
    },
    {
        key: 'actions',
        label: '',
        // Ignoruj pierwzy parametr
        render: (_, row) => {
            const menuItems: DropdownItem[] = [
                {
                    label: options.t('table.users.actions.edit'),
                    onClick: () => options.onEdit(row),
                },
            ];

            if (options.onDelete) {
                menuItems.push({
                    label: options.t('table.users.actions.delete', { name: row.name }),
                    onClick: () => options.onDelete?.(row),
                    danger: true,
                });
            }

            return h(TableActionsDropdown, { items: menuItems });
        },
    },
];
