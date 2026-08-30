<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import { Link } from '@inertiajs/vue3';
import { EllipsisVerticalIcon, PenIcon, TrashIcon } from 'lucide-vue-next';

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['delete']);
const isOpen = ref(false);

// Referencje do przycisku oraz do samego menu
const buttonRef = ref(null);
const dropdownRef = ref(null);

// Style określające dynamiczną pozycję menu na ekranie
const dropdownStyles = ref({
    position: 'absolute',
    top: '0px',
    left: '0px',
});

// Funkcja obliczająca dokładną pozycję przycisku trzech kropek na ekranie
const calculatePosition = async () => {
    if (!buttonRef.value) return;

    await nextTick();
    const rect = buttonRef.value.getBoundingClientRect();

    // Obliczamy pozycję z uwzględnieniem przewijania strony (scroll)
    dropdownStyles.value = {
        position: 'absolute',
        top: `${rect.bottom + window.scrollY + 4}px`,
        // Odejmujemy 160px (szerokość menu w60), aby menu wyrównało się do prawej krawędzi kropek
        left: `${rect.right + window.scrollX - 160}px`,
    };
};

const toggleDropdown = async () => {
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        await calculatePosition();
    }
};

// Zamknij menu, jeśli kliknięto poza nim lub poza przyciskiem aktywującym
const closeDropdown = (e) => {
    if (isOpen.value && buttonRef.value && !buttonRef.value.contains(e.target) && dropdownRef.value && !dropdownRef.value.contains(e.target)) {
        isOpen.value = false;
    }
};

// Aktualizuj pozycję menu przy przewijaniu ekranu lub zmianie rozmiaru okna
const handleResizeOrScroll = () => {
    if (isOpen.value) {
        calculatePosition();
    }
};

onMounted(() => {
    document.addEventListener('click', closeDropdown);
    window.addEventListener('resize', handleResizeOrScroll);
    window.addEventListener('scroll', handleResizeOrScroll, true);
});

onUnmounted(() => {
    document.removeEventListener('click', closeDropdown);
    window.removeEventListener('resize', handleResizeOrScroll);
    window.removeEventListener('scroll', handleResizeOrScroll, true);
});
</script>

<template>
    <div class="inline-block text-left">
        <!-- Przycisk trzech kropek (baza do obliczenia pozycji) -->
        <button ref="buttonRef" @click="toggleDropdown" class="flex items-center rounded-full p-1.5 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600 focus:outline-none" type="button">
            <span class="sr-only">Otwórz menu akcji</span>
            <EllipsisVerticalIcon />
        </button>

        <!-- TELEPORT: Wyrywa to menu z tabeli i przenosi na koniec body -->
        <Teleport to="body">
            <div v-if="isOpen" ref="dropdownRef" :style="dropdownStyles" class="ring-opacity-5 z-9999 w-40 divide-y divide-gray-100 rounded-lg bg-white shadow-xl ring-1 ring-gray-300 focus:outline-none">
                <div class="py-1">
                    <Link :href="`/test/users/${user.id}/edit`" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 transition-colors hover:bg-gray-50" @click="isOpen = false">
                        <PenIcon />
                        Edytuj
                    </Link>
                </div>

                <div class="py-1">
                    <button
                        @click="
                            isOpen = false;
                            emit('delete', user);
                        "
                        type="button"
                        class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-red-600 transition-colors hover:bg-red-50"
                    >
                        <TrashIcon />
                        Usuń
                    </button>
                </div>
            </div>
        </Teleport>
    </div>
</template>
