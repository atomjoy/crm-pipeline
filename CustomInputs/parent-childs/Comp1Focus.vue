<script setup>
import { useSlots, computed, cloneVNode, ref, nextTick } from 'vue'
import comp2 from './comp2.vue'
import comp3 from './comp3.vue'

const slots = useSlots()
const isComp3Visible = ref(false)

// Zmienne na referencje VNode obydwu dzieci
let comp2VNode = null
let comp3VNode = null

// Przenoszenie focusu do przodu (z comp2 do comp3)
const focusComp3 = async () => {
	isComp3Visible.value = true
	await nextTick()
	if (comp3VNode && comp3VNode.el) {
		comp3VNode.el.focus()
	}
}

// Przenoszenie focusu wstecz (z comp3 do comp2)
const focusComp2 = async () => {
	await nextTick()
	if (comp2VNode && comp2VNode.el) {
		comp2VNode.el.focus()
	}
}

const processedChildren = computed(() => {
	const defaultSlot = slots.default ? slots.default() : []

	defaultSlot.forEach((vnode) => {
		if (vnode.type === comp2) {
			comp2VNode = cloneVNode(vnode, {
				onSuccess: focusComp3,
			})
		}

		if (vnode.type === comp3) {
			comp3VNode = cloneVNode(vnode, {
				isVisible: isComp3Visible.value,
				// Wstrzykujemy funkcję powrotu do comp3
				onBackward: focusComp2,
			})
		}
	})

	return { comp2VNode, comp3VNode }
})
</script>

<template>
	<div class="comp1-container" style="max-width: 400px; margin: 20px auto; font-family: sans-serif">
		<div v-if="processedChildren.comp2VNode" style="margin-bottom: 20px">
			<component :is="processedChildren.comp2VNode" />
		</div>
		<div v-if="processedChildren.comp3VNode">
			<component :is="processedChildren.comp3VNode" />
		</div>
	</div>
</template>
