<!-- resources/js/Components/DynamicTable.vue -->
<script setup lang="ts" generic="T extends Record<string, any>">
import { TableColumn } from './types/table';
import TableSkeletonRow from './TableSkeletonRow.vue';

const props = withDefaults(
    defineProps<{
        columns: TableColumn<T>[];
        rows: T[];
        loading?: boolean;
        sortBy?: string;
        sortDirection?: 'asc' | 'desc';
    }>(),
    {
        loading: false,
        sortBy: '',
        sortDirection: 'asc',
    },
);

const emit = defineEmits<{
    (e: 'sort', payload: { field: string; direction: 'asc' | 'desc' }): void;
}>();

const handleSort = (col: TableColumn<T>) => {
    if (!col.sortable) return;

    // Odwołujemy się przez `props.sortBy` oraz `props.sortDirection`
    const isCurrentField = props.sortBy === col.key;
    const direction = isCurrentField && props.sortDirection === 'asc' ? 'desc' : 'asc';

    emit('sort', { field: col.key, direction });
};
</script>

<template>
    <div class="ring-opacity-5 overflow-x-auto shadow ring-1 ring-black md:rounded-lg">
        <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-50">
                <tr>
                    <th v-for="col in columns" :key="col.key" @click="handleSort(col)" :class="[col.sortable ? 'cursor-pointer select-none hover:bg-gray-100' : '', 'px-3 py-3.5 text-left text-sm font-semibold text-gray-900 transition-colors duration-150']">
                        <div class="flex items-center space-x-1">
                            <span>{{ col.label }}</span>

                            <!-- Używamy props.sortBy oraz props.sortDirection -->
                            <span v-if="col.sortable" class="text-xs text-gray-400">
                                <template v-if="props.sortBy === col.key">
                                    {{ props.sortDirection === 'asc' ? '▲' : '▼' }}
                                </template>
                                <template v-else>
                                    <span class="opacity-30">▲▼</span>
                                </template>
                            </span>
                        </div>
                    </th>
                </tr>
            </thead>
            <!-- Reszta szablonu tbody pozostaje bez zmian (wykorzystuje bezpośrednie zmienne rows, loading, columns z defineProps) -->
            <tbody class="relative divide-y divide-gray-200 bg-white">
                <template v-if="loading">
                    <TableSkeletonRow v-for="n in 5" :key="n" :columns-count="columns.length" />
                </template>
                <template v-else-if="rows.length === 0">
                    <tr>
                        <td :colspan="columns.length" class="px-3 py-8 text-center text-sm text-gray-500">Brak danych do wyświetlenia.</td>
                    </tr>
                </template>
                <template v-else>
                    <tr v-for="(row, rowIndex) in rows" :key="row.id ?? rowIndex" class="transition-colors duration-150 hover:bg-gray-50">
                        <td v-for="col in columns" :key="col.key" class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                            <template v-if="col.render">
                                <component :is="col.render(row[col.key], row)" />
                            </template>
                            <template v-else>
                                {{ row[col.key] }}
                            </template>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</template>
