<script setup>
// Definiujemy props, który comp1 wstrzykuje automatycznie przy klonowaniu
defineProps({
	parentData: String,
	isVisible: Boolean,
	selectedItem: Object,
})

const emit = defineEmits(['close'])

// Obsługa klawisza Escape do zamykania
const handleGlobalKeydown = (event) => {
	if (event.key === 'Escape') {
		emit('close')
	}
}
</script>

<template>
	<div class="box-comp2" @keydown="handleGlobalKeydown">
		<h3>Komponent 3</h3>

		<!-- Wyświetli: "Dane z komponentu nadrzędnego!" -->
		<p>Otrzymane dane: {{ parentData }}</p>

		<p>
			🎉 Wybrałeś z listy w Comp2: <strong>{{ chosenData.name }}</strong> (ID: {{ chosenData.id }})
		</p>

		<!-- Zdarzenia default onClick tylko bez defineEmits() -->
		<button>Kliknij mnie (zdarzenie obsłuży Comp1 gdy nie będzie defineEmits())</button>

		<!-- Tutaj wpadnie wszystko, co wpiszesz między <Comp2> a </Comp2> -->
		<div class="zawartosc-slotu">
			<slot>Default slot</slot>
		</div>

		<!-- Ta część pojawi się dopiero po kliknięciu w Comp2 -->
		<Transition name="expand">
			<div v-if="isVisible" class="ukryta-czesc" style="background: #e6ffe6; padding: 10px">
				<p>🎉 Cześć! Pojawiłem się, bo kliknąłeś w Comp2!</p>
				<!-- Przycisk wywołujący emit zdefiniowany przez Comp1 -->
				<button @click="emit('close')" style="background: red; color: white; cursor: pointer">Zamknij / Ukryj</button>
			</div>
		</Transition>

		<div v-if="!isVisible">
			<p>Część zawartości jest ukryta...kliknij w Comp2</p>
		</div>
	</div>
</template>

<style scoped>
/* Klasy aktywne podczas trwania animacji */
.expand-enter-active,
.expand-leave-active {
	transition: all 0.4s ease-in-out;
	max-height: 200px; /* Potrzebne do płynnego rozwijania wysokości */
}

/* Stan początkowy przy otwieraniu / stan końcowy przy zamykaniu */
.expand-enter-from,
.expand-leave-to {
	opacity: 0;
	max-height: 0;
	transform: translateY(-10px);
}
</style>
