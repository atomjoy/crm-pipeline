<!-- resources/js/Components/DynamicTable.vue -->
<script setup lang="ts" generic="T extends Record<string, any>">
import { TableColumn } from './types/table';

// Korzystamy z nowej składni generycznej w Vue 3 dla elastycznego typowania wierszy
defineProps<{
    columns: TableColumn<T>[];
    rows: T[];
}>();
</script>

<template>
    <div class="ring-opacity-5 overflow-hidden shadow ring-1 ring-black md:rounded-lg">
        <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-50">
                <tr>
                    <th v-for="col in columns" :key="col.key" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                        {{ col.label }}
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                <tr v-for="(row, rowIndex) in rows" :key="row.id ?? rowIndex">
                    <td v-for="col in columns" :key="col.key" class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        <!-- Renderowanie przez h() / VNode -->
                        <template v-if="col.render">
                            <component :is="col.render(row[col.key], row)" />
                        </template>

                        <!-- Domyślne renderowanie tekstu -->
                        <template v-else>
                            {{ row[col.key] }}
                        </template>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
