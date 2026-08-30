<script setup>
const emit = defineEmits(['click'])
const handleKeydown = (event) => {
	if (event.key === 'ArrowDown') {
		console.log('Naciśnięto strzałkę w dół w COMP3 🟢')
	}
}
</script>

<template>
	<div class="lista-2" tabindex="0" @keydown="handleKeydown" style="border: 1px solid green; padding: 10px; margin-top: 10px">
		<h3>Lista w Comp3 (Kliknij tu, aby sterować klawiaturą)</h3>
		<ul>
			<li @click="emit('click', 'item3')">Element z Comp3</li>
		</ul>
	</div>
</template>
