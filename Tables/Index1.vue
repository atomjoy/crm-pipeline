<!-- resources/js/Pages/Users/Index.vue -->
<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import { getUserColumns } from './types/columns';
import { SimplePaginatedData, User } from './types/table';
import DynamicTable from '@/Components/DynamicTable.vue';

defineProps<{
    users: SimplePaginatedData<User>;
}>();

// Actions path
const baseUsersPath = '/test/table/users';

// Locales
const { t } = useI18n({ useScope: 'global' });

// Generujemy kolumny przekazując tylko t do nagłówków
const tableColumns = getUserColumns({ t, basePath: baseUsersPath });
</script>

<template>
    <div class="mx-auto max-w-7xl p-6">
        <h1 class="mb-4 text-2xl font-bold">{{ t('users.title') }}</h1>

        <DynamicTable :columns="tableColumns" :rows="users.data" />
    </div>
</template>
