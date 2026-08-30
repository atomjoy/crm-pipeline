<script setup lang="ts">
import { ref, computed, useId, watch, nextTick } from 'vue';

interface Category {
    id: number | string;
    name: string;
}

const componentId = useId().replace(/:/g, '');
const listboxId = `listbox-${componentId}`;

const allCategories = computed<Category[]>(() => [
    { id: 1, name: 'Elektronika' },
    { id: 2, name: 'Moda i Odzież' },
    { id: 3, name: 'Dom i Ogród' },
    { id: 4, name: 'Książki i Rozrywka' },
    { id: 5, name: 'Sport i Turystyka' },
    { id: 6, name: 'Motoryzacja' },
    { id: 7, name: 'Zdrowie i Uroda' },
]);

const searchQuery = ref<string>('');
const focusedIndex = ref<number>(0);
const isExpanded = ref<boolean>(false);
const selectedCategories = ref<Category[]>([]);
const itemRefs = ref<HTMLElement[]>([]);

const setItemRef = (el: any, index: number) => {
    if (el) itemRefs.value[index] = el;
};

const isCategorySelected = (id: number | string): boolean => {
    return selectedCategories.value.some((selected) => selected.id === id);
};

const filteredCategories = computed<Category[]>(() => {
    return allCategories.value.filter((category) => category.name.toLowerCase().includes(searchQuery.value.toLowerCase()));
});

const activeDescendantId = computed<string | undefined>(() => {
    const currentItem = filteredCategories.value[focusedIndex.value];
    if (!currentItem || !isExpanded.value) return undefined;
    return `${listboxId}-opt-${currentItem.id}`;
});

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

const handleInput = (): void => {
    focusedIndex.value = 0;
    itemRefs.value = [];
    isExpanded.value = true;
};

const toggleCategory = (category: Category): void => {
    if (isCategorySelected(category.id)) {
        removeCategory(category.id);
    } else {
        selectedCategories.value.push(category);
    }
    searchQuery.value = '';
    focusedIndex.value = 0;
    itemRefs.value = [];
};

const removeCategory = (id: number | string): void => {
    selectedCategories.value = selectedCategories.value.filter((cat) => cat.id !== id);
};

const handleKeydown = (event: KeyboardEvent): void => {
    if (!isExpanded.value) {
        if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
            isExpanded.value = true;
        }
        return;
    }

    if (filteredCategories.value.length === 0) return;

    if (event.key === 'ArrowDown') {
        event.preventDefault();
        focusedIndex.value = (focusedIndex.value + 1) % filteredCategories.value.length;
    } else if (event.key === 'ArrowUp') {
        event.preventDefault();
        focusedIndex.value = (focusedIndex.value - 1 + filteredCategories.value.length) % filteredCategories.value.length;
    } else if (event.key === 'Enter') {
        event.preventDefault();
        const activeCategory = filteredCategories.value[focusedIndex.value];
        if (activeCategory) {
            toggleCategory(activeCategory);
        }
    } else if (event.key === 'Escape') {
        isExpanded.value = false;
    }
};

const handleBlur = (): void => {
    setTimeout(() => {
        isExpanded.value = false;
    }, 200);
};
</script>

<template>
    <div class="multiselect-container" style="font-family: sans-serif; max-width: 400px; position: relative">
        <input type="text" role="combobox" v-model="searchQuery" @input="handleInput" @keydown="handleKeydown" @focus="isExpanded = true" @blur="handleBlur" autocomplete="off" aria-autocomplete="list" :aria-expanded="isExpanded" :aria-controls="listboxId" :aria-activedescendant="activeDescendantId" placeholder="Wyszukaj i przełącz kategorię..." style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; outline: none; font-size: 14px" />

        <ul v-if="isExpanded && filteredCategories.length > 0" :id="listboxId" role="listbox" style="list-style: none; padding: 0; margin: 5px 0 0 0; max-height: 160px; overflow-y: auto; border: 1px solid #ddd; border-radius: 6px; position: absolute; width: 100%; background: white; z-index: 10; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1)">
            <li
                v-for="(category, index) in filteredCategories"
                :key="category.id"
                :ref="(el) => setItemRef(el, index)"
                :id="`${listboxId}-opt-${category.id}`"
                role="option"
                :aria-selected="isCategorySelected(category.id)"
                @mousedown="toggleCategory(category)"
                :style="{
                    padding: '10px',
                    background: index === focusedIndex ? '#3b82f6' : 'white',
                    color: index === focusedIndex ? 'white' : '#333',
                    cursor: 'pointer',
                    fontSize: '14px',
                    display: 'flex',
                    justifyContent: 'space-between',
                    alignItems: 'center',
                }"
            >
                <span>{{ category.name }}</span>
                <span v-if="isCategorySelected(category.id)" :style="{ color: index === focusedIndex ? 'white' : '#22c55e', fontWeight: 'bold' }">✓</span>
            </li>
        </ul>

        <div class="chips-wrapper" style="display: flex; flex-wrap: wrap; gap: 6px; margin-top: 10px">
            <div v-for="category in selectedCategories" :key="category.id" style="display: inline-flex; align-items: center; background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 16px; font-size: 13px; font-weight: 500">
                <span>{{ category.name }}</span>
                <button type="button" @click="removeCategory(category.id)" style="background: none; border: none; color: #0369a1; margin-left: 6px; cursor: pointer; font-weight: bold; padding: 0 2px; font-size: 14px; line-height: 1">&times;</button>
            </div>
        </div>
    </div>
</template>
