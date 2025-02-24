<script setup lang="ts">
import { Head, usePage } from "@inertiajs/vue3";
import AuthenticatedHeaderLayout from "../../../Layouts/AuthenticatedHeaderLayout.vue";
import { ref, onMounted } from "vue";
import Slider from "../../../Components/ui/slider/Slider.vue";
import { Opening, Order } from "../../../lib/types";
import { useOpeningStore } from "../../../Stores/openingsStore";
import Separator from "../../../Components/ui/separator/Separator.vue";
import RadioGroup from "../../../Components/ui/radio-group/RadioGroup.vue";
import RadioGroupItem from "../../../Components/ui/radio-group/RadioGroupItem.vue";
import Label from "../../../Components/ui/label/Label.vue";

const order = ref(usePage().props.order as Order);
const openings = ref(usePage().props.openings as Opening[]);
const selectedOpeningID = ref<number>(0);

const openingStore = useOpeningStore();

interface Constraint {
	start: number;
	end: number;
	default: number;
	interval: number;
}

const sketch_constraints: Record<string, Constraint> = {
	a: { start: 5, end: 20, default: 14, interval: 1 },
	b: { start: 17, end: 17, default: 17, interval: 1 },
	c: { start: 13, end: 13, default: 13, interval: 1 },
	j: { start: 6, end: 6, default: 6, interval: 1 },
	i: { start: 100, end: 1100, default: 550, interval: 10 },
	k: { start: 5, end: 20, default: 14, interval: 1 },
	g: { start: 5, end: 20, default: 14, interval: 1 },
	f: { start: 5, end: 20, default: 14, interval: 1 },
	d: { start: 5, end: 20, default: 14, interval: 1 },
};

const sketch_vars = ref({
	a: [sketch_constraints.a.default],
	b: [sketch_constraints.b.default],
	c: [sketch_constraints.c.default],
	j: [sketch_constraints.j.default],
	i: [sketch_constraints.i.default],
	k: [sketch_constraints.k.default],
	g: [sketch_constraints.g.default],
	f: [sketch_constraints.f.default],
	d: [sketch_constraints.d.default],
});

// Set default selected opening to the first one if available
onMounted(() => {
	if (openings.value.length > 0) {
		selectedOpeningID.value = openings.value[0].id;
	}
});
</script>

<template>
	<Head title="Черталка" />
	<AuthenticatedHeaderLayout />

	<div class="container p-0 md:p-4">
		<div class="p-4 md:p-8 md:mt-8 md:border rounded-2xl bg-background">
			<h2 class="text-3xl font-semibold mb-6">Чертальник</h2>

			<div class="mb-4">
				<h3 class="text-xl font-semibold text-muted-foreground">Проемы заказа</h3>
				<p class="text-xs text-muted-foreground">Выберите проем, параметры которого вы хотите изменить</p>

				<RadioGroup v-model="selectedOpeningID" class="grid grid-cols-1 md:grid-cols-4 gap-2 md:gap-4 mt-4">
					<div 
						v-for="opening in openings" 
						:key="opening.id" 
						class="border rounded-lg p-2 md:p-4"
						:class="{ 'border-primary': opening.id === selectedOpeningID }"
					>
						<div class="flex flex-col gap-2">
							<div class="flex items-center gap-2">
								<RadioGroupItem :value="opening.id" :id="`opening-${opening.id}`" />
								<Label :for="`opening-${opening.id}`" class="w-full">{{ openingStore.openingTypes[opening.type] }}</Label>
							</div>
							<div class="text-sm text-muted-foreground flex justify-between items-center italic">
								<span> {{ opening.width }}мм <span class="text-xs">✕</span> {{ opening.height }}мм </span>
								<span>{{ opening.doors }} ств.</span>
							</div>
						</div>
					</div>
				</RadioGroup>
			</div>

			<Separator />

			<div class="grid grid-cols-1 md:grid-cols-12 gap-2 md:gap-4 mt-4">
				<div class="col-span-9">Kartinka</div>

				<div class="col-span-9 md:col-span-3 p-4 rounded-lg border">
					<h3 class="text-xl mb-4 text-muted-foreground font-semibold">Параметры проема</h3>

					<div v-for="(value, key) in sketch_vars" :key="key" class="flex flex-col gap-2 pb-4">
						<span class="font-medium">{{ key }}: {{ value[0] }}мм</span>
						<Slider v-model="sketch_vars[key]" :default-value="[sketch_constraints[key].default]" :min="sketch_constraints[key].start" :max="sketch_constraints[key].end" :step="sketch_constraints[key].interval" />
						<span class="text-muted-foreground text-xs"> lorem ipsum dolor sit amet </span>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>
