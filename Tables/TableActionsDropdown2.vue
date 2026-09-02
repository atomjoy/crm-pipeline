<!-- resources/js/Components/TableActionsDropdown.vue -->
<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { User } from './types/table';

// IMPORT Z ACTIONS: Spina się bezpośrednio z UserController w PHP
// import { UserController } from '@/actions/TestUserController';
// router.get(UserController.edit(props.user.id));

// IMPORT Z ROUTES: Szuka nazwy trasy 'users.edit'
// import { users } from '@/routes/table/users';
// router.get(users.edit(props.user.id));

// Przyjmujemy cały obiekt użytkownika z wiersza tabeli
const props = defineProps<{
    user: User;
    basePath: string;
}>();

const { t } = useI18n({ useScope: 'global' });
const isOpen = ref(false);
const dropdownRef = ref<HTMLElement | null>(null);

const toggleDropdown = () => {
    isOpen.value = !isOpen.value;
};

// AUTONOMICZNA LOGIKA AKCJI
const handleEdit = () => {
    // Przykład z Inertia: przekierowanie na stronę edycji
    // router.get(route('users.edit', props.user.id));
};

const handleDelete = () => {
    if (confirm(t('users.actions.confirm_delete', { name: props.user.name }))) {
        // Przykład z Inertia: wysłanie żądania DELETE do Laravel
        // router.delete(route('users.destroy', props.user.id), {
        //     preserveState: true,
        //     preserveScroll: true,
        // });
    }
};

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
        <button @click.stop="toggleDropdown" class="flex items-center rounded-full p-2 text-gray-400 transition-colors duration-150 hover:bg-gray-100 hover:text-gray-600 focus:outline-none">
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
            </svg>
        </button>

        <div v-if="isOpen" class="ring-opacity-5 absolute right-0 z-50 mt-2 w-48 origin-top-right divide-y divide-gray-100 rounded-md bg-white shadow-lg ring-1 ring-black" @click.stop="isOpen = false">
            <div class="py-1">
                <button @click="handleEdit" class="group flex w-full items-center px-4 py-2 text-left text-sm text-gray-700 transition-colors duration-150 hover:bg-gray-100">
                    {{ t('users.actions.edit') }}
                </button>
                <button @click="handleDelete" class="group flex w-full items-center px-4 py-2 text-left text-sm text-red-700 transition-colors duration-150 hover:bg-red-50">
                    {{ t('users.actions.delete', { name: user.name }) }}
                </button>
            </div>
        </div>
    </div>
</template>
