<script setup>
import { ref } from 'vue'

const emit = defineEmits(['click'])

const items = [
	{ id: 1, name: 'Element Pierwszy' },
	{ id: 2, name: 'Element Drugi' },
	{ id: 3, name: 'Element Trzeci' },
]

// Indeks aktualnie podświetlonego elementu (-1 oznacza brak podświetlenia)
const focusedIndex = ref(-1)

// Obsługa klawiszy strzałek i Enter
const handleKeydown = (event) => {
	if (event.key === 'ArrowDown') {
		// Blokujemy domyślne przewijanie strony
		event.preventDefault()
		// Przechodzimy w dół, a po dotarciu do końca wracamy na początek list
		focusedIndex.value = (focusedIndex.value + 1) % items.length
	} else if (event.key === 'ArrowUp') {
		// Blokujemy domyślne przewijanie strony
		event.preventDefault()
		// Przechodzimy w górę, a po dotarciu do początku skaczemy na koniec listy
		focusedIndex.value = (focusedIndex.value - 1 + items.length) % items.length
	} else if (event.key === 'Enter') {
		// Jeśli jakiś element jest podświetlony, zatwierdzamy go Enterem
		if (focusedIndex.value >= 0 && focusedIndex.value < items.length) {
			emit('click', items[focusedIndex.value])
		}
	}
}

// Resetujemy podświetlenie, gdy użytkownik kliknie myszką poza listę
const resetFocus = () => {
	focusedIndex.value = -1
}
</script>

<template>
	<!-- tabindex="0" pozwala na kliknięcie tabem i sterowanie strzałkami -->
	<div class="lista-kontener" tabindex="0" @keydown="handleKeydown" @blur="resetFocus" style="outline: none; padding: 10px; border: 1px solid #ddd; display: inline-block">
		<h3 style="margin-top: 0">Wybierz strzałkami ⌨️ lub myszką 🖱️:</h3>

		<ul style="list-style: none; padding: 0; margin: 0">
			<li
				v-for="(item, index) in items"
				:key="item.id"
				@click="emit('click', item)"
				@mouseenter="focusedIndex = index"
				:style="{
					padding: '8px 12px',
					margin: '4px 0',
					cursor: 'pointer',
					// Dynamicznie zmieniamy tło dla podświetlonego elementu
					background: index === focusedIndex ? '#3b82f6' : '#eee',
					color: index === focusedIndex ? 'white' : 'black',
					borderRadius: '4px',
				}">
				{{ item.name }}
			</li>
		</ul>
	</div>
</template>
