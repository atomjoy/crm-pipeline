<!-- resources/js/Components/RoleFacetFilter.vue -->
<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { RoleFacet } from './table';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Search } from 'lucide-vue-next';

const props = defineProps<{
    facets: RoleFacet[]; // Tablica ról z licznikami z Laravela
    selectedRoles: string[]; // Tablica aktualnie wybranych ról z adresu URL (?roles[]=...)
}>();

const emit = defineEmits<{
    (e: 'change', roles: string[]): void;
}>();

const { t } = useI18n({ useScope: 'global' });

// Lokalny stan dla checkboxów i wewnętrznej miniaturowej wyszukiwarki ról
const checkedRoles = ref<string[]>([...props.selectedRoles]);
const facetSearchQuery = ref('');

// Synchronizacja, jeśli filtry zostaną wyczyszczone z poziomu przycisku globalnego
watch(
    () => props.selectedRoles,
    (newRoles) => {
        checkedRoles.value = [...newRoles];
    },
);

// Wewnętrzne filtrowanie ról (gdy w bazie jest ich bardzo dużo)
const filteredFacets = computed(() => {
    if (!facetSearchQuery.value) return props.facets;
    return props.facets.filter((facet) => facet.name.toLowerCase().includes(facetSearchQuery.value.toLowerCase()));
});

// Zarządzanie zaznaczaniem ról
const handleRoleToggle = (roleName: string, isChecked: boolean) => {
    if (isChecked) {
        checkedRoles.value.push(roleName);
    } else {
        checkedRoles.value = checkedRoles.value.filter((r) => r !== roleName);
    }
    emit('change', checkedRoles.value);
};
</script>

<template>
    <div class="w-full max-w-xs space-y-3 rounded-xl border bg-white p-4 shadow-sm">
        <!-- Nagłówek i mały licznik zaznaczeń -->
        <div class="flex items-center justify-between border-b pb-2">
            <h3 class="text-sm font-semibold text-gray-900">
                {{ t('users.facets.role_title', 'Filtruj po roli') }}
            </h3>
            <Badge v-if="checkedRoles.length > 0" class="border border-indigo-200 bg-indigo-50 font-medium text-indigo-700 hover:bg-indigo-100">
                {{ checkedRoles.length }}
            </Badge>
        </div>

        <!-- Wewnętrzna wyszukiwarka ról (przydatna, gdy ról jest dużo) -->
        <div class="relative flex items-center">
            <Search class="absolute left-2.5 h-3.5 w-3.5 text-gray-400" />
            <Input v-model="facetSearchQuery" type="text" :placeholder="t('users.facets.search_placeholder', 'Szukaj roli...')" class="h-8 pl-8 text-xs focus-visible:ring-indigo-500" />
        </div>

        <!-- Lista checkboxów z rolami -->
        <div class="max-h-60 space-y-1 overflow-y-auto pr-1">
            <div v-for="role in filteredFacets" :key="role.id" @click="handleRoleToggle(role.name, !checkedRoles.includes(role.name))" class="flex cursor-pointer items-center justify-between space-x-2 rounded-md p-2 transition-colors duration-150 hover:bg-slate-50">
                <div class="flex items-center space-x-2.5" @click.stop>
                    <Checkbox :id="`role-${role.id}`" :checked="checkedRoles.includes(role.name)" @update:checked="(checked: any) => handleRoleToggle(role.name, !!checked)" class="data-[state=checked]:border-indigo-600 data-[state=checked]:bg-indigo-600" />
                    <Label :for="`role-${role.id}`" class="cursor-pointer text-sm font-medium text-gray-700 capitalize select-none">
                        {{ role.name }}
                    </Label>
                </div>

                <!-- Licznik użytkowników przypisanych do tej roli -->
                <span class="rounded-full bg-slate-100 px-2 py-0.5 font-mono text-[11px] font-semibold text-gray-500">
                    {{ role.count }}
                </span>
            </div>

            <!-- Stan pusty wewnętrznej wyszukiwarki -->
            <div v-if="filteredFacets.length === 0" class="py-4 text-center text-xs text-gray-400">Brak pasujących ról.</div>
        </div>
    </div>
</template>
