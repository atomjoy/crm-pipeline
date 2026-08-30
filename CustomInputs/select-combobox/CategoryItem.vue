<script setup lang="ts">
// Diagnozujemy i pobieramy dane z Props
defineProps<{
    id: number | string;
    name: string;
}>();
</script>

<template>
    <!-- Pozostaje pusty, ponieważ dane zbieramy bezpośrednio z VNode w komponencie nadrzędnym -->
</template>
