<script setup>
import { ref, watch, computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';
import Pagination from './Pagination.vue';
import UserActions from './Actions.vue';
import Avatar from './Avatar.vue';
import Toast from './Toast.vue';
import TableHeaderSortBtn from './TableHeaderSortBtn.vue';
import { PlusIcon, TrashIcon, ChevronsUpDown, ChevronUp, ChevronDown } from 'lucide-vue-next';

const props = defineProps({
    users: {
        type: Object,
        required: true,
    },
    roles: {
        type: Array,
        required: true,
    },
    facets: {
        type: Object, // Format: { admin: { id: 1, count: 3 }, editor: { id: 2, count: 12 } }
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({ search: '', role: '', field: 'id', direction: 'asc', per_page: '10' }),
    },
});

// Api points
const urlIndex = '/test/users';
const urlDelete = '/test/users/bulk-delete';

// Filtry wyszukiwania i sortowania (aktualizacja z serwera)
const search = ref(props.filters.search || '');
const selectedRole = ref(props.filters.role || '');
const sortField = ref(props.filters.field || 'id');
const sortDirection = ref(props.filters.direction || 'asc');
const perPage = ref(props.filters.per_page || '10');

// Tablica przechowująca ID zaznaczonych użytkowników
const selectedUsers = ref([]);

// Logika zaznacz wszystkich na bieżącej stronie
const isAllSelected = computed({
    get() {
        return props.users.data.length > 0 && selectedUsers.value.length === props.users.data.length;
    },
    set(value) {
        selectedUsers.value = value ? props.users.data.map((user) => user.id) : [];
    },
});

// Resetujemy zaznaczenie przy zmianie filtrów lub stron
router.on('finish', () => {
    selectedUsers.value = [];
});

// Czyste przekazywanie filtrów (wywoływane przy wyszukiwaniu/sortowaniu)
const updateFilters = () => {
    router.get(
        urlIndex,
        {
            search: search.value,
            role: selectedRole.value,
            field: sortField.value,
            direction: sortDirection.value,
            per_page: perPage.value,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

watch(
    [search, selectedRole],
    debounce(() => {
        updateFilters();
    }, 300),
);

// Obserwujemy zmianę w select i natychmiast odświeżamy listę (bez debounce, bo to kliknięcie)
watch(perPage, () => {
    updateFilters();
});

// Sortowanie wierszy tabeli
const sort = (field) => {
    if (sortField.value === field) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortField.value = field;
        sortDirection.value = 'asc';
    }
    updateFilters();
};

// Pojedyncze usuwanie z menu akcji
const deleteSingleUser = (user) => {
    if (confirm(`Czy na pewno chcesz usunąć użytkownika ${user.name}?`)) {
        router.delete(urlIndex + '/' + user.id);
    }
};

// Zbiorowa akcja (masowe usuwanie)
const deleteSelectedUsers = () => {
    if (confirm(`Czy na pewno chcesz usunąć ${selectedUsers.value.length} użytkowników?`)) {
        router.post(
            urlDelete,
            {
                ids: selectedUsers.value,
            },
            {
                onSuccess: () => {
                    selectedUsers.value = [];
                },
            },
        );
    }
};
</script>

<template>
    <div class="w-full p-6">
        <!-- NAGŁÓWEK STRONY (Zawsze dopasowany flexem) -->
        <div class="mb-6 flex items-center justify-between">
            <div class="text-2xl font-bold text-gray-800">Zarządzanie użytkownikami</div>
            <Link href="/test/users/create" class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-blue-700">
                <PlusIcon />
                Utwórz
            </Link>
        </div>

        <!-- FILTRY I AKCJE MASOWE -->
        <div class="mb-4 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <!-- Blok filtrów: na mobile kolumna (w-full), od wielkości md układ w rzędzie (w-auto) -->
            <div class="grid w-full grid-cols-1 gap-3 sm:grid-cols-2 md:flex md:w-auto md:items-center">
                <!-- Wyszukiwarka: w-full na mobile, md:w-64 dla stałej szerokości na desktopie -->
                <input v-model="search" type="text" placeholder="Szukaj użytkownika..." class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none md:w-64" />

                <!-- Filtr Roli (Spatie): w-full na mobile, md:w-48 na desktopie -->
                <select v-model="selectedRole" class="w-full cursor-pointer rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none md:w-48">
                    <option value="">Wszystkie role</option>
                    <option v-for="(roleData, roleName) in facets" :key="roleData.id" :value="roleName">{{ roleName }} ({{ roleData.count }})</option>
                </select>

                <!-- Filtr perPage z tekstem wewnątrz selecta -->
                <div class="w-full md:w-auto">
                    <select v-model="perPage" class="w-full cursor-pointer rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 focus:ring-2 focus:ring-blue-500 focus:outline-none md:w-auto">
                        <option value="1">1 na stronie</option>
                        <option value="3">3 na stronie</option>
                        <option value="5">5 na stronie</option>
                        <option value="10">10 na stronie</option>
                        <option value="25">25 na stronie</option>
                        <option value="50">50 na stronie</option>
                    </select>
                </div>
            </div>

            <!-- Przycisk Zbiorowych Akcji (Wyrównany do prawej strony na desktopie dzięki md:justify-end) -->
            <div v-if="selectedUsers.length > 0" class="animate-fade-in flex w-full items-center justify-between gap-3 rounded-lg bg-red-50 p-2 md:w-auto md:justify-end md:bg-transparent md:p-0">
                <span class="px-2 text-sm font-medium text-red-700 md:px-0 md:text-gray-600">Wybrano: {{ selectedUsers.length }}</span>
                <button @click="deleteSelectedUsers" class="inline-flex w-full items-center justify-center gap-1.5 rounded-lg bg-red-600 px-3 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-red-700 md:w-auto">
                    <TrashIcon />
                    Usuń zaznaczone
                </button>
            </div>
        </div>

        <!-- PASEK ZBIORCZEGO ZAZNACZANIA MOBILNEGO -->
        <div class="mb-4 flex items-center gap-3 rounded-lg border border-gray-200 bg-gray-100 p-3 md:hidden">
            <input v-model="isAllSelected" type="checkbox" id="selectAllMobile" class="h-4 w-4 cursor-pointer rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
            <label for="selectAllMobile" class="w-full cursor-pointer text-sm font-medium text-gray-700 select-none"> Zaznacz wszystkich na tej stronie </label>
        </div>

        <!-- WIDOK MOBILNY: UKŁAD KART (Widoczny poniżej ekranów 'md') -->
        <div class="mb-4 block space-y-3 md:hidden">
            <div v-for="user in users.data" :key="'mobile-' + user.id" class="relative flex items-start gap-3 rounded-xl border bg-white p-4 transition-colors" :class="selectedUsers.includes(user.id) ? 'border-blue-300 bg-blue-50/20' : 'border-gray-200'">
                <!-- Mobilny Checkbox wyboru wiersza -->
                <div class="pt-0.5">
                    <input v-model="selectedUsers" :value="user.id" type="checkbox" class="h-4 w-4 cursor-pointer rounded border-gray-300 text-blue-600" />
                </div>

                <!-- Informacje o użytkowniku -->
                <div class="min-w-0 flex-1 pr-8">
                    <div class="mb-1 flex items-center gap-2">
                        <span class="rounded border border-gray-100 bg-gray-50 px-1.5 py-0.5 font-mono text-xs text-gray-400">ID: {{ user.id }}</span>
                    </div>

                    <!-- Name -->
                    <h2 class="truncate font-bold text-gray-900">{{ user.name }}</h2>
                    <p class="mb-2.5 truncate text-sm text-gray-500">{{ user.email }}</p>

                    <!-- Role -->
                    <div class="flex flex-wrap gap-1">
                        <span v-for="role in user.roles" :key="role.id" class="rounded-md border border-gray-200 bg-gray-100 px-2 py-0.5 text-[13px] font-semibold text-gray-700">
                            {{ role.name }}
                        </span>
                        <span v-if="user.roles.length === 0" class="text-xs text-gray-400 italic">Brak ról</span>
                    </div>
                </div>

                <!-- Pozycjonowanie absolutne dla menu akcji w prawym górnym rogu karty -->
                <div class="absolute top-3 right-3">
                    <UserActions :user="user" @delete="deleteSingleUser" />
                </div>
            </div>

            <!-- Komunikat o braku wyników na mobile -->
            <div v-if="users.data.length === 0" class="rounded-xl border border-gray-200 bg-white p-8 text-center text-sm text-gray-400 shadow-sm">Nie dopasowano żadnych użytkowników.</div>
        </div>

        <!-- WIDOK DESKTOP: KLASYCZNA TABELA (Ukryta na telefonach, widoczna od 'md') -->
        <div class="mb-4 hidden overflow-hidden! rounded-xl! border border-gray-200 bg-white md:block">
            <table class="w-full border-collapse text-left">
                <thead class="border-b border-gray-200 bg-gray-50">
                    <tr>
                        <!-- Checkbox nagłówka -->
                        <th class="w-12 p-4 text-center">
                            <input v-model="isAllSelected" type="checkbox" class="h-4 w-4 cursor-pointer rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                        </th>

                        <!-- Kolumna Id -->
                        <th @click="sort('id')" class="cursor-pointer p-4 text-xs font-semibold tracking-wider text-gray-500 uppercase transition-colors select-none hover:bg-gray-100">
                            <div class="flex items-center gap-1.5">
                                ID
                                <TableHeaderSortBtn name="id" :sortField="sortField" :sortDirection="sortDirection" />
                            </div>
                        </th>

                        <!-- Kolumna Name -->
                        <th @click="sort('name')" class="cursor-pointer p-4 text-xs font-semibold tracking-wider text-gray-500 uppercase transition-colors select-none hover:bg-gray-100">
                            <div class="flex items-center gap-1.5">
                                Użytkownik
                                <TableHeaderSortBtn name="name" :sortField="sortField" :sortDirection="sortDirection" />
                            </div>
                        </th>

                        <!-- Kolumna Role -->
                        <th class="p-4 text-xs font-semibold tracking-wider text-gray-500 uppercase select-none">Role</th>

                        <!-- Kolumna Akcje -->
                        <th class="p-4 text-right text-xs font-semibold tracking-wider text-gray-500 uppercase select-none">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="user in users.data" :key="user.id" class="transition-colors hover:bg-gray-50/70" :class="{ 'bg-blue-50/40 hover:bg-blue-50/60': selectedUsers.includes(user.id) }">
                        <!-- Checkbox wiersza -->
                        <td class="p-4 text-center">
                            <input v-model="selectedUsers" :value="user.id" type="checkbox" class="h-4 w-4 cursor-pointer rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                        </td>

                        <!-- Id -->
                        <td class="p-4 font-mono text-sm text-gray-500">{{ user.id }}</td>

                        <!-- Name -->
                        <td class="p-4">
                            <div class="flex items-center gap-3">
                                <Avatar :src="'/image/' + user.avatar" :name="user.name" />
                                <div>
                                    <div class="text-sm font-semibold text-gray-900">{{ user.name }}</div>
                                    <div class="text-sm text-gray-500">{{ user.email }}</div>
                                </div>
                            </div>
                        </td>

                        <!-- Roles -->
                        <td class="p-4">
                            <div class="flex flex-wrap gap-1">
                                <span v-for="role in user.roles" :key="role.id" class="rounded-md border border-gray-200 bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-700">
                                    {{ role.name }}
                                </span>
                                <span v-if="user.roles.length === 0" class="text-xs text-gray-400 italic">Brak ról</span>
                            </div>
                        </td>

                        <!-- Komórka z komponentem akcji -->
                        <td class="p-4 text-right whitespace-nowrap">
                            <UserActions :user="user" @delete="deleteSingleUser" />
                        </td>
                    </tr>
                    <tr v-if="users.data.length === 0">
                        <td colspan="4" class="p-8 text-center text-sm text-gray-400">Nie dopasowano żadnych użytkowników do wybranych kryteriów.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Paginacja -->
        <Pagination :links="users.links" :meta="users" />
    </div>

    <!-- Powiadomieia tooltip -->
    <Toast />
</template>
