<!-- resources/js/Components/TableSkeletonRow.vue -->
<script setup lang="ts">
// Przyjmujemy liczbę kolumn z komponentu nadrzędnego,
// aby dopasować strukturę szkieletu do siatki tabeli
defineProps<{
    columnsCount: number;
}>();
</script>

<template>
    <tr class="animate-pulse bg-white">
        <td v-for="i in columnsCount" :key="i" class="px-3 py-4 whitespace-nowrap">
            <!-- Tworzymy paski o dynamicznych szerokościach przy użyciu reszty z dzielenia %, -->
            <!-- dzięki czemu szary szkielet wygląda bardzo naturalnie i asymetrycznie -->
            <div class="h-4 rounded bg-gray-200" :class="[i % 3 === 0 ? 'w-2/3' : '', i % 3 === 1 ? 'w-1/2' : '', i % 3 === 2 ? 'w-3/4' : '']"></div>
        </td>
    </tr>
</template>
