// resources/js/Pages/Users/Columns.ts
import { h } from 'vue';
// import { t } from './i18n'; // Import translate
import { Composer } from 'vue-i18n'; // Import typu dla funkcji t
import { TableColumn, User } from './table';
import UserStatusBadge from '../TableUserStatusBadge.vue';
import TableActionsDropdown from '../TableActionsDropdown.vue';

interface ColumnActions {
    onEdit: (user: User) => void;
    onDelete?: (user: User) => void;
    t?: Composer['t'];
}

// Definiujemy typ dla pojedynczego elementu menu, identyczny jak w komponencie Vue
interface DropdownItem {
    label: string;
    onClick: () => void;
    danger?: boolean;
}

export const getUserColumns = (actions: ColumnActions): TableColumn<User>[] => [
    {
        key: 'id',
        label: 'ID',
    },
    {
        key: 'name',
        label: 'Imię i nazwisko',
    },
    {
        key: 'email',
        label: 'Adres Email',
    },
    {
        key: 'is_active',
        label: 'Status',
        render: (value: boolean) => h(UserStatusBadge, { active: value }),
    },
    {
        key: 'actions',
        label: '',
        render: (_, row) => {
            // JAWNE TYPOWANIE: Informujemy TS, że to tablica obiektów typu DropdownItem
            const menuItems: DropdownItem[] = [
                {
                    label: 'Edytuj profil',
                    onClick: () => actions.onEdit(row),
                    danger: false,
                },
            ];

            if (actions.onDelete) {
                menuItems.push({
                    label: 'Usuń użytkownika',
                    onClick: () => actions.onDelete?.(row),
                    danger: true,
                });
            }

            return h(TableActionsDropdown, { items: menuItems });
        },
    },
];
