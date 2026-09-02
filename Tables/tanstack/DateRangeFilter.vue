<!-- resources/js/Components/DateRangeFilter.vue -->
<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { parseDate, getLocalTimeZone, today } from '@internationalized/date';
import type { DateRange } from 'radix-vue';
// Gotowe komponenty Shadcn Vue
import { RangeCalendar } from '@/components/ui/range-calendar';
import { Button } from '@/components/ui/button';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Calendar as CalendarIcon, X } from 'lucide-vue-next';

const props = defineProps<{
    startDate?: string;
    endDate?: string;
}>();

const emit = defineEmits<{
    (e: 'change', payload: { start_date?: string; end_date?: string }): void;
}>();

const { t } = useI18n({ useScope: 'global' });
const isOpen = ref(false);

// Formatowanie strefy czasowej i inicjalizacja stanu kalendarza
const tz = getLocalTimeZone();
const dateRange = ref<DateRange>({
    start: props.startDate ? parseDate(props.startDate) : undefined,
    end: props.endDate ? parseDate(props.endDate) : undefined,
});

// Dynamiczny tekst wyświetlany na przycisku
const displayText = computed(() => {
    if (dateRange.value.start && dateRange.value.end) {
        return `${dateRange.value.start.toString()} — ${dateRange.value.end.toString()}`;
    }
    if (dateRange.value.start) {
        return dateRange.value.start.toString();
    }
    return t('global.filters.date_placeholder', 'Wybierz zakres dat');
});

// Synchronizacja w przypadku zresetowania filtrów z zewnątrz (np. przyciskiem "Wyczyść")
watch(
    () => [props.startDate, props.endDate],
    ([newStart, newEnd]) => {
        dateRange.value = {
            start: newStart ? parseDate(newStart) : undefined,
            end: newEnd ? parseDate(newEnd) : undefined,
        };
    },
);

// Przekazanie wybranych dat do adresu URL
const applyFilter = () => {
    emit('change', {
        start_date: dateRange.value.start?.toString(),
        end_date: dateRange.value.end?.toString(),
    });
    isOpen.value = false;
};

// Czyszczenie filtra dat
const clearFilter = () => {
    dateRange.value = { start: undefined, end: undefined };
    emit('change', { start_date: undefined, end_date: undefined });
    isOpen.value = false;
};
</script>

<template>
    <div class="flex items-center space-x-2">
        <Popover v-model:open="isOpen">
            <PopoverTrigger as-child>
                <Button variant="outline" class="h-9 min-w-60 justify-start text-left text-xs font-normal" :class="!dateRange.start && 'text-muted-foreground'">
                    <CalendarIcon class="mr-2 h-4 w-4 text-gray-500" />
                    {{ displayText }}
                </Button>
            </PopoverTrigger>

            <PopoverContent class="w-auto p-4" align="start">
                <!-- Reużywalny RangeCalendar z Shadcn Vue z obsługą v-model -->
                <RangeCalendar v-model="dateRange" :placeholder="today(tz)" initial-focus class="rounded-md border-0" />

                <!-- Przyciski akcji (Zatwierdzenie chroni przed zbędnymi zapytaniami SQL w trakcie klikania) -->
                <div class="mt-2 flex items-center justify-end space-x-2 border-t pt-3">
                    <Button variant="ghost" size="sm" class="h-8 text-xs" @click="clearFilter">
                        {{ t('global.actions.clear', 'Reset') }}
                    </Button>
                    <Button size="sm" class="h-8 bg-indigo-600 text-xs text-white hover:bg-indigo-700" @click="applyFilter">
                        {{ t('global.actions.apply', 'Zastosuj') }}
                    </Button>
                </div>
            </PopoverContent>
        </Popover>

        <!-- Szybki przycisk 'X' usuwający filtr bez otwierania kalendarza -->
        <Button v-if="startDate || endDate" variant="ghost" class="h-9 w-9 p-0 hover:bg-gray-100" @click="clearFilter">
            <X class="h-4 w-4 text-gray-400" />
        </Button>
    </div>
</template>
