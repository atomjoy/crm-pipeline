<!-- resources/js/Pages/Users/Index.vue -->
<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { useVueTable, getCoreRowModel, FlexRender } from '@tanstack/vue-table';
import debounce from 'lodash/debounce';
import RoleFacetFilter from './RoleFacetFilter.vue';
import DateRangeFilter from './DateRangeFilter.vue';
import { columns } from './Columns';
import { SimplePaginatedData, User, UserFilters, RoleFacet } from './table';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight, X, Trash2 } from 'lucide-vue-next';

const props = defineProps<{
    users: SimplePaginatedData<User> & { current_page: number; last_page: number; total: number; per_page: number };
    filters: UserFilters;
    roleFacets: RoleFacet[];
}>();
const { t } = useI18n({ useScope: 'global' });
const searchQuery = ref(props.filters.search || '');
const selectedRole = ref(props.filters.role || 'all');
const selectedStatus = ref(props.filters.is_active || 'all');
const rowSelection = ref({});
const tableCols = computed(() => columns(t, '/users'));

const table = useVueTable({
    // enableRowSelection: true,
    manualSorting: true,
    manualPagination: true,
    get data() {
        return props.users.data;
    },
    get columns() {
        return tableCols.value;
    },
    state: {
        get rowSelection() {
            return rowSelection.value;
        },
    },
    getRowId: (row: any) => row.id,
    getCoreRowModel: getCoreRowModel(),
    onRowSelectionChange: (updater) => {
        rowSelection.value = typeof updater === 'function' ? updater(rowSelection.value) : updater;
    },
});

const handleBulkDelete = () => {
    const selectedRows = table.getSelectedRowModel().rows;
    const selectedIds = selectedRows.map((row) => row.original.id);
    if (confirm(`Czy na pewno chcesz usunąć ${selectedIds.length} zaznaczonych użytkowników?`)) {
        router.post(
            '/users/bulk-delete',
            { ids: selectedIds },
            {
                preserveState: false,
                onSuccess: () => {
                    rowSelection.value = {};
                },
            },
        );
    }
};
const updateFilters = (extraParams: Partial<UserFilters> = {}) => {
    router.get('/users', { search: searchQuery.value || undefined, role: selectedRole.value !== 'all' ? selectedRole.value : undefined, is_active: selectedStatus.value !== 'all' ? selectedStatus.value : undefined, sort: props.filters.sort, direction: props.filters.direction, per_page: props.filters.per_page, page: props.users.current_page, ...extraParams }, { preserveState: true, preserveScroll: true, replace: true });
};
watch(
    searchQuery,
    debounce((value: string) => updateFilters({ search: value, page: 1 }), 300),
);
const handleFacetChange = (type: 'role' | 'status', value: any) => {
    if (type === 'role') selectedRole.value = String(value);
    if (type === 'status') selectedStatus.value = String(value);
    updateFilters({ page: 1 });
};
const resetFilters = () => {
    searchQuery.value = '';
    selectedRole.value = 'all';
    selectedStatus.value = 'all';
    router.get('/users', {}, { preserveState: true });
};
const handleSort = (columnId: string) => {
    if (columnId === 'select' || columnId === 'actions') return;
    const isCurrent = props.filters.sort === columnId;
    const direction = isCurrent && props.filters.direction === 'asc' ? 'desc' : 'asc';
    updateFilters({ sort: columnId, direction });
};
const isFiltered = computed(() => searchQuery.value !== '' || selectedRole.value !== 'all' || selectedStatus.value !== 'all');

