<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import { EllipsisVerticalIcon, PenIcon, TrashIcon } from 'lucide-vue-next';

defineProps({
    user: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['delete']);
const isOpen = ref(false);
const dropdownRef = ref(null);

const toggleDropdown = () => {
    isOpen.value = !isOpen.value;
};

// Zamykanie menu po kliknięciu poza komponentem
const closeDropdown = (e) => {
    if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
        isOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', closeDropdown);
});

onUnmounted(() => {
    document.removeEventListener('click', closeDropdown);
});
</script>

<template>
    <div class="relative inline-block text-left" ref="dropdownRef">
        <button @click="toggleDropdown" class="flex items-center rounded-full p-1.5 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600 focus:outline-none" type="button">
            <span class="sr-only">Otwórz menu akcji</span>
            <EllipsisVerticalIcon />
        </button>

        <!-- Lista opcji menu (animowana) -->
        <div v-if="isOpen" class="ring-opacity-5 absolute right-0 z-50 mt-2 w-40 divide-y divide-gray-100 rounded-lg bg-white shadow-lg ring-1 ring-black focus:outline-none">
            <div class="py-1">
                <Link :href="`/test/users/${user.id}/edit`" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 transition-colors hover:bg-gray-50" @click="isOpen = false">
                    <PenIcon />
                    Edytuj
                </Link>
            </div>

            <div class="py-1">
                <button
                    @click="
                        isOpen = false;
                        emit('delete', user);
                    "
                    type="button"
                    class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-red-600 transition-colors hover:bg-red-50"
                >
                    <TrashIcon />
                    Usuń
                </button>
            </div>
        </div>
    </div>
</template>
