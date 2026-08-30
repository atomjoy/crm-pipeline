<!--
<template>
  <comp1 sharedState="Wiadomość testowa">
    <comp2 />
    <comp3 />
  </comp1>
</template>
-->

<!-- comp1.vue -->
<script setup lang="ts">
import { useSlots, computed, cloneVNode, ref, nextTick, useId, type VNode } from 'vue';
import comp2 from './Comp2.vue';
import comp3 from './Comp3.vue';
import type { MainItem } from './types';

const slots = useSlots();
const isComp3Visible = ref<boolean>(false);
const selectedItem = ref<MainItem | null>(null);

const baseId = useId();
const comp2Id = `listbox-1-${baseId}`;
const comp3Id = `listbox-2-${baseId}`;

let comp2VNode: VNode | null = null;
let comp3VNode: VNode | null = null;

const focusComp3 = async (item: MainItem): Promise<void> => {
    selectedItem.value = item;
    isComp3Visible.value = true;
    await nextTick();
    const el = comp3VNode?.el as HTMLElement | null;
    if (el) el.focus();
};

const focusComp2 = async (): Promise<void> => {
    await nextTick();
    if (comp2VNode && comp2VNode.el) {
        const rootEl = comp2VNode.el as HTMLElement;
        // Szukamy inputu wewnątrz comp2, aby tam z powrotem ustawić kursor myszy/focus
        const input = rootEl.querySelector('input');
        if (input) input.focus();
    }
};

const processedChildren = computed(() => {
    const defaultSlot: VNode[] = slots.default ? slots.default() : [];

    defaultSlot.forEach((vnode: VNode) => {
        if (vnode.type === comp2) {
            comp2VNode = cloneVNode(vnode, {
                id: comp2Id,
                controlledId: comp3Id,
                isExpanded: isComp3Visible.value,
                onSuccess: focusComp3,
            });
        }

        if (vnode.type === comp3) {
            comp3VNode = cloneVNode(vnode, {
                id: comp3Id,
                isVisible: isComp3Visible.value,
                chosenData: selectedItem.value,
                onBackward: focusComp2,
            });
        }
    });

    return { comp2VNode, comp3VNode };
});
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