// Obsługujemy zmianę fasetów ról z checkboxów
const handleRoleFacetChange = (selectedRoles: string[]) => {
    router.get(
        '/users',
        {
            search: props.filters.search,
            sort: props.filters.sort,
            direction: props.filters.direction,
            per_page: props.filters.per_page,
            roles: selectedRoles, // Przekazujemy tablicę zaznaczonych ról []
            page: 1, // Resetujemy na 1. stronę po zmianie kryteriów ról
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

// Wywoływane, gdy użytkownik klika checkboxy ról
const onRoleFacetChange = (selectedRoles: string[]) => {
    router.get(
        '/users',
        {
            search: props.filters.search,
            sort: props.filters.sort,
            direction: props.filters.direction,
            per_page: props.filters.per_page,
            roles: selectedRoles, // Przesyłamy tablicę ról: ?roles[]=admin&roles[]=user
            page: 1, // Obowiązkowo cofamy na 1. stronę
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

const onDateFilterChange = (payload: { start_date?: string; end_date?: string }) => {
    router.get(
        '/users',
        {
            ...props.filters, // Zachowujemy stan wyszukiwarki tekstowej oraz fasetów ról
            start_date: payload.start_date,
            end_date: payload.end_date,
            page: 1, // Każda zmiana kryteriów filtrów zawsze cofa na 1. stronę
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};
</script>
<template>
    <div class="mx-auto max-w-7xl space-y-4 p-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex max-w-2xl flex-1 items-center space-x-2">
                <Input v-model="searchQuery" :placeholder="t('users.search_placeholder', 'Szukaj użytkowników...')" class="max-w-xs" />

                <Select :model-value="selectedRole" @update:model-value="(val) => handleFacetChange('role', val)">
                    <SelectTrigger class="w-37.5">
                        <SelectValue placeholder="Rola" />
                    </SelectTrigger>

                    <SelectContent>
                        <SelectItem value="all">Wszystkie role</SelectItem>
                        <SelectItem value="admin">Administrator</SelectItem><SelectItem value="manager">Menedżer</SelectItem>
                        <SelectItem value="user">Użytkownik</SelectItem>
                    </SelectContent>
                </Select>

                <Select :model-value="selectedStatus" @update:model-value="(val) => handleFacetChange('status', val)">
                    <SelectTrigger class="w-37.5">
                        <SelectValue placeholder="Status" />
                    </SelectTrigger>

                    <SelectContent>
                        <SelectItem value="all">Wszystkie statusy</SelectItem>
                        <SelectItem value="1">Aktywni</SelectItem>
                        <SelectItem value="0">Nieaktywni</SelectItem>
                    </SelectContent>
                </Select>

                <Button v-if="isFiltered" variant="ghost" @click="resetFilters" class="h-8 px-2 text-xs lg:px-3"> {{ t('global.actions.reset', 'Wyczyść') }} <X class="ml-2 h-4 w-4" /> </Button>
            </div>

            <div class="flex items-center space-x-2">
                <DateRangeFilter :start-date="filters.start_date" :end-date="filters.end_date" @change="onDateFilterChange" />
                <RoleFacetFilter :facets="roleFacets" :selected-roles="filters.roles || []" @change="handleRoleFacetChange" />
                <RoleFacetFilter :facets="roleFacets" :selected-roles="filters.roles || []" @change="onRoleFacetChange" />
            </div>

            <div class="flex items-center space-x-2">
                <div v-if="table.getFilteredSelectedRowModel().rows.length > 0" class="flex items-center space-x-2">
                    <span class="text-sm font-medium text-gray-500">Zaznaczono: {{ table.getFilteredSelectedRowModel().rows.length }}</span>
                    <Button variant="destructive" size="sm" @click="handleBulkDelete" class="h-8"><Trash2 class="mr-2 h-4 w-4" />Usuń zaznaczone</Button>
                </div>
                <div class="text-sm text-muted-foreground">
                    <Badge variant="secondary">{{ users.total }} wyników</Badge>
                </div>
            </div>
        </div>

        <div class="rounded-md border bg-white shadow-sm">
            <Table>
                <TableHeader>
                    <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
                        <TableHead v-for="header in headerGroup.headers" :key="header.id" @click="handleSort(header.id)" :class="[header.id !== 'select' && header.id !== 'actions' ? 'cursor-pointer select-none hover:bg-slate-50' : '', 'font-semibold']">
                            <div class="flex items-center space-x-1">
                                <FlexRender :render="header.column.columnDef.header" :props="header.getContext()" /><span class="text-xs text-muted-foreground" v-if="filters.sort === header.id">{{ filters.direction === 'asc' ? '▲' : '▼' }}</span>
                            </div>
                        </TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody>
                    <TableRow v-for="row in table.getRowModel().rows" :key="row.id" :data-state="row.getIsSelected() && 'selected'">
                        <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id"><FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" /></TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <div class="flex items-center justify-between px-2 py-2">
            <div class="flex items-center space-x-2">
                <p class="text-sm font-medium text-gray-600">Wierszy na stronę</p>
                <Select :model-value="String(users.per_page)" @update:model-value="(val) => updateFilters({ per_page: Number(val), page: 1 })">
                    <SelectTrigger class="h-8 w-17.5">
                        <SelectValue :placeholder="String(users.per_page)" />
                    </SelectTrigger>

                    <SelectContent side="top">
                        <SelectItem v-for="size in [5, 10, 25, 50]" :key="size" :value="String(size)">{{ size }}</SelectItem>
                    </SelectContent>
                </Select>
            </div>
            <div class="flex w-25 items-center justify-center text-sm font-medium text-gray-600">Strona {{ users.current_page }} z {{ users.last_page }}</div>
            <div class="flex items-center space-x-2">
                <Button variant="outline" class="hidden h-8 w-8 p-0 lg:flex" :disabled="users.current_page === 1" @click="updateFilters({ page: 1 })"><ChevronsLeft class="h-4 w-4" /></Button>
                <Button variant="outline" class="h-8 w-8 p-0" :disabled="users.current_page === 1" @click="updateFilters({ page: users.current_page - 1 })"><ChevronLeft class="h-4 w-4" /></Button>
                <Button variant="outline" class="h-8 w-8 p-0" :disabled="users.current_page === users.last_page" @click="updateFilters({ page: users.current_page + 1 })"><ChevronRight class="h-4 w-4" /></Button>
                <Button variant="outline" class="hidden h-8 w-8 p-0 lg:flex" :disabled="users.current_page === users.last_page" @click="updateFilters({ page: users.last_page })"><ChevronsRight class="h-4 w-4" /></Button>
            </div>
        </div>
    </div>
</template>
