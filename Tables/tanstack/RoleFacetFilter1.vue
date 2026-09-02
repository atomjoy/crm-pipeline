<!-- resources/js/Components/RoleFacetFilter.vue -->
<script setup lang="ts">
import { ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { RoleFacet } from './table';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';

const props = defineProps<{
    facets: RoleFacet[]; // Dane z backendu (id, name, count)
    selectedRoles: string[]; // Aktualnie zaznaczone w URL role
}>();

const emit = defineEmits<{
    (e: 'change', roles: string[]): void;
}>();

const { t } = useI18n({ useScope: 'global' });

// Inicjalizacja lokalnego stanu zaznaczonych ról na podstawie propsów
const checkedRoles = ref<string[]>([...props.selectedRoles]);

// Obserwujemy zmiany z zewnątrz (np. gdy użytkownik kliknie "Wyczyść filtry")
watch(
    () => props.selectedRoles,
    (newRoles) => {
        checkedRoles.value = [...newRoles];
    },
);

// Funkcja obsługująca kliknięcie w checkbox ról
const handleCheckboxChange = (roleName: string, isChecked: boolean) => {
    if (isChecked) {
        checkedRoles.value.push(roleName);
    } else {
        checkedRoles.value = checkedRoles.value.filter((r) => r !== roleName);
    }
    // Emitujemy zaktualizowaną tablicę ról do Index.vue
    emit('change', checkedRoles.value);
};
</script>

<template>
    <div class="max-w-xs space-y-3 rounded-lg border bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between border-b pb-2">
            <h3 class="text-sm font-semibold text-gray-900">
                {{ t('users.facets.role_title', 'Filtruj po roli') }}
            </h3>
            <!-- Pokazuje mały licznik aktywnych filtrów ról -->
            <Badge v-if="checkedRoles.length > 0" variant="outline" class="border border-indigo-200 bg-indigo-50 px-1.5 py-0.5 text-xs text-indigo-700 hover:bg-indigo-100">
                {{ checkedRoles.length }}
            </Badge>
        </div>

        <div class="space-y-2">
            <div v-for="role in facets" :key="role.id" class="flex cursor-pointer items-center justify-between space-x-2 rounded-md p-2 transition-colors duration-150 hover:bg-gray-50" @click="handleCheckboxChange(role.name, !checkedRoles.includes(role.name))">
                <div class="flex items-center space-x-2" @click.stop>
                    <!-- Checkbox z Shadcn UI -->
                    <Checkbox :id="`role-${role.id}`" :checked="checkedRoles.includes(role.name)" @update:checked="(checked: any) => handleCheckboxChange(role.name, !!checked)" />
                    <Label :for="`role-${role.id}`" class="cursor-pointer text-sm font-medium text-gray-700 capitalize select-none">
                        {{ role.name }}
                    </Label>
                </div>

                <!-- Liczba użytkowników dla danej roli (Styl Muted Gray) -->
                <span class="rounded-full bg-gray-100 px-2 py-0.5 font-mono text-xs font-medium text-gray-400">
                    {{ role.count }}
                </span>
            </div>
        </div>
    </div>
</template>
