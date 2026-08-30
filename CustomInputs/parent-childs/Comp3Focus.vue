<script setup>
import { ref } from 'vue'

defineProps({
	isVisible: Boolean,
})

// Definiujemy emit backward do powrotu
const emit = defineEmits(['backward'])

const subItems = ['Szczegół 1', 'Szczegół 2']
const focusedSubIndex = ref(0)

const handleKeydown = (event) => {
	// 1. Sprawdzamy czy wciśnięto SHIFT + TAB
	if (event.shiftKey && event.key === 'Tab') {
		event.preventDefault() // Blokujemy ucieczkę focusu ze strony
		emit('backward') // Informujemy comp1, że chcemy wrócić do comp2
		return
	}

	// Standardowa obsługa strzałek
	if (event.key === 'ArrowDown') {
		event.preventDefault()
		focusedSubIndex.value = (focusedSubIndex.value + 1) % subItems.length
	} else if (event.key === 'ArrowUp') {
		event.preventDefault()
		focusedSubIndex.value = (focusedSubIndex.value - 1 + subItems.length) % subItems.length
	}
}
</script>

<template>
	<div class="lista-2" tabindex="0" @keydown="handleKeydown" style="border: 2px solid #10b981; padding: 15px; outline: none; border-radius: 8px">
		<h3 style="margin-top: 0; color: #10b981">2. Szczegóły w Comp3</h3>

		<div v-if="isVisible">
			<p style="font-size: 13px; color: #666">Naciśnij <b>Shift + Tab</b>, aby wrócić wyżej.</p>
			<ul style="list-style: none; padding: 0; margin: 0">
				<li
					v-for="(subItem, index) in subItems"
					:key="subItem"
					:style="{
						padding: '8px',
						background: index === focusedSubIndex ? '#10b981' : '#eee',
						color: index === focusedSubIndex ? 'white' : 'black',
						margin: '4px 0',
						borderRadius: '4px',
					}">
					{{ subItem }}
				</li>
			</ul>
		</div>
		<div v-else>
			<p style="margin: 0; color: #999">Czeka na akcję z komponentu 2...</p>
		</div>
	</div>
</template>
