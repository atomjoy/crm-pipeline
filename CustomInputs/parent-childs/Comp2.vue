<script setup>
// Events
const emit = defineEmits(['select', 'item-select'])

// Definiujemy props, który Comp1 wstrzykuje automatycznie przy klonowaniu
defineProps({
	parentData: String,
})

// Przykładowa lista danych wewnątrz comp2
const items = [
	{ id: 1, name: 'Element Pierwszy' },
	{ id: 2, name: 'Element Drugi' },
	{ id: 3, name: 'Element Trzeci' },
]
</script>

<template>
	<div class="box-comp2">
		<h3>Komponent 2</h3>

		<!-- Wyświetli: "Dane z komponentu nadrzędnego!" -->
		<p>Otrzymane dane: {{ parentData }}</p>

		<!-- Standardowe zachowanie onClick nie działa po zdefiniowaniu defineEmits() -->
		<p>🚫 Kliknięcie we mnie nic nie robi istnieje (defineEmits)</p>

		<!-- Zdarzenia -->
		<button @click="emit('select')">🎯 Kliknij mnie, aby pokazać ukrytą część w Comp3! (zdarzenie obsłuży Comp1)</button>

		<!-- Tutaj wpadnie wszystko, co wpiszesz między <Comp1> a </Comp1> -->
		<div class="zawartosc-slotu">
			<slot>Default slot</slot>
		</div>

		<div class="lista-kontener">
			<h3>Wybierz element z listy:</h3>

			<ul>
				<!-- Dodajesz event do każdego elementu w pętli -->
				<li v-for="item in items" :key="item.id" @click="emit('item-select', item)" style="cursor: pointer; padding: 5px; margin: 5px 0; background: #eee">{{ item.name }} (Kliknij mnie)</li>
			</ul>
		</div>
	</div>
</template>
