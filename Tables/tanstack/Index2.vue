<!-- resources/js/Pages/Users/Index.vue -->
<script setup lang="ts">
import debounce from 'lodash/debounce';
import { ref, watch, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { useVueTable, getCoreRowModel, FlexRender } from '@tanstack/vue-table';
import { SimplePaginatedData, User, UserFilters } from './table';
import { columns } from './Columns';

// Komponenty Shadcn Vue
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight, X } from 'lucide-vue-next';

const props = defineProps<{
    users: SimplePaginatedData<User> & { current_page: number; last_page: number; total: number; per_page: number };
    filters: UserFilters;
}>();

const { t } = useI18n({ useScope: 'global' });

// Lokalne stany reaktywne dla fasetów i paska wyszukiwania
const searchQuery = ref(props.filters.search || '');
const selectedRole = ref(props.filters.role || 'all');
const selectedStatus = ref(props.filters.is_active || 'all');

// Inicjalizacja TanStack Table
const tableCols = computed(() => columns(t, '/users'));
const table = useVueTable({
    get data() {
        return props.users.data;
    },
    get columns() {
        return tableCols.value;
    },
    getCoreRowModel: getCoreRowModel(),
    manualSorting: true,
    manualPagination: true,
});

// Centralna funkcja aktualizująca adres URL przez Inertia.js (Server-side)
const updateFilters = (extraParams: Partial<UserFilters> = {}) => {
    router.get(
        '/users',
        {
            search: searchQuery.value || undefined,
            role: selectedRole.value !== 'all' ? selectedRole.value : undefined,
            is_active: selectedStatus.value !== 'all' ? selectedStatus.value : undefined,
            sort: props.filters.sort,
            direction: props.filters.direction,
            per_page: props.filters.per_page,
            page: props.users.current_page,
            ...extraParams,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

// Obserwowanie paska wyszukiwania z Debounce
watch(
    searchQuery,
    debounce((value: string) => {
        updateFilters({ search: value, page: 1 });
    }, 300),
);

// Obsługa zmian w fasetach (Rola / Status)
const handleFacetChange = (type: 'role' | 'status', value: any) => {
    if (type === 'role') selectedRole.value = value;
    if (type === 'status') selectedStatus.value = value;
    updateFilters({ page: 1 });
};

// Resetowanie wszystkich filtrów
const resetFilters = () => {
    searchQuery.value = '';
    selectedRole.value = 'all';
    selectedStatus.value = 'all';
    router.get('/users', {}, { preserveState: true });
};

// Obsługa dynamicznego sortowania kolumn
const handleSort = (columnId: string) => {
    const isCurrent = props.filters.sort === columnId;
    const direction = isCurrent && props.filters.direction === 'asc' ? 'desc' : 'asc';
    updateFilters({ sort: columnId, direction });
};

// Czy jakikolwiek filtr jest aktywny? (Do warunkowego wyświetlania przycisku "Wyczyść")
const isFiltered = computed(() => {
    return searchQuery.value !== '' || selectedRole.value !== 'all' || selectedStatus.value !== 'all';
});
</script>

<template>
    <div class="mx-auto max-w-7xl space-y-4 p-6">
        <!-- PASKI FILTRÓW, FASETÓW I RESETU -->
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex max-w-xl flex-1 items-center space-x-2">
                <Input v-model="searchQuery" :placeholder="t('users.search_placeholder', 'Szukaj użytkowników...')" class="max-w-xs" />

                <!-- Faseta 1: Rola -->
                <Select :model-value="selectedRole" @update:model-value="(val) => handleFacetChange('role', val)">
                    <SelectTrigger class="w-37.5">
                        <SelectValue placeholder="Rola" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">Wszystkie role</SelectItem>
                        <SelectItem value="admin">Administrator</SelectItem>
                        <SelectItem value="manager">Menedżer</SelectItem>
                        <SelectItem value="user">Użytkownik</SelectItem>
                    </SelectContent>
                </Select>

                <!-- Faseta 2: Status -->
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

                <Button v-if="isFiltered" variant="ghost" @click="resetFilters" class="h-8 px-2 text-xs lg:px-3">
                    {{ t('global.actions.reset', 'Wyczyść') }}
                    <X class="ml-2 h-4 w-4" />
                </Button>
            </div>

            <div class="text-sm text-muted-foreground">
                <Badge variant="secondary">{{ users.total }} {{ t('users.total_found', 'wyników') }}</Badge>
            </div>
        </div>

        <!-- STRUKTURA TABELI (SHADCN + TANSTACK) -->
        <div class="rounded-md border bg-white shadow-sm">
            <Table>
                <TableHeader>
                    <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
                        <TableHead v-for="header in headerGroup.headers" :key="header.id" @click="handleSort(header.id)" class="cursor-pointer font-semibold select-none hover:bg-slate-50">
                            <div class="flex items-center space-x-1">
                                <FlexRender :render="header.column.columnDef.header" :props="header.getContext()" />
                                <span class="text-xs text-muted-foreground" v-if="filters.sort === header.id">
                                    {{ filters.direction === 'asc' ? '▲' : '▼' }}
                                </span>
                            </div>
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

        <!-- ZAAWANSOWANA PAGINACJA -->
        <div class="flex items-center justify-between px-2 py-2">
            <div class="flex items-center space-x-2">
                <p class="text-sm font-medium text-gray-600">Wierszy na stronę</p>
                <Select :model-value="String(users.per_page)" @update:model-value="updateFilters({ per_page: Number($event), page: 1 })">
                    <SelectTrigger class="h-8 w-17.5">
                        <SelectValue :placeholder="String(users.per_page)" />
                    </SelectTrigger>
                    <SelectContent side="top">
                        <SelectItem v-for="size in [5, 10, 25, 50]" :key="size" :value="String(size)">
                            {{ size }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <div class="flex w-25 items-center justify-center text-sm font-medium text-gray-600">Strona {{ users.current_page }} z {{ users.last_page }}</div>

            <div class="flex items-center space-x-2">
                <Button variant="outline" class="hidden h-8 w-8 p-0 lg:flex" :disabled="users.current_page === 1" @click="updateFilters({ page: 1 })">
                    <ChevronsLeft class="h-4 w-4" />
                </Button>
                <Button variant="outline" class="h-8 w-8 p-0" :disabled="users.current_page === 1" @click="updateFilters({ page: users.current_page - 1 })">
                    <ChevronLeft class="h-4 w-4" />
                </Button>
                <Button variant="outline" class="h-8 w-8 p-0" :disabled="users.current_page === users.last_page" @click="updateFilters({ page: users.current_page + 1 })">
                    <ChevronRight class="h-4 w-4" />
                </Button>
                <Button variant="outline" class="hidden h-8 w-8 p-0 lg:flex" :disabled="users.current_page === users.last_page" @click="updateFilters({ page: users.last_page })">
                    <ChevronsRight class="h-4 w-4" />
                </Button>
            </div>
        </div>
    </div>
</template>
