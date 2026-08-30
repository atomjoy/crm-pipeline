<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <!-- Ukryj paginację, jeśli zawiera tylko link "Poprzednia" i "Następna" (brak stron) -->
    <div v-if="links.length > 3" class="mt-4 flex flex-wrap justify-center gap-1">
        <component
            :is="link.url ? Link : 'span'"
            v-for="(link, index) in links"
            :key="index"
            :href="link.url"
            v-html="link.label"
            class="rounded border px-3 py-1.5 text-sm transition-colors"
            :class="{
                'border-blue-600 bg-blue-600 font-medium text-white': link.active,
                'border-gray-300 text-gray-500 hover:bg-gray-50': link.url && !link.active,
                'cursor-not-allowed border-gray-100 text-gray-300': !link.url,
            }"
            preserve-state
        />
    </div>
</template>
