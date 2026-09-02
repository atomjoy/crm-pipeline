<!-- resources/js/Components/Pagination.vue -->
<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

// Przekazujemy linki bezpośrednio z obiektu użytkowników z Laravela
// <Pagination :links="users.links" />

defineProps<{
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
}>();
</script>

<template>
    <div v-if="links.length > 3" class="mt-4 flex flex-wrap justify-center space-x-1">
        <template v-for="(link, key) in links" :key="key">
            <div v-if="link.url === null" class="cursor-not-allowed rounded border bg-gray-50 px-4 py-2 text-sm leading-4 text-gray-400 select-none" v-html="link.label" />
            <Link v-else :href="link.url" class="rounded border px-4 py-2 text-sm leading-4 transition-colors duration-150 hover:bg-indigo-50 focus:border-indigo-500 focus:text-indigo-500" :class="{ 'bg-indigo-600 font-semibold text-white hover:bg-indigo-600': link.active }" preserve-scroll preserve-state v-html="link.label" />
        </template>
    </div>
</template>
