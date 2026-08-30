<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    links: {
        type: Array,
        required: true,
    },
});

// Logika ograniczania wyświetlanych stron do max 5
const visibleLinks = computed(() => {
    if (props.links.length <= 3) return [];

    // Pierwszy link ("Poprzednia") i ostatni ("Następna") z domyślnej paginacji Laravela
    const firstLink = props.links[0];
    const lastLink = props.links[props.links.length - 1];

    // Wyciągamy tylko linki będące numerami stron (odrzucamy skrajne)
    const pageLinks = props.links.slice(1, -1);

    // Szukamy indeksu aktualnie aktywnej strony
    const activeIndex = pageLinks.findIndex((link) => link.active);

    // Maksymalnie 5 stron: ustawiamy zakres tak, aby aktywna strona była w środku
    let start = Math.max(0, activeIndex - 2);
    let end = Math.min(pageLinks.length, start + 5);

    // Korekta zakresu w przypadku, gdy jesteśmy na ostatnich stronach
    if (end - start < 5) {
        start = Math.max(0, end - 5);
    }

    // Wycinamy maksymalnie 5 stron i łączymy z przyciskami Poprzednia/Następna
    return [firstLink, ...pageLinks.slice(start, end), lastLink];
});
</script>

<template>
    <!-- Komponent wyświetli się tylko, gdy mamy faktyczne strony do pokazania -->
    <div v-if="visibleLinks.length > 0" class="mt-4 flex flex-wrap justify-center gap-1">
        <component
            :is="link.url ? Link : 'span'"
            v-for="(link, index) in visibleLinks"
            :key="index"
            :href="link.url"
            v-html="link.label"
            class="min-w-9 rounded border px-3 py-1.5 text-center text-sm transition-colors"
            :class="{
                'border-blue-600 bg-blue-600 font-medium text-white': link.active,
                'border-gray-300 text-gray-500 hover:bg-gray-50': link.url && !link.active,
                'cursor-not-allowed border-gray-100 text-gray-300': !link.url,
            }"
            preserve-state
        />
    </div>
</template>
