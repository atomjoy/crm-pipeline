<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { loadSchema } from '@/lib/utils/loadSchema';
import Terminal from '@/pages/website/terminal/Terminal.vue';
import GalleryList from '@/components/pages/website/galleries/GalleryList.vue';
import Comp1 from '@/test/Comp1.vue';
import Comp2 from '@/test/Comp2.vue';
import Comp3 from '@/test/Comp3.vue';
import CategorySelect from '@/test/CategorySelect.vue';
import CategoryItem from '@/test/CategoryItem.vue';
import Avatar from '@/test/Avatar.vue';

// import { useI18n } from 'vue-i18n';
// const { t, locale } = useI18n({ useScope: 'global' });

withDefaults(
    defineProps<{
        data: Array<any> | null;
    }>(),
    {
        data: null,
    },
);

onMounted(() => {
    window.onload = () => {
        loadSchema('/schema/website.json');
    };
});

interface Category {
    id: number | string;
    name: string;
}

const myCategoriesList = ref<Category[]>([
    { id: 1, name: 'Elektronika' },
    { id: 2, name: 'Moda i Odzież' },
    { id: 3, name: 'Dom i Ogród' },
    { id: 4, name: 'Książki i Rozrywka' },
    { id: 5, name: 'Sport i Turystyka' },
]);

// Tutaj automatycznie będą trafiać wybrane przez użytkownika elementy
const mySelectedItems = ref<Category[]>([
    { id: 1, name: 'Elektronika' },
    { id: 3, name: 'Dom i Ogród' },
]);

const mySelectedItems1 = ref<Category[]>([{ id: 1, name: 'Elektronika' }]);
</script>

<template>
    <Head :title="$t('title.home')">
        <meta name="description" :content="$t('description.home') ?? $t('AtomjoyCMS home page.')" />
    </Head>

    <div class="p-8">
        <!-- <Terminal /> -->
        <h1 class="mb-6 text-3xl">AtomjoyCMS</h1>
        <p>{{ $t('description.home') }}</p>

        <!-- RekaUi napraw comboselect w-full -->
        <!-- <ComboboxContent class="absolute z-10 mt-1 max-h-[--reka-combobox-content-available-height] w-[--reka-combobox-trigger-width] overflow-y-auto rounded-md border border-gray-200 bg-white shadow-lg"></ComboboxContent> -->
        <!-- Wyłączamy magię portali i Floating UI. Lista renderuje się jak zwykły HTML -->
        <!-- <ComboboxContent position="inline" class="absolute left-0 right-0 z-10 mt-1 max-h-40 overflow-y-auto rounded-md border border-gray-200 bg-white shadow-lg"> -->

        <!-- Wszystkie style wizualne i paddingi lądują tutaj -->
        <!-- Input jest "goły", wypełnia tylko przestrzeń kontenera -->
        <!-- <ComboboxAnchor class="flex w-full items-center rounded-md border border-gray-300 bg-white px-4 py-3 text-sm focus-within:border-blue-500 focus-within:ring-1 focus-within:ring-blue-500">
            <ComboboxInput v-model="searchQuery" placeholder="Wyszukaj..." class="w-full bg-transparent outline-none" />
        </ComboboxAnchor> -->

        <!-- Reka UI nie stworzy własnego taga, tylko "wstrzyknie" logikę do Twojego inputa z Tailwinda -->
        <!-- <ComboboxInput as-child v-model="searchQuery">
            <input type="text" placeholder="Wyszukaj..." class="w-full rounded-md border border-gray-300 px-4 py-3 text-sm outline-none" />
        </ComboboxInput> -->

        <h2>User Avatar</h2>
        <div class="flex flex-col gap-4 p-6">
            <!-- Scenariusz 1: Poprawny obrazek -->
            <div class="flex items-center gap-3">
                <Avatar src="default/logo.svg" name="Anna Nowak" size="lg" />
                <span>Poprawny link (Rozmiar LG)</span>
            </div>

            <!-- Scenariusz 2: Błędny URL (fallback do inicjałów Jan Kowalski -> JK) -->
            <div class="flex items-center gap-3">
                <Avatar src="https://nieistnieje-image-url.com" name="Jan Kowalski" size="md" />
                <span>Błędny link (Inicjały JK)</span>
            </div>

            <!-- Scenariusz 3: Brak przekazanego parametru src -->
            <div class="flex items-center gap-3">
                <Avatar name="Tomasz" size="sm" />
                <span>Brak linku (Rozmiar SM, inicjał T)</span>
            </div>
        </div>

        <h2>Wybór kategorii w formularzu Comp8</h2>
        <div style="--input-padding-block: 10px; --input-padding-inline: 15px">
            <CategorySelect v-model="mySelectedItems" name="my_categories[]" placeholder="Wybierz tagi...">
                <CategoryItem id="1" name="Elektronika" />
                <CategoryItem id="2" name="Moda i Odzież" />
                <CategoryItem id="3" name="Dom i Ogród" />
                <CategoryItem id="4" name="Książki" />
            </CategorySelect>
        </div>

        <div class="[--input-padding-block:10px] [--input-padding-inline:20px]">
            <CategorySelect v-model="mySelectedItems1" name="my_categories[]" placeholder="Wybierz tagi..." :limit="2">
                <CategoryItem id="1" name="Elektronika" />
                <CategoryItem id="2" name="Moda i Odzież" />
                <CategoryItem id="3" name="Dom i Ogród" />
                <CategoryItem id="4" name="Książki" />
            </CategorySelect>
        </div>

        <!-- <h2>Wybór kategorii w formularzu Comp7</h2>
        <CategorySelect :categories="myCategoriesList" v-model="mySelectedItems" />
        <div style="margin-top: 20px; font-family: monospace; background: #f4f4f4; padding: 10px; border-radius: 4px">
            <strong>Wybrane ID w Parent component:</strong>
            {{ mySelectedItems.map((item) => item.id) }}
        </div> -->

        <!-- <h2>Wybór kategorii w formularzu Comp4</h2>
        <CategorySelect name="category" /> -->

        <h2>Child components</h2>
        <Comp1 sharedState="Wiadomość testowa">
            <Comp2 id="item" :isExpanded="false" />
            <Comp3 id="item" :isVisible="false" :chosenData="null" />
        </Comp1>
    </div>

    <!-- <GalleryList /> -->
</template>
