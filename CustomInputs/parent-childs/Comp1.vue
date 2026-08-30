<!--
Przekazujesz props do Comp1, a Comp1 przekazuje go do dzieci
<template>
  <Comp1 sharedState="Dynamiczna wartość z samej góry aplikacji!">
    <Comp2 />
    <Comp3 />
  </Comp1>
</template>
-->

<script setup>
import { useSlots, computed, cloneVNode } from 'vue'
import Comp2 from './Comp2.vue' // Importujemy komponenty do porównania referencji
import Comp3 from './Comp3.vue'

// Przykładowe dane, które Comp1 chce przekazać do dzieci automatycznie
const props = defineProps({
	sharedState: {
		type: String,
		default: 'Dane z komponentu nadrzędnego!',
	},
})

// Slots
const slots = useSlots()

// Stan sterujący widocznością zawartości w comp3
const isComp3Visible = ref(false)

// Stan przechowujący dane wybranego z listy elementu
const selectedItem = ref(null)

// Funkcje sterujące stanem
const showComp3Section = () => {
	isComp3Visible.value = true
}
const hideComp3Section = () => {
	isComp3Visible.value = false
}

// Funkcja obsługuje zdarzenie, które przyszło z komponentu podrzędnego
const handleChildClick = (dataFromChild) => {
	console.log('Rodzic (comp1) odebrał dane z dziecka:', dataFromChild)
	// Wyświetl konponent
	showComp3Section()
}

// Odbierz event
const handleItemSelect = (clickedItem) => {
	selectedItem.value = clickedItem
	console.log('W Comp2 wybrano:', clickedItem.name)
}

const processedChildren = computed(() => {
	const defaultSlot = slots.default ? slots.default() : []
	let comp2VNode = null
	let comp3VNode = null

	// Iterujemy po dzieciach slotu
	defaultSlot.forEach((vnode) => {
		// Porównujemy bezpośrednio referencje obiektów (najbezpieczniejsza metoda)
		if (vnode.type === Comp2) {
			// Klonujemy vnode i wstrzykujemy propsy oraz zdarzenie onClick
			comp2VNode = cloneVNode(vnode, {
				parentData: props.sharedState,
				onClick: handleChildClick,
				onSelect: showComp3Section,
				onItemSelect: handleItemSelect,
				onKeydown: (event) => {
					if (event.key === 'ArrowDown') console.log('Wstrzyknięty keydown dla Comp2')
				},
				// // Inne zdarzenia
				// onMouseenter: handleMouseEnter,
				// onFocus: handleFocus,
				// // Własne zdarzenia wyemitowane przez Comp2 za pomocą defineEmits(['update', 'close'])
				// onUpdate: (newValue) => console.log('Zmieniono wartość na:', newValue),
				// onClose: () => console.log('Komponent Comp2 został zamknięty!'),
				// // Zdarzenia z wieloma wyrazami w nazwie (np. @custom-event-name) zamieniasz na CamelCase
				// onCustomEventName: handleCustomEvent,
			})
		}

		if (vnode.type === Comp3) {
			// Klonujemy vnode i przekazujemy np. klasę CSS lub inny props
			comp3VNode = cloneVNode(vnode, {
				parentData: props.sharedState,
				class: 'automatyczna-klasa-z-comp1',
				isVisible: isComp3Visible.value,
				onClose: hideComp3Section, // Reakcja na kliknięcie "Zamknij" w Comp3
				selectedItem: selectedItem.value, // Przekazujemy dane wybranego elementu do Comp3
				onKeydown: (event) => {
					if (event.key === 'ArrowDown') console.log('Wstrzyknięty keydown dla Comp3')
				},
			})
		}
	})

	return { comp2VNode, comp3VNode }
})
</script>

<template>
	<div class="comp1-container">
		<h2>Kontener główny</h2>

		<p>Selected item from Comp2: {{ selectedItem.value }}</p>

		<!-- Renderujemy sklonowany i zmodyfikowany Comp2 -->
		<div class="miejsce-na-comp2" v-if="processedChildren.comp2VNode">
			<component :is="processedChildren.comp2VNode" />
		</div>

		<hr />

		<!-- Renderujemy sklonowany i zmodyfikowany Comp3 -->
		<div class="miejsce-na-comp3" v-if="processedChildren.comp3VNode">
			<component :is="processedChildren.comp3VNode" />
		</div>
	</div>
</template>
