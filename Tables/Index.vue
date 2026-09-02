<!-- resources/js/Pages/Users/Index.vue -->
<script setup lang="ts">
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import debounce from 'lodash/debounce';
import Pagination from './Pagination.vue';
import DynamicTable from './DynamicTable.vue';
import { getUserColumns } from './types/columns';
import { SimplePaginatedData, User, UserFilters } from './types/table';

const props = defineProps<{
    users: SimplePaginatedData<User>;
    filters: UserFilters;
}>();

const { t } = useI18n({ useScope: 'global' });

const basePath = '/test/table/users';
const searchQuery = ref(props.filters.search || '');
const tableColumns = getUserColumns({ t, basePath: basePath });

// Reaktywne wyszukiwanie (Debounce)
watch(
    searchQuery,
    debounce((newValue: string) => {
        router.get(
            basePath,
            {
                search: newValue,
                sort: props.filters.sort,
                direction: props.filters.direction,
            },
            { preserveState: true, replace: true },
        );
    }, 300),
);

const onTableSort = (payload: { field: string; direction: 'asc' | 'desc' }) => {
    router.get(
        basePath,
        {
            search: searchQuery.value,
            sort: payload.field,
            direction: payload.direction,
        },
        { preserveState: true, preserveScroll: true },
    );
};
</script>

<template>
    <div class="mx-auto max-w-7xl p-6">
        <!-- Wyszukiwarka -->
        <div class="mb-4 max-w-xs">
            <input v-model="searchQuery" type="text" :placeholder="t('users.search_placeholder', 'Szukaj...')" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
        </div>

        <!-- Tabela mapuje dane z props.users, a stany sortowania z props.filters -->
        <DynamicTable :columns="tableColumns" :rows="users.data" :sort-by="filters.sort" :sort-direction="filters.direction" @sort="onTableSort" />

        <!-- Paginacja oparta o linki bezpośrednio z obiektu users -->
        <Pagination :links="users.links" />
    </div>
</template>
