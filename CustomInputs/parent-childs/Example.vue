<template>
	<Comp1 sharedState="Dane z Comp1">
		<!-- Sytuacja A: Pobiera 'Dane z Comp1' automatycznie -->
		<Comp2 />

		<!-- Sytuacja B: Nadpisuje automatyczne dane własnymi -->
		<Comp2 parentData="Własne, nadpisane dane tylko dla tego elementu" />

		<!-- Sytuacja C: Przekazuje zupełnie inny, własny props (np. id) -->
		<Comp2 id="unikalny-id" />
	</Comp1>
</template>
