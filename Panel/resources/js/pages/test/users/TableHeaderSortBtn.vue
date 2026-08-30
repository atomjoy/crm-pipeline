<script setup lang="ts">
import { ChevronsUpDown, ChevronUp, ChevronDown } from 'lucide-vue-next';

const props = withDefaults(
    defineProps<{
        name: string;
        sortField: string;
        sortDirection: string;
    }>(),
    {
        name: 'id',
        sortField: 'id',
        sortDirection: 'asc',
    },
);
</script>
<template>
    <span class="text-gray-400">
        <ChevronsUpDown v-if="sortField !== name" class="h-3.5 w-3.5" />
        <ChevronUp v-else-if="sortDirection === 'asc'" class="h-3.5 w-3.5 text-blue-600" />
        <ChevronDown v-else class="h-3.5 w-3.5 text-blue-600" />
    </span>
</template>
