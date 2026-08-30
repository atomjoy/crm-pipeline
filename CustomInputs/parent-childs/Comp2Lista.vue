<script setup>
const emit = defineEmits(['click'])
const handleKeydown = (event) => {
	if (event.key === 'ArrowDown') {
		console.log('Naciśnięto strzałkę w dół w COMP2 🔵')
	}
}
</script>

<template>
	<!-- tabindex="0" sprawia, że ten komponent staje się "skupialny" -->
	<div class="lista-1" tabindex="0" @keydown="handleKeydown" style="border: 1px solid blue; padding: 10px">
		<h3>Lista w Comp2 (Kliknij tu, aby sterować klawiaturą)</h3>
		<ul>
			<li @click="emit('click', 'item2')">Element z Comp2</li>
		</ul>
	</div>
</template>
