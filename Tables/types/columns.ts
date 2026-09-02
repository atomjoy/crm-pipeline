// resources/js/Pages/Users/Columns.ts
import { h } from 'vue';
import { Composer } from 'vue-i18n';
import { TableColumn, User } from './table';
import UserStatusBadge from '@/Components/UserStatusBadge.vue';
import TableActionsDropdown from '@/Components/TableActionsDropdown.vue';

interface ColumnOptions {
    t: Composer['t'];
    basePath: string;
}

export const getUserColumns = (options: ColumnOptions): TableColumn<User>[] => [
    { key: 'id', label: options.t('users.id') },
    { key: 'name', label: options.t('users.name') },
    { key: 'email', label: options.t('users.email') },
    {
        key: 'is_active',
        label: options.t('users.status'),
        render: (value: boolean) => h(UserStatusBadge, { active: value }),
    },
    {
        key: 'actions',
        label: '',
        render: (_, row) =>
            h(TableActionsDropdown, {
                basePath: options.basePath,
                row: row,
            }),
    },
];
