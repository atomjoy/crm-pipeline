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
const isUrlChecking = ref<boolean>(true);
const isImageDecoding = ref<boolean>(true);

// Końcowy status ładowania – loader kręci się, dopóki URL się sprawdza LUB obrazek fizycznie się pobiera
const isLoading = computed(() => isUrlChecking.value || (isImageValid.value && isImageDecoding.value));

const sizeClasses = {
    sm: 'h-8 w-8 text-xs',
    md: 'h-12 w-12 text-sm',
    lg: 'h-16 w-16 text-xl',
};

const spinnerSizeClasses = {
    sm: 'h-4 w-4 border-2',
    md: 'h-6 w-6 border-2',
    lg: 'h-8 w-8 border-3',
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
        isUrlChecking.value = false;
        isImageDecoding.value = false;
        return;
    }

    isUrlChecking.value = true;
    isImageDecoding.value = true;

    try {
        const response = await fetch(url, { method: 'HEAD' });
        const contentType = response.headers.get('content-type');

        if (response.ok && contentType && contentType.startsWith('image/')) {
            isImageValid.value = true;
        } else {
            isImageValid.value = false;
            isImageDecoding.value = false;
        }
    } catch {
        isImageValid.value = false;
        isImageDecoding.value = false;
    } finally {
        isUrlChecking.value = false;
    }
};

// Funkcja wywoływana, gdy przeglądarka całkowicie pobierze i wyrenderuje obrazek
const onImageActualLoad = () => {
    isImageDecoding.value = false;
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
    <div :class="['relative flex shrink-0 items-center justify-center overflow-hidden rounded-full bg-gray-100 font-semibold transition-all duration-200 select-none dark:bg-gray-800', sizeClasses[size], border ? 'ring-1 ring-blue-100 ring-offset-0 dark:ring-offset-gray-900' : '']">
        <!-- Zdjęcie: Renderuje się w tle, używamy v-show zamiast v-if, aby przeglądarka mogła wywołać @load pod maską -->
        <img v-show="isImageValid && !isImageDecoding" :src="src ?? undefined" :alt="name" @load="onImageActualLoad" class="h-full w-full rounded-full object-cover" />

        <!-- Inicjały: Pojawią się TYLKO wtedy, gdy wiemy na 100%, że URL jest błędny lub pusty (brak migania) -->
        <div v-if="!isUrlChecking && !isImageValid" class="flex h-full w-full items-center justify-center rounded-full bg-blue-50 text-blue-600 dark:bg-blue-900 dark:text-blue-200">
            {{ initials }}
        </div>

        <!-- Tło zastępcze (Placeholder): Czyste tło pod loaderem w trakcie ściągania zdjęcia, ukrywające inicjały -->
        <div v-if="isImageValid && isImageDecoding" class="absolute inset-0 bg-gray-100 dark:bg-gray-800" />

        <!-- Loader: Kręci się przez cały proces pobierania danych i obrazka -->
        <div v-if="isLoading" class="absolute inset-0 flex items-center justify-center bg-black/4 backdrop-blur-[0.5px] dark:bg-black/20">
            <svg :class="['animate-spin rounded-full border-solid border-blue-500 border-t-transparent dark:border-blue-400 dark:border-t-transparent', spinnerSizeClasses[size]]" viewBox="0 0 24 24"></svg>
        </div>
    </div>
</template>
