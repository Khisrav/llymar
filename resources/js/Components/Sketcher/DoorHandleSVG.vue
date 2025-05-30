<script setup lang="ts">
const props = defineProps<{ type: string }>();
</script>

<script lang="ts">
export default {
	name: 'DoorHandleSVG'
}
</script>

<template>
	<svg width="6" height="64" viewBox="0 0 6 64" xmlns="http://www.w3.org/2000/svg">
		<rect x="0" y="0" width="6" height="64" fill="none" stroke="#40bcf7" />
	</svg>
</template>
