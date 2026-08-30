<script setup lang="ts">
import { ref, watch, onMounted, computed } from 'vue';

const props = withDefaults(
    defineProps<{
        src?: string | null;
        name: string;
        size?: 'sm' | 'md' | 'lg';
        border?: boolean;
    }>(),
    {
        src: null,
        size: 'md',
        border: true,
    },
);

const isImageValid = ref<boolean>(false);
const isLoading = ref<boolean>(true);

const sizeClasses = {
    sm: 'h-8 w-8 text-xs',
    md: 'h-12 w-12 text-sm',
    lg: 'h-16 w-16 text-xl',
};

const initials = computed<string>(() => {
    if (!props.name) return '?';
    const parts = props.name.trim().split(/\s+/);
    if (parts.length >= 2) {
        return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
    }
    return parts[0][0].toUpperCase();
});

const checkImageJsonUrl = async (url: string | null | undefined): Promise<void> => {
    if (!url) {
        isImageValid.value = false;
        isLoading.value = false;
        return;
    }

    isLoading.value = true;
    try {
        const response = await fetch(url, { method: 'HEAD' });
        const contentType = response.headers.get('content-type');

        if (response.ok && contentType && contentType.startsWith('image/')) {
            isImageValid.value = true;
        } else {
            isImageValid.value = false;
        }
    } catch {
        isImageValid.value = false;
    } finally {
        isLoading.value = false;
    }
};

onMounted(() => {
    checkImageJsonUrl(props.src);
});

watch(
    () => props.src,
    (newSrc) => {
        checkImageJsonUrl(newSrc);
    },
);
</script>

<template>
    <div :class="['relative flex shrink-0 items-center justify-center overflow-hidden rounded-full font-semibold select-none', sizeClasses[size], border ? 'ring-1 ring-gray-300 ring-offset-2 dark:ring-offset-gray-900' : '']">
        <div v-if="isLoading" class="absolute inset-0 animate-pulse bg-gray-200 dark:bg-gray-700" />

        <img v-if="!isLoading && isImageValid && src" :src="src" :alt="name" class="h-full w-full rounded-full object-cover" />

        <div v-if="!isLoading && !isImageValid" class="flex h-full w-full items-center justify-center rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200">
            {{ initials }}
        </div>
    </div>
</template>
