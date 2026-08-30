<script setup lang="ts">
import { ref, computed, useId, watch, nextTick, onMounted, onUnmounted } from 'vue';

interface Category {
    id: number | string;
    name: string;
}

const props = withDefaults(
    defineProps<{
        categories: Category[];
        name?: string;
    }>(),
    {
        name: 'categories[]',
    },
);

const selectedCategories = defineModel<Category[]>({ default: () => [] });

const componentId = useId().replace(/:/g, '');
const listboxId = `listbox-${componentId}`;

const searchQuery = ref<string>('');
const focusedIndex = ref<number>(0);
const isExpanded = ref<boolean>(false);
const itemRefs = ref<HTMLElement[]>([]);
const containerRef = ref<HTMLElement | null>(null);

const setItemRef = (el: any, index: number) => {
    if (el) itemRefs.value[index] = el;
};

const isCategorySelected = (id: number | string): boolean => {
    return selectedCategories.value.some((selected) => selected.id === id);
};

const filteredCategories = computed<Category[]>(() => {
    return props.categories.filter((category) => category.name.toLowerCase().includes(searchQuery.value.toLowerCase()));
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

const handleClickOutside = (event: MouseEvent): void => {
    if (containerRef.value && !containerRef.value.contains(event.target as Node)) {
        isExpanded.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
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
</script>

<template>
    <div ref="containerRef" class="relative w-full max-w-sm font-sans">
        <input type="text" role="combobox" v-model="searchQuery" @input="handleInput" @keydown="handleKeydown" @focus="isExpanded = true" autocomplete="off" aria-autocomplete="list" :aria-expanded="isExpanded" :aria-controls="listboxId" :aria-activedescendant="activeDescendantId" placeholder="Wyszukaj i przełącz kategorię..." class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm transition outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" />

        <select :name="props.name" multiple class="hidden">
            <option v-for="category in selectedCategories" :key="'select-' + category.id" :value="category.id" selected>
                {{ category.name }}
            </option>
        </select>

        <ul v-if="isExpanded && filteredCategories.length > 0" :id="listboxId" role="listbox" class="absolute z-10 mt-1 max-h-40 w-full overflow-y-auto rounded-md border border-gray-200 bg-white p-0 shadow-lg">
            <li v-for="(category, index) in filteredCategories" :key="category.id" :ref="(el) => setItemRef(el, index)" :id="`${listboxId}-opt-${category.id}`" role="option" :aria-selected="isCategorySelected(category.id)" @mouseenter="focusedIndex = index" @click="toggleCategory(category)" :class="['flex cursor-pointer items-center justify-between px-3 py-2 text-sm transition-colors', index === focusedIndex ? 'bg-blue-600 text-white' : 'bg-white text-gray-800']">
                <span>{{ category.name }}</span>
                <span v-if="isCategorySelected(category.id)" :class="['text-sm font-bold', index === focusedIndex ? 'text-white' : 'text-green-500']"> ✓ </span>
            </li>
        </ul>

        <div class="mt-2.5 flex flex-wrap gap-1.5">
            <div v-for="category in selectedCategories" :key="category.id" class="inline-flex items-center gap-1.5 rounded-full border border-blue-100 bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700">
                <span>{{ category.name }}</span>
                <button type="button" @click="removeCategory(category.id)" class="rounded-full p-0.5 text-blue-500 transition-colors hover:bg-blue-100 hover:text-blue-800 focus:outline-none">
                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</template>
