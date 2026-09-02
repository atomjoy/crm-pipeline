<!-- resources/js/Pages/Users/Index.vue -->
<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { useVueTable, getCoreRowModel, FlexRender } from '@tanstack/vue-table';
import debounce from 'lodash/debounce';

import { columns } from './Columns';
import { SimplePaginatedData, User, UserFilters } from '../types/table';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

const props = defineProps<{
    users: SimplePaginatedData<User>;
    filters: UserFilters;
}>();

const { t } = useI18n({ useScope: 'global' });
const searchQuery = ref(props.filters.search || '');

// Konfiguracja TanStack Table
const tableCols = computed(() => columns(t, '/users'));
const table = useVueTable({
    get data() {
        return props.users.data;
    },
    get columns() {
        return tableCols.value;
    },
    getCoreRowModel: getCoreRowModel(),
    manualSorting: true, // Informujemy TanStack, że sortowaniem zajmuje się Laravel
});

// Wyszukiwanie z debounce
watch(
    searchQuery,
    debounce((value: string) => {
        router.get(
            '/users',
            {
                search: value,
                sort: props.filters.sort,
                direction: props.filters.direction,
            },
            { preserveState: true, replace: true },
        );
    }, 300),
);

// Sortowanie za pomocą kliknięcia (spięte z nagłówkami Shadcn)
const handleSort = (columnId: string) => {
    const isCurrent = props.filters.sort === columnId;
    const direction = isCurrent && props.filters.direction === 'asc' ? 'desc' : 'asc';

    router.get(
        '/users',
        {
            search: searchQuery.value,
            sort: columnId,
            direction,
        },
        { preserveState: true, preserveScroll: true },
    );
};
</script>

<template>
    <div class="mx-auto max-w-7xl space-y-4 p-6">
        <Input v-model="searchQuery" placeholder="Szukaj użytkowników..." class="max-w-sm" />

        <div class="rounded-md border bg-white">
            <Table>
                <TableHeader>
                    <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
                        <TableHead v-for="header in headerGroup.headers" :key="header.id" @click="handleSort(header.id)" class="cursor-pointer select-none hover:bg-slate-50">
                            <FlexRender :render="header.column.columnDef.header" :props="header.getContext()" />
                            <span class="ml-2 text-xs" v-if="filters.sort === header.id">
                                {{ filters.direction === 'asc' ? '▲' : '▼' }}
                            </span>
                        </TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="row in table.getRowModel().rows" :key="row.id">
                        <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id">
                            <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </div>
</template>
