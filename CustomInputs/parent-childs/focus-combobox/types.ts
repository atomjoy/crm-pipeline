// src/components/types.ts

// Interfejs opisujący pojedynczy element na głównej liście (Comp2)
export interface MainItem {
	id: string | number
	name: string
	description?: string
	ariaId?: string // Nowe pole na wygenerowany ID dla ARIA
}

// Interfejs opisujący element na liście szczegółów (Comp3)
export interface SubItem {
	id: string | number
	label: string
	ariaId?: string
}

// Sygnatury funkcji obsługujących zdarzenia (używane w cloneVNode)
export type SuccessHandler = () => Promise<void> | void
export type BackwardHandler = () => Promise<void> | void
