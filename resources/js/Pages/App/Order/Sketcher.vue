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
import Button from "../../../Components/ui/button/Button.vue";
import { DownloadIcon, SaveIcon } from "lucide-vue-next";

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
	d: { start: 6, end: 6, default: 6, interval: 1 },
	e: { start: 100, end: 1100, default: 550, interval: 10 },
	f: { start: 5, end: 20, default: 14, interval: 1 },
	g: { start: 5, end: 20, default: 14, interval: 1 },
	i: { start: 5, end: 20, default: 14, interval: 1 },
};

// Create a reactive object to hold sketch_vars for each opening.
const sketch_vars = ref<Record<number, Record<string, number[]>>>({});

// Initialize sketch_vars for each opening using the default constraints.
openings.value.forEach((opening: Opening) => {
	sketch_vars.value[opening.id as number] = {
		a: [opening.a as number],
		b: [opening.b as number],
		c: [opening.c as number],
		d: [opening.d as number],
		e: [opening.e as number],
		f: [opening.f as number],
		g: [opening.g as number],
		i: [opening.i as number],
	};
});

// Set the default selected opening if available.
onMounted(() => {
	if (openings.value.length > 0) {
		selectedOpeningID.value = openings.value[0].id as number;
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
				<p class="text-xs text-muted-foreground">
					Выберите проем, параметры которого вы хотите изменить
				</p>

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
								<Label :for="`opening-${opening.id}`" class="w-full">
									{{ openingStore.openingTypes[opening.type] }}
								</Label>
							</div>
							<div class="text-sm text-muted-foreground flex justify-between items-center italic">
								<span>
									{{ opening.width }}мм <span class="text-xs">✕</span> {{ opening.height }}мм
								</span>
								<span>{{ opening.doors }} ств.</span>
							</div>
						</div>
					</div>
				</RadioGroup>
			</div>

			<Separator />

			<div class="grid grid-cols-1 md:grid-cols-12 gap-2 md:gap-4 mt-4">
				<div class="col-span-9">
					<img src="/assets/sketch-reference.png" class="w-full" />
				</div>

				<form class="col-span-9 md:col-span-3 p-4 rounded-lg border">
					<h3 class="text-xl mb-4 text-muted-foreground font-semibold">
						Параметры проема
					</h3>

					<!-- Use the sketch_vars for the currently selected opening -->
					<div 
						v-for="(value, key) in sketch_vars[selectedOpeningID]" 
						:key="key" 
						class="flex flex-col gap-2 pb-4"
					>
						<span class="font-medium">{{ key }}: {{ value[0] }}мм</span>
						<Slider 
							v-model="sketch_vars[selectedOpeningID][key]" 
							:default-value="[sketch_constraints[key].default]" 
							:min="sketch_constraints[key].start" 
							:max="sketch_constraints[key].end" 
							:step="sketch_constraints[key].interval" 
						/>
						<span class="text-muted-foreground text-xs">
							lorem ipsum dolor sit amet
						</span>
					</div>
					
					<div>
						<Button class="w-full mb-4" size="icon"><SaveIcon class="mr-2 h-4 w-4" /> Сохранить и закрыть</Button>
						<Button class="w-full" variant="outline"><DownloadIcon class="mr-2 h-4 w-4" /> Сохранить и скачать PDF</Button>
					</div>
				</form>
			</div>
		</div>
	</div>
</template>
