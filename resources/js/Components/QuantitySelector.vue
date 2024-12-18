<template>
	<div class="flex items-center justify-between gap-2 rounded-md">
		<Button variant="outline" :disabled="doors <= props.min" class="rounded-full" size="icon" @click="decrement">
			<MinusIcon />
		</Button>
		<Input v-model="doors" class="hidden" type="number" />
		<span>{{ doors }} шт.</span>
		<Button variant="outline" :disabled="doors >= props.max" class="rounded-full" size="icon" @click="increment">
			<PlusIcon />
		</Button>
	</div>
</template>

<script lang="ts" setup>
import { defineModel } from "vue";
import Button from "./ui/button/Button.vue";
import Input from "./ui/input/Input.vue";
import { MinusIcon, PlusIcon } from "lucide-vue-next";

const props = defineProps<{
	min: number;
	max: number;
	step: number;
}>();

const doors = defineModel<number>({ default: 0 });

const increment = () => {
	if (doors.value < props.max) {
		doors.value += props.step;
	}
};

const decrement = () => {
	if (doors.value > props.min) {
		doors.value -= props.step;
	}
};
</script>
