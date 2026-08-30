<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    links: {
        type: Array,
        required: true,
    },
    meta: {
        type: Object,
        required: true,
    },
});

// Wyciągamy TYLKO czyste numery stron dla pętli v-for (od 1 do max 5)
const visiblePageLinks = computed(() => {
    if (!props.links || props.links.length <= 3) return [];

    // Wycinamy skrajne elementy Laravela (poprzednia/następna), zostawiając same numery
    const pageLinks = props.links.slice(1, -1);
    const activeIndex = pageLinks.findIndex((link) => link.active);

    let start = Math.max(0, activeIndex - 2);
    let end = Math.min(pageLinks.length, start + 5);

    if (end - start < 5) {
        start = Math.max(0, end - 5);
    }

    return pageLinks.slice(start, end);
});

// Pobieramy linki do kroku wstecz i w przód bezpośrednio ze struktury Laravela
const previousPageUrl = computed(() => props.links[0]?.url);
const nextPageUrl = computed(() => props.links[props.links.length - 1]?.url);

const isFirstPage = computed(() => props.meta.current_page === 1);
const isLastPage = computed(() => props.meta.current_page === props.meta.last_page);
</script>

<template>
    <div v-if="props.links.length > 3" class="mt-4 flex flex-col items-center gap-3">
        <!-- Przyciski nawigacyjne -->
        <div class="flex flex-wrap justify-center gap-1">
            <!-- NA SAM POCZĄTEK (<<) -->
            <component
                :is="!isFirstPage ? Link : 'span'"
                :href="props.meta.first_page_url"
                class="min-w-9 rounded border px-3 py-1.5 text-center text-sm font-medium transition-colors select-none"
                :class="{
                    'cursor-pointer border-gray-300 text-gray-500 hover:bg-gray-50': !isFirstPage,
                    'cursor-not-allowed border-gray-200 bg-gray-50 text-gray-300': isFirstPage,
                }"
                preserve-state
            >
                &lt;&lt;
            </component>

            <!-- O JEDNĄ STRONĘ W LEWO (<) -->
            <component
                :is="previousPageUrl ? Link : 'span'"
                :href="previousPageUrl"
                class="min-w-9 rounded border px-3 py-1.5 text-center text-sm font-medium transition-colors select-none"
                :class="{
                    'cursor-pointer border-gray-300 text-gray-500 hover:bg-gray-50': previousPageUrl,
                    'cursor-not-allowed border-gray-200 bg-gray-50 text-gray-300': !previousPageUrl,
                }"
                preserve-state
            >
                &lt;
            </component>

            <!-- PĘTLA WYŚWIETLAJĄCA WYŁĄCZNIE NUMERY STRON -->
            <component
                :is="link.url ? Link : 'span'"
                v-for="(link, index) in visiblePageLinks"
                :key="index"
                :href="link.url"
                class="min-w-9 rounded border px-3 py-1.5 text-center text-sm font-medium transition-colors select-none"
                :class="{
                    'border-blue-600 bg-blue-600 text-white': link.active,
                    'cursor-pointer border-gray-300 text-gray-500 hover:bg-gray-50': link.url && !link.active,
                    'cursor-not-allowed border-gray-200 bg-gray-50 text-gray-300': !link.url,
                }"
                preserve-state
            >
                <span v-html="link.label"></span>
            </component>

            <!-- O JEDNĄ STRONĘ W PRAWO (>) -->
            <component
                :is="nextPageUrl ? Link : 'span'"
                :href="nextPageUrl"
                class="min-w-9 rounded border px-3 py-1.5 text-center text-sm font-medium transition-colors select-none"
                :class="{
                    'cursor-pointer border-gray-300 text-gray-500 hover:bg-gray-50': nextPageUrl,
                    'cursor-not-allowed border-gray-200 bg-gray-50 text-gray-300': !nextPageUrl,
                }"
                preserve-state
            >
                &gt;
            </component>

            <!-- NA SAM KONIEC (>>) -->
            <component
                :is="!isLastPage ? Link : 'span'"
                :href="props.meta.last_page_url"
                class="min-w-9 rounded border px-3 py-1.5 text-center text-sm font-medium transition-colors select-none"
                :class="{
                    'cursor-pointer border-gray-300 text-gray-500 hover:bg-gray-50': !isLastPage,
                    'cursor-not-allowed border-gray-200 bg-gray-50 text-gray-300': isLastPage,
                }"
                preserve-state
            >
                &gt;&gt;
            </component>
        </div>

        <!-- Licznik stron -->
        <div class="overflow-hidden rounded border border-gray-200 bg-gray-50 px-3 py-2 text-[13px] font-medium text-gray-500">
            Strona <span class="font-bold text-gray-700">{{ props.meta.current_page }}</span> z <span class="font-bold text-gray-700">{{ props.meta.last_page }}</span>
        </div>
    </div>
</template>
