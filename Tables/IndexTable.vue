<!-- resources/js/Pages/Users/Index.vue -->
<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';

const props = defineProps({
    users: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const sortField = ref(props.filters.sort || 'id');
const sortDirection = ref(props.filters.direction || 'desc');

// Reaktywne wyszukiwanie z opóźnieniem (debounce), aby nie przeciążać bazy danych
watch(
    search,
    debounce((value) => {
        router.get(
            route('users.index'),
            { search: value, sort: sortField.value, direction: sortDirection.value },
            {
                preserveState: true,
                replace: true,
            },
        );
    }, 300),
);

// Funkcja obsługująca zmianę sortowania kolumn
const sortBy = (field) => {
    sortDirection.value = sortField.value === field && sortDirection.value === 'asc' ? 'desc' : 'asc';
    sortField.value = field;

    router.get(
        route('users.index'),
        { search: search.value, sort: field, direction: sortDirection.value },
        {
            preserveState: true,
        },
    );
};
</script>

<template>
    <div class="mx-auto max-w-7xl p-6">
        <!-- Wyszukiwarka -->
        <div class="mb-4">
            <input v-model="search" type="text" placeholder="Szukaj użytkowników..." class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
        </div>

        <!-- Tabela -->
        <div class="ring-opacity-5 overflow-hidden shadow ring-1 ring-black md:rounded-lg">
            <table class="min-w-full divide-y divide-gray-300">
                <thead class="bg-gray-50">
                    <tr>
                        <th @click="sortBy('name')" class="cursor-pointer px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                            Imię i nazwisko <span v-if="sortField === 'name'">{{ sortDirection === 'asc' ? '↑' : '↓' }}</span>
                        </th>
                        <th @click="sortBy('email')" class="cursor-pointer px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                            Email <span v-if="sortField === 'email'">{{ sortDirection === 'asc' ? '↑' : '↓' }}</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    <tr v-for="user in users.data" :key="user.id">
                        <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">{{ user.name }}</td>
                        <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">{{ user.email }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Paginacja -->
        <div class="mt-4 flex items-center justify-between">
            <component :is="link.url ? 'Link' : 'span'" v-for="link in users.links" :href="link.url" v-html="link.label" class="mx-0.5 rounded border px-3 py-1 text-sm" :class="{ 'bg-indigo-600 text-white': link.active, 'text-gray-400': !link.url }" />
        </div>
    </div>
</template>
