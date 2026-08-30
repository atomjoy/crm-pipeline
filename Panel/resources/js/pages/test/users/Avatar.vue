<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';

const props = withDefaults(
    defineProps<{
        src?: string | null;
        name: string;
        size?: 'sm' | 'md' | 'lg';
        border?: boolean;
    }>(),
    {
        src: null,
        name: 'Guest',
        size: 'md',
        border: true,
    },
);

const isLoaded = ref<boolean>(false);
const isLoading = ref<boolean>(true);

const sizeClasses = {
    sm: 'h-8 w-8 text-xs',
    md: 'h-12 w-12 text-sm',
    lg: 'h-16 w-16 text-xl',
};

function checkSrcPresence() {
    if (!props.src) {
        isLoading.value = false;
        isLoaded.value = false;
    } else {
        isLoading.value = true;
        isLoaded.value = false;
    }
}

onMounted(() => {
    checkSrcPresence();
});

watch(
    () => props.src,
    () => {
        checkSrcPresence();
    },
);

function onImgLoad() {
    isLoading.value = false;
    isLoaded.value = true;
}

function onImgError() {
    isLoading.value = false;
    isLoaded.value = false;
}

const initials = computed<string>(() => {
    if (!props.name) return '?';
    const parts = props.name.trim().split(/\s+/);
    if (parts.length >= 2) {
        return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
    }
    return parts[0][0].toUpperCase();
});
</script>

<template>
    <div :class="['relative flex shrink-0 items-center justify-center overflow-hidden rounded-full font-semibold select-none', sizeClasses[size], border ? 'ring-2 ring-blue-100 ring-offset-0 dark:ring-offset-gray-900' : '']">
        <img v-if="src" v-show="isLoaded" @load="onImgLoad" @error="onImgError" :src="src" :alt="name" class="h-full w-full rounded-full object-cover" />
        <div v-if="!isLoaded" class="flex h-full w-full items-center justify-center rounded-full bg-blue-50 text-blue-600 dark:bg-blue-900 dark:text-blue-200">
            {{ initials }}
        </div>
        <div v-if="isLoading" class="absolute inset-0 animate-pulse bg-gray-200 dark:bg-gray-700" />
        <!-- Loader -->
        <div v-if="isLoading" class="absolute inset-0 flex items-center justify-center bg-black/5 backdrop-blur-[0.5px] dark:bg-black/20">
            <svg class="h-8 w-8 animate-spin rounded-full border-2 border-solid border-blue-500 border-t-transparent dark:border-blue-400 dark:border-t-transparent" viewBox="0 0 24 24"></svg>
        </div>
    </div>
</template>
