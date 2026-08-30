<script setup>
import { ref } from 'vue'

// Definiujemy emit success
const emit = defineEmits(['success'])

const items = ['Opcja A', 'Opcja B', 'Opcja C']
const focusedIndex = ref(0)

const handleKeydown = (event) => {
	if (event.key === 'ArrowDown') {
		event.preventDefault()
		focusedIndex.value = (focusedIndex.value + 1) % items.length
	} else if (event.key === 'ArrowUp') {
		event.preventDefault()
		focusedIndex.value = (focusedIndex.value - 1 + items.length) % items.length
	} else if (event.key === 'Enter') {
		event.preventDefault()
		console.log('Zatwierdzono w Comp2:', items[focusedIndex.value])
		// Wywołujemy emit, który wyzwie funkcję focusującą comp3
		emit('success')
	}
}
</script>

<template>
	<div class="lista-1" tabindex="0" @keydown="handleKeydown" style="border: 2px solid #3b82f6; padding: 15px; outline: none; border-radius: 8px">
		<h3 style="margin-top: 0; color: #3b82f6">1. Lista w Comp2 (Naciśnij Enter)</h3>
		<ul style="list-style: none; padding: 0; margin: 0">
			<li
				v-for="(item, index) in items"
				:key="item"
				:style="{
					padding: '8px',
					background: index === focusedIndex ? '#3b82f6' : '#eee',
					color: index === focusedIndex ? 'white' : 'black',
					margin: '4px 0',
					borderRadius: '4px',
				}">
				{{ item }}
			</li>
		</ul>
	</div>
</template>
