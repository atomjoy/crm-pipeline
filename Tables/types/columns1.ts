// resources/js/Pages/Users/Columns.ts
import { h } from 'vue';
import { TableColumn, User } from './table';
import UserStatusBadge from './UserStatusBadge.vue';

// Definiujemy interfejs dla opcji/akcji, które chcemy wstrzyknąć do kolumn
interface ColumnActions {
    onEdit: (user: User) => void;
    onDelete?: (user: User) => void;
}

// Eksportujemy funkcję, która generuje kolumny na podstawie przekazanych akcji
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
        label: 'Akcje',
        render: (_, row) =>
            h('div', { class: 'flex space-x-2' }, [
                h(
                    'button',
                    {
                        class: 'text-indigo-600 hover:text-indigo-900 font-medium text-sm',
                        onClick: () => actions.onEdit(row),
                    },
                    'Edytuj',
                ),
                actions.onDelete
                    ? h(
                          'button',
                          {
                              class: 'text-red-600 hover:text-red-900 font-medium text-sm',
                              onClick: () => actions.onDelete?.(row),
                          },
                          'Usuń',
                      )
                    : null,
            ]),
    },
];
