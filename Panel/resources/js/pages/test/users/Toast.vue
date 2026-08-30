<script setup>
import { ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const message = ref(null);
const type = ref('success'); // 'success' lub 'error'
let timeout = null;

const showToast = (msg, toastType) => {
    if (timeout) clearTimeout(timeout);
    message.value = msg;
    type.value = toastType;

    timeout = setTimeout(() => {
        message.value = null;
    }, 4000);
};

// Globalny watch nasłuchujący zmian we flash messages z Inertii
watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.message) {
            showToast(flash.message, 'success');
        } else if (flash?.error) {
            showToast(flash.error, 'error');
        }
    },
    { deep: true, immediate: true },
);
</script>

<template>
    <!-- MAGICZNY TELEPORT: Wyrywa Toaster z układu strony i przenosi na koniec body -->
    <Teleport to="body">
        <Transition enter-active-class="transform ease-out duration-300 transition" enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2" enter-to-class="translate-y-0 opacity-100 sm:translate-x-0" leave-active-class="transition ease-in duration-100" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="message" class="fixed right-5 bottom-5 z-50 flex w-full max-w-xs items-center rounded-xl border border-gray-100 bg-white p-4 text-gray-500 shadow-lg" role="alert">
                <!-- Ikona sukcesu (zielona) -->
                <div v-if="type === 'success'" class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-green-50 p-1.5 text-green-500">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"></path>
                    </svg>
                </div>

                <!-- Ikona błędu (czerwona) -->
                <div v-else class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-red-50 p-1.5 text-red-500">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>

                <!-- Treść komunikatu -->
                <div class="ms-3 text-sm leading-tight font-medium text-gray-700">
                    {{ message }}
                </div>

                <!-- Przycisk zamknięcia -->
                <button @click="message = null" type="button" class="-mx-1.5 -my-1.5 ms-auto inline-flex h-8 w-8 items-center justify-center rounded-lg bg-white p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-900">
                    <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </Transition>
    </Teleport>
</template>
