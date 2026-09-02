<!-- resources/js/Components/TableActionsDropdown.vue -->
<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';

defineProps<{
    // Przyjmujemy tablicę akcji do wyrenderowania w liście
    items: Array<{
        label: string;
        onClick: () => void;
        danger?: boolean;
    }>;
}>();

const isOpen = ref(false);
const dropdownRef = ref<HTMLElement | null>(null);

const toggleDropdown = () => {
    isOpen.value = !isOpen.value;
};

// Zamykanie dropdownu po kliknięciu poza nim
const handleClickOutside = (event: MouseEvent) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target as Node)) {
        isOpen.value = false;
    }
};

onMounted(() => document.addEventListener('click', handleClickOutside));
onUnmounted(() => document.removeEventListener('click', handleClickOutside));
</script>

<template>
    <div ref="dropdownRef" class="relative inline-block text-left">
        <!-- Przycisk z trzema kropkami -->
        <button @click.stop="toggleDropdown" class="flex items-center rounded-full p-2 text-gray-400 transition-colors duration-150 hover:bg-gray-100 hover:text-gray-600 focus:outline-none" aria-label="Opcje">
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
            </svg>
        </button>

        <!-- Menu rozwijane (Tooltip/Dropdown) -->
        <div v-if="isOpen" class="ring-opacity-5 absolute right-0 z-50 mt-2 w-48 origin-top-right divide-y divide-gray-100 rounded-md bg-white shadow-lg ring-1 ring-black" @click.stop="isOpen = false">
            <div class="py-1">
                <button v-for="(item, index) in items" :key="index" @click="item.onClick" :class="[item.danger ? 'text-red-700 hover:bg-red-50' : 'text-gray-700 hover:bg-gray-100', 'group flex w-full items-center px-4 py-2 text-left text-sm transition-colors duration-150']">
                    {{ item.label }}
                </button>
            </div>
        </div>
    </div>
</template>
