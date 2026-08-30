<!--
<template>
  <comp1>
    <comp2 />
    <comp3 />
  </comp1>
</template>
-->

<script setup>
import { useSlots, computed } from 'vue'
import Comp2 from './Comp2.vue' // Importujemy komponenty do porównania referencji
import Comp3 from './Comp3.vue'

const slots = useSlots()

// Filtrujemy dzieci wrzucone do domyślnego slotu
const subComponents = computed(() => {
	const defaultSlot = slots.default ? slots.default() : []

	// Szukamy konkretnych komponentów na podstawie ich nazwy (type.__name lub type.name)
	const comp2 = defaultSlot.find((vnode) => vnode.type.__name === 'Comp2')
	const comp3 = defaultSlot.find((vnode) => vnode.type.__name === 'Comp3')
	// Type
	// const comp2 = defaultSlot.find((vnode) => vnode.type === Comp2)
	// const comp3 = defaultSlot.find((vnode) => vnode.type === Comp3)

	return { comp2, comp3 }
})
</script>

<template>
	<div class="comp1-container">
		<h2>Nagłówek kontenera (comp1)</h2>

		<!-- Renderujemy comp2 w wybranym miejscu -->
		<div class="sekcja-dla-comp2" v-if="subComponents.comp2">
			<component :is="subComponents.comp2" />
		</div>

		<p>Treść rozdzielająca wewnątrz comp1...</p>

		<!-- Renderujemy comp3 w wybranym miejscu -->
		<div class="sekcja-dla-comp3" v-if="subComponents.comp3">
			<component :is="subComponents.comp3" />
		</div>
	</div>
</template>
