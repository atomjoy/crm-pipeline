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
    <div :id="id" role="listbox" tabindex="0" :aria-activedescendant="activeSubDescendantId" @keydown="handleKeydown" style="border: 2px solid #10b981; padding: 15px; outline: none; border-radius: 8px">
        <h3 style="margin-top: 0; color: #10b981">2. Szczegóły w Comp3</h3>

        <div v-if="isVisible && chosenData">
            <p style="margin: 0 0 10px 0; color: #047857">
                Aktywny element: <strong>{{ chosenData.name }}</strong>
            </p>

            <ul style="list-style: none; padding: 0; margin: 0">
                <li
                    v-for="(subItem, index) in subItems"
                    :key="subItem.id"
                    :id="subItem.ariaId"
                    role="option"
                    :aria-selected="index === focusedSubIndex"
                    :style="{
                        padding: '8px',
                        background: index === focusedSubIndex ? '#10b981' : '#eee',
                        color: index === focusedSubIndex ? 'white' : 'black',
                        margin: '4px 0',
                        borderRadius: '4px',
                    }"
                >
                    {{ subItem.label }}
                </li>
            </ul>
        </div>
        <div v-else>
            <p style="margin: 0; color: #999" role="status">Czeka na wybór z komponentu 2...</p>
        </div>
    </div>
</template>
