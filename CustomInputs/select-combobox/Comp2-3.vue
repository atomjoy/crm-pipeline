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
    <div class="combobox-wrapper" style="border: 2px solid #3b82f6; padding: 15px; border-radius: 8px">
        <h3 style="margin-top: 0; color: #3b82f6">1. Wyszukiwarka w Comp2 (Combobox)</h3>

        <input type="text" role="combobox" v-model="searchQuery" @input="handleInput" @keydown="handleKeydown" autocomplete="off" aria-autocomplete="list" :aria-expanded="isExpanded" :aria-controls="id" :aria-activedescendant="activeDescendantId" placeholder="Wyszukaj ..." style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; outline: none" />

        <ul :id="id" role="listbox" style="list-style: none; padding: 0; margin: 10px 0 0 0; max-height: 150px; overflow-y: auto">
            <li
                v-for="(item, index) in filteredItems"
                :key="item.id"
                :ref="(el) => setItemRef(el, index)"
                :id="`${id}-opt-${item.id}`"
                role="option"
                :aria-selected="index === focusedIndex"
                @mouseenter="focusedIndex = index"
                @mousedown="emit('success', item)"
                :style="{
                    padding: '8px',
                    background: index === focusedIndex ? '#3b82f6' : '#eee',
                    color: index === focusedIndex ? 'white' : 'black',
                    margin: '4px 0',
                    borderRadius: '4px',
                    cursor: 'pointer',
                }"
            >
                {{ item.name }}
            </li>

            <li v-if="filteredItems.length === 0" style="padding: 8px; color: #999; text-align: center">Brak wyników 🔍</li>
        </ul>
    </div>
</template>
