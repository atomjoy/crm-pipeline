<script setup lang="ts">
import { ref, computed, useId, watch, nextTick } from 'vue';
import type { MainItem } from './types';

const props = defineProps<{
    isExpanded: boolean;
}>();

const emit = defineEmits<{
    (e: 'success', item: MainItem): void;
}>();

const componentId = useId().replace(/:/g, '');
const id = `listbox-${componentId}`;

const allItems = computed<MainItem[]>(() => [
    { id: 'clear', name: 'Clear field' },
    { id: 1, name: 'Apple iPhone' },
    { id: 2, name: 'Samsung Galaxy' },
    { id: 3, name: 'Google Pixel' },
    { id: 4, name: 'Xiaomi Redmi' },
]);

const searchQuery = ref<string>('');
const focusedIndex = ref<number>(0);
const itemRefs = ref<HTMLElement[]>([]);

const setItemRef = (el: any, index: number) => {
    if (el) {
        itemRefs.value[index] = el;
    }
};

watch(focusedIndex, async (newIndex) => {
    await nextTick();
    const activeElement = itemRefs.value[newIndex];
    if (activeElement) {
        activeElement.scrollIntoView({
            block: 'nearest',
            behavior: 'smooth',
        });
    }
});

const filteredItems = computed<MainItem[]>(() => {
    return allItems.value.filter((item) => item.name.toLowerCase().includes(searchQuery.value.toLowerCase()));
});

const handleInput = (): void => {
    focusedIndex.value = 0;
    itemRefs.value = [];
};

const activeDescendantId = computed<string | undefined>(() => {
    const currentItem = filteredItems.value[focusedIndex.value];
    if (!currentItem) return undefined;
    return `${id}-opt-${currentItem.id}`;
});

const handleKeydown = (event: KeyboardEvent): void => {
    if (filteredItems.value.length === 0) return;

    if (event.key === 'ArrowDown') {
        event.preventDefault();
        focusedIndex.value = (focusedIndex.value + 1) % filteredItems.value.length;
    } else if (event.key === 'ArrowUp') {
        event.preventDefault();
        focusedIndex.value = (focusedIndex.value - 1 + filteredItems.value.length) % filteredItems.value.length;
    } else if (event.key === 'Enter') {
        event.preventDefault();
        if (filteredItems.value[focusedIndex.value]) {
            emit('success', filteredItems.value[focusedIndex.value]);
        }
    }
};
</script>

<template>
    <div class="w-full max-w-sm rounded-lg border-2 border-blue-500 p-4 font-sans">
        <h3 class="mt-0 mb-3 text-base font-semibold text-blue-500">1. Wyszukiwarka w Comp2 (Combobox)</h3>

        <input type="text" role="combobox" v-model="searchQuery" @input="handleInput" @keydown="handleKeydown" autocomplete="off" aria-autocomplete="list" :aria-expanded="isExpanded" :aria-controls="id" :aria-activedescendant="activeDescendantId" placeholder="Wyszukaj ..." class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm transition outline-none focus:border-blue-500" />

        <ul :id="id" role="listbox" class="m-0 mt-2.5 max-h-36 list-none overflow-y-auto p-0">
            <li v-for="(item, index) in filteredItems" :key="item.id" :ref="(el) => setItemRef(el, index)" :id="`${id}-opt-${item.id}`" role="option" :aria-selected="index === focusedIndex" @mouseenter="focusedIndex = index" @mousedown="emit('success', item)" :class="['my-1 cursor-pointer rounded px-2 py-1.5 text-sm transition-colors', index === focusedIndex ? 'bg-blue-500 text-white' : 'bg-gray-100 text-black']">
                {{ item.name }}
            </li>

            <li v-if="filteredItems.length === 0" class="py-2 text-center text-sm text-gray-400">Brak wyników 🔍</li>
        </ul>
    </div>
</template>
