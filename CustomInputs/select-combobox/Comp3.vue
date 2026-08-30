<script setup lang="ts">
import { ref, computed } from 'vue';
import type { MainItem, SubItem } from './types';

const props = defineProps<{
    id: string;
    isVisible: boolean;
    chosenData: MainItem | null;
}>();

const emit = defineEmits<{
    (e: 'backward'): void;
}>();

const subItems = computed<SubItem[]>(() => [
    { id: 's1', label: 'Szczegół Poziomu 1', ariaId: `${props.id}-sub-1` },
    { id: 's2', label: 'Szczegół Poziomu 2', ariaId: `${props.id}-sub-2` },
]);

const focusedSubIndex = ref<number>(0);

const activeSubDescendantId = computed<string | undefined>(() => {
    return subItems.value[focusedSubIndex.value]?.ariaId;
});

const handleKeydown = (event: KeyboardEvent): void => {
    if (event.shiftKey && event.key === 'Tab') {
        event.preventDefault();
        emit('backward');
        return;
    }

    if (event.key === 'ArrowDown') {
        event.preventDefault();
        focusedSubIndex.value = (focusedSubIndex.value + 1) % subItems.value.length;
    } else if (event.key === 'ArrowUp') {
        event.preventDefault();
        focusedSubIndex.value = (focusedSubIndex.value - 1 + subItems.value.length) % subItems.value.length;
    }
};
</script>

<template>
    <div :id="id" role="listbox" tabindex="0" :aria-activedescendant="activeSubDescendantId" @keydown="handleKeydown" class="w-full max-w-sm rounded-lg border-2 border-emerald-500 p-4 font-sans outline-none">
        <h3 class="mt-0 mb-3 text-base font-semibold text-emerald-500">2. Szczegóły w Comp3</h3>

        <div v-if="isVisible && chosenData">
            <p class="mt-0 mr-0 mb-2.5 ml-0 text-sm text-emerald-700">
                Aktywny element: <strong class="font-bold">{{ chosenData.name }}</strong>
            </p>

            <ul class="m-0 list-none p-0">
                <li v-for="(subItem, index) in subItems" :key="subItem.id" :id="subItem.ariaId" role="option" :aria-selected="index === focusedSubIndex" @mouseenter="focusedSubIndex = index" :class="['my-1 cursor-default rounded px-2 py-1.5 text-sm transition-colors', index === focusedSubIndex ? 'bg-emerald-500 text-white' : 'bg-gray-100 text-black']">
                    {{ subItem.label }}
                </li>
            </ul>
        </div>
        <div v-else>
            <p class="m-0 text-sm text-gray-400" role="status">Czeka na wybór z komponentu 2...</p>
        </div>
    </div>
</template>
