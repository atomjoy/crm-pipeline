<script setup>
import { useForm, Link } from '@inertiajs/vue3';

defineProps({
    roles: {
        type: Array,
        required: true,
    },
});

// Inicjalizacja formularza z pustymi wartościami domyślnymi
const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    roles: [],
});

// Wysłanie formularza metodą POST
const submit = () => {
    form.post('/test/users', {
        preserveScroll: true,
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <div class="w-full p-6">
        <!-- Nagłówek i powrót -->
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-800">Nowy użytkownik</h1>
            <Link href="/test/users" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-600 transition-colors hover:bg-gray-50">
                <svg xmlns="http://w3.org" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                Powrót do listy
            </Link>
        </div>

        <!-- Formularz -->
        <form @submit.prevent="submit" class="space-y-6 rounded-xl border border-gray-200 bg-white p-6 shadow">
            <!-- Pole: Imię i nazwisko -->
            <div>
                <label for="name" class="mb-1 block text-sm font-medium text-gray-700">Imię i nazwisko</label>
                <input id="name" v-model="form.name" type="text" placeholder="Jan Kowalski" class="w-full rounded-lg border px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" :class="{ 'border-red-500 focus:ring-red-500': form.errors.name, 'border-gray-300': !form.errors.name }" />
                <div v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</div>
            </div>

            <!-- Pole: Email -->
            <div>
                <label for="email" class="mb-1 block text-sm font-medium text-gray-700">Adres Email</label>
                <input id="email" v-model="form.email" type="email" placeholder="przyklad@domena.pl" class="w-full rounded-lg border px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" :class="{ 'border-red-500 focus:ring-red-500': form.errors.email, 'border-gray-300': !form.errors.email }" />
                <div v-if="form.errors.email" class="mt-1 text-xs text-red-500">{{ form.errors.email }}</div>
            </div>

            <!-- Sekcja Haseł -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <!-- Pole: Hasło -->
                <div>
                    <label for="password" class="mb-1 block text-sm font-medium text-gray-700">Hasło</label>
                    <input id="password" v-model="form.password" type="password" class="w-full rounded-lg border px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" :class="{ 'border-red-500 focus:ring-red-500': form.errors.password, 'border-gray-300': !form.errors.password }" />
                    <div v-if="form.errors.password" class="mt-1 text-xs text-red-500">{{ form.errors.password }}</div>
                </div>

                <!-- Pole: Potwierdź hasło -->
                <div>
                    <label for="password_confirmation" class="mb-1 block text-sm font-medium text-gray-700">Potwierdź hasło</label>
                    <input id="password_confirmation" v-model="form.password_confirmation" type="password" class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" />
                </div>
            </div>

            <!-- Przypisywanie ról (Spatie Permissions) -->
            <div class="border-t border-gray-100 pt-4">
                <label class="mb-2 block text-sm font-semibold text-gray-800">Przypisz role</label>

                <div class="grid grid-cols-2 gap-3">
                    <label v-for="role in roles" :key="role.id" class="flex cursor-pointer items-center gap-2 rounded-lg border p-3 transition-colors select-none hover:bg-gray-50" :class="{ 'border-blue-200 bg-blue-50/30': form.roles.includes(role.name), 'border-gray-200': !form.roles.includes(role.name) }">
                        <input type="checkbox" :value="role.name" v-model="form.roles" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                        <span class="text-sm font-medium text-gray-700">{{ role.name }}</span>
                    </label>
                </div>
                <div v-if="form.errors.roles" class="mt-1 text-xs text-red-500">{{ form.errors.roles }}</div>
            </div>

            <!-- Przyciski formularza -->
            <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-4">
                <Link href="/test/users" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-200"> Anuluj </Link>
                <button type="submit" :disabled="form.processing" class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50">
                    <svg v-if="form.processing" class="h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Utwórz użytkownika
                </button>
            </div>
        </form>
    </div>
</template>
