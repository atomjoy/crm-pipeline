<script setup lang="ts">
import { ref, computed } from 'vue'
import type { MainItem } from './types'

// Odbieramy propsy potrzebne do ARIA wstrzyknięte z comp1
const props = defineProps<{
	id: string
	controlledId: string
	isExpanded: boolean
}>()

const emit = defineEmits<{
	(e: 'success', item: MainItem): void
}>()

// Mapujemy elementy dodając im unikalne ID oparte o ID kontenera
const items = computed<MainItem[]>(() => [
	{ id: 1, name: 'Element Pierwszy', ariaId: `${props.id}-opt-1` },
	{ id: 2, name: 'Element Drugi', ariaId: `${props.id}-opt-2` },
	{ id: 3, name: 'Element Trzeci', ariaId: `${props.id}-opt-3` },
])

const focusedIndex = ref<number>(0)

// Wyliczamy ID aktualnie aktywnego elementu dla czytnika ekranu
const activeDescendantId = computed<string | undefined>(() => {
	return items.value[focusedIndex.value]?.ariaId
})

const handleKeydown = (event: KeyboardEvent): void => {
	if (event.key === 'ArrowDown') {
		event.preventDefault()
		focusedIndex.value = (focusedIndex.value + 1) % items.value.length
	} else if (event.key === 'ArrowUp') {
		event.preventDefault()
		focusedIndex.value = (focusedIndex.value - 1 + items.value.length) % items.value.length
	} else if (event.key === 'Enter') {
		event.preventDefault()
		emit('success', items.value[focusedIndex.value])
	}
}
</script>

<template>
	<div :id="id" role="listbox" tabindex="0" :aria-expanded="isExpanded" :aria-controls="controlledId" :aria-activedescendant="activeDescendantId" @keydown="handleKeydown" style="border: 2px solid #3b82f6; padding: 15px; outline: none; border-radius: 8px">
		<h3 style="margin-top: 0; color: #3b82f6">1. Lista w Comp2 (Accessible)</h3>
		<ul style="list-style: none; padding: 0; margin: 0">
			<li
				v-for="(item, index) in items"
				:key="item.id"
				:id="item.ariaId"
				role="option"
				:aria-selected="index === focusedIndex"
				:style="{
					padding: '8px',
					background: index === focusedIndex ? '#3b82f6' : '#eee',
					color: index === focusedIndex ? 'white' : 'black',
					margin: '4px 0',
					borderRadius: '4px',
				}">
				{{ item.name }}
			</li>
		</ul>
	</div>
</template>
