<script setup lang="ts">
import { Head, usePage, useForm } from "@inertiajs/vue3";
import { ref, computed, onMounted } from "vue";
import AuthenticatedHeaderLayout from "../../../Layouts/AuthenticatedHeaderLayout.vue";
import Slider from "../../../Components/ui/slider/Slider.vue";
import Separator from "../../../Components/ui/separator/Separator.vue";
import RadioGroup from "../../../Components/ui/radio-group/RadioGroup.vue";
import RadioGroupItem from "../../../Components/ui/radio-group/RadioGroupItem.vue";
import Label from "../../../Components/ui/label/Label.vue";
import Button from "../../../Components/ui/button/Button.vue";
import { CircleHelpIcon, DownloadIcon, EraserIcon, FileAxis3DIcon, FileType2Icon, SaveIcon } from "lucide-vue-next";
import { Item, Opening, Order } from "../../../lib/types";
import { useOpeningStore } from "../../../Stores/openingsStore";
import { toast } from "vue-sonner";
import { Toaster } from "../../../Components/ui/sonner";
import axios from "axios";
import Select from "../../../Components/ui/select/Select.vue";
import SelectTrigger from "../../../Components/ui/select/SelectTrigger.vue";
import SelectValue from "../../../Components/ui/select/SelectValue.vue";
import SelectContent from "../../../Components/ui/select/SelectContent.vue";
import SelectItem from "../../../Components/ui/select/SelectItem.vue";
import DoorHandleSVG from "../../../Components/Sketcher/DoorHandleSVG.vue";
import SelectGroup from "../../../Components/ui/select/SelectGroup.vue";
import SelectLabel from "../../../Components/ui/select/SelectLabel.vue";

// Retrieve order and openings from Inertia props.
const order = ref(usePage().props.order as Order);
const openings = ref(usePage().props.openings as Opening[]);
const doorHandles = ref(usePage().props.door_handles as Item[]);
const allDoorHandles = ref(usePage().props.all_door_handles as Item[]);
const selectedOpeningID = ref<number>(0);
const openingStore = useOpeningStore();

// Define constraint interface and values.
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

// Initialize reactive sketch_vars for each opening.
const sketch_vars = ref<Record<number, Record<string, number[]>>>({});
const selectedDoorHandles = ref<Record<number, number | undefined>>({}); // Track selected door handles

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
	selectedDoorHandles.value[opening.id as number] = opening.door_handle_item_id;
});

onMounted(() => {
	if (openings.value.length > 0) {
		selectedOpeningID.value = openings.value[0].id as number;
	}
});

const currentSketch = computed(() => sketch_vars.value[selectedOpeningID.value] || {});
const currentOpening = computed(() => openings.value.find((opening) => opening.id === selectedOpeningID.value));

const getOpeningSketchDimensions = (doorIndex: number) => {
	if (!currentOpening.value) return { width: 0, height: 0 };

	let gap = currentOpening.value.type == "center" ? sketch_vars.value[selectedOpeningID.value].a[0] + sketch_vars.value[selectedOpeningID.value].b[0] + sketch_vars.value[selectedOpeningID.value].e[0] + sketch_vars.value[selectedOpeningID.value].g[0] + 3 : 130;
	let doorsGap = {
		start: sketch_vars.value[selectedOpeningID.value].e[0] + sketch_vars.value[selectedOpeningID.value].g[0],
		end: sketch_vars.value[selectedOpeningID.value].b[0],
	};
	let overlaps = currentOpening.value.doors / (currentOpening.value.type == "center" ? 2 : 1) - 1;
	let middle = Math.floor((overlaps * 13) / (currentOpening.value.doors / (currentOpening.value.type == "center" ? 2 : 1)));
	let edges = currentOpening.value.type == "center" ? Math.floor((overlaps * 13 - middle * (currentOpening.value.doors / 2 - 2)) / 2) : Math.floor((overlaps * 13 - middle * (currentOpening.value.doors - 2)) / 2);
	let y = currentOpening.value.width / (currentOpening.value.type == "center" ? 2 : 1) - gap;
	let z = y / (currentOpening.value.doors / (currentOpening.value.type == "center" ? 2 : 1));
	let shirinaStvorok = [];

	if (currentOpening.value.type == "center") {
		for (let i = 1; i <= currentOpening.value.doors / 2; i++) {
			let temp = z + (i == currentOpening.value.doors / 2 || i == 1 ? edges : middle);
			if (i == 1) {
				temp += doorsGap["end"];
			} else if (i == currentOpening.value.doors / 2) {
				temp += doorsGap["start"];
			}

			shirinaStvorok[i] = Math.floor(temp);
			shirinaStvorok[currentOpening.value.doors - i + 1] = Math.floor(temp);
		}
	} else if (currentOpening.value.type == "left" || currentOpening.value.type == "right") {
		for (let i = 1; i <= currentOpening.value.doors; i++) {
			let temp = z + (i == currentOpening.value.doors || i == 1 ? edges : middle);
			if (i == 1) {
				temp += doorsGap["end"];
			} else if (i == currentOpening.value.doors) {
				temp += doorsGap["start"];
			}

			shirinaStvorok[i] = Math.floor(temp);
		}
	}

	return {
		width: shirinaStvorok[doorIndex],
		height: currentOpening.value.height - 103,
	};
};

const combinedOpenings = computed(() => {
	return openings.value.map((opening) => {
		const updatedSketch = sketch_vars.value[opening.id as number] || {};
		const flatSketch: Record<string, number> = {};
		for (const key in updatedSketch) {
			flatSketch[key] = updatedSketch[key][0];
		}
		return { ...opening, ...flatSketch, door_handle_item_id: selectedDoorHandles.value[opening.id as number] };
	});
});

const form = useForm({
	openings: [] as Array<Record<string, any>>,
});

const saveAndClose = () => {
	form.openings = combinedOpenings.value;
	form.post("/app/order/sketch/save", {
		preserveScroll: true,
		onSuccess: () => toast("Сохранено"),
	});
};

// Download request: send all openings data.
const saveAndDownload = async () => {
	try {
		const response = await axios.post(
			"/app/order/sketch/download",
			{
				openings: combinedOpenings.value,
				saveData: true,
			},
			{
				responseType: "blob",
			}
		);

		const fileBlob = new Blob([response.data], { type: "application/pdf" });
		const fileURL = URL.createObjectURL(fileBlob);

		const link = document.createElement("a");
		link.href = fileURL;
		link.setAttribute("download", "sketch.pdf");
		document.body.appendChild(link);
		link.click();

		document.body.removeChild(link);
		URL.revokeObjectURL(fileURL);
	} catch (error) {
		console.error("Failed to download PDF:", error);
	}
};

const downloadDXF = async () => {
	try {
		const response = await axios.post(
			`/orders/${order.value.id}/dxf`,
			{
				openings: combinedOpenings.value,
				saveData: false,
			},
			{
				responseType: "blob",
			}
		);
		
		//download .dxf file
		const fileBlob = new Blob([response.data], { type: "application/dxf" });
		const fileURL = URL.createObjectURL(fileBlob);

		const link = document.createElement("a");
		link.href = fileURL;
		link.setAttribute("download", "sketch.dxf");
		document.body.appendChild(link);
		link.click();

		document.body.removeChild(link);
		URL.revokeObjectURL(fileURL);
	} catch (error) {
		console.error("Failed to download DXF:", error);
	}
};

const showSketchReference = ref(false);

const isDoorHandleSelected = (doorHandleId: number) => {
	return Object.values(selectedDoorHandles.value).includes(doorHandleId);
};

const selectDoorHandle = (openingId: number, doorHandleId: number) => {
	selectedDoorHandles.value[openingId] = doorHandleId;
};

const clearSelectedDoorHandles = (id?: number) => {
	if (!id) selectedDoorHandles.value = {};
	else {
		delete selectedDoorHandles.value[id];
	}
};
</script>

<template>
	<Head title="Черталка" />
	<AuthenticatedHeaderLayout />

	<Toaster />

	<div class="container p-0 md:p-4">
		<div class="p-4 md:p-8 md:mt-8 md:border rounded-2xl bg-background">
			<h2 class="text-3xl font-semibold mb-6">Чертеж</h2>

			<!-- Section to select an opening -->
			<div class="mb-4">
				<div class="flex items-center justify-between gap-4">
					<div>
						<h3 class="text-xl font-semibold text-muted-foreground">Проемы заказа</h3>
						<p class="text-xs text-muted-foreground">Выберите проем, параметры которого вы хотите изменить</p>
					</div>
					<div>
						<!-- <Button variant="outline" size="icon" @click="clearSelectedDoorHandles">
							<EraserIcon class="h-4 w-4" />
						</Button> -->
					</div>
				</div>

				<RadioGroup v-model="selectedOpeningID as any" class="grid grid-cols-1 md:grid-cols-4 gap-2 md:gap-4 mt-4">
					<div v-for="opening in openings" :key="opening.id" class="border rounded-lg p-2 md:p-4" :class="{ 'border-primary': opening.id === selectedOpeningID }">
						<div class="flex flex-col gap-2">
							<div class="flex items-center gap-2">
								<RadioGroupItem :value="opening.id as any" :id="`opening-${opening.id as number}`" />
								<Label :for="`opening-${opening.id}`" class="w-full">
									{{ openingStore.openingTypes[opening.type] }}
								</Label>
							</div>
							<div class="text-sm text-muted-foreground flex justify-between items-center italic">
								<span> {{ opening.width }}мм <span class="text-xs">✕</span> {{ opening.height }}мм </span>
								<span>{{ opening.doors }} ств.</span>
							</div>
						</div>

						<div class="mt-2 flex items-center justify-between gap-2">
							<div class="flex-1 overflow-hidden">
								<Select v-model="selectedDoorHandles[opening.id as number] as any" @update:model-value="selectDoorHandle(opening.id as number, $event as any)">
									<SelectTrigger>
										<SelectValue :placeholder="selectedDoorHandles[opening.id as number] ? doorHandles.find(dh => dh.id === selectedDoorHandles[opening.id as number])?.name : 'Выберите ручку'" />
									</SelectTrigger>

									<SelectContent class="max-w-xs sm:max-w-max">
										<SelectGroup>
											<SelectLabel>Ручки заказа</SelectLabel>
											<SelectItem v-for="doorHandle in doorHandles" :key="doorHandle.id" :value="doorHandle.id as any" :disabled="isDoorHandleSelected(doorHandle.id) && selectedDoorHandles[opening.id as number] !== doorHandle.id">
												{{ doorHandle.name }}
											</SelectItem>
										</SelectGroup>
										<SelectGroup>
											<SelectLabel>Все ручки</SelectLabel>
											<SelectItem
												v-for="doorHandle in allDoorHandles"
												:key="doorHandle.id"
												v-if="doorHandles.includes(doorHandle)"
												:value="doorHandle.id as any"
												:disabled="isDoorHandleSelected(doorHandle.id) && selectedDoorHandles[opening.id as number] !== doorHandle.id"
												>{{ doorHandle.name }}</SelectItem
											>
										</SelectGroup>
									</SelectContent>
								</Select>
							</div>
							<Button variant="outline" size="icon" @click="clearSelectedDoorHandles(opening.id)">
								<EraserIcon class="h-4 w-4" />
							</Button>
						</div>
					</div>
				</RadioGroup>
			</div>

			<Separator />

			<div class="grid grid-cols-1 md:grid-cols-12 gap-2 md:gap-4 mt-4">
				<!-- Sketch reference image -->
				<div class="col-span-9">
					<div v-show="showSketchReference" class="flex items-center justify-center mb-2">
						<img :src="`/assets/sketch-reference/${currentOpening?.type}.jpg`" class="w-full max-w-md" alt="Sketch reference" />
					</div>
					<div class="text-center">
						<div class="text-red-400">
							<span>Вид изнутри</span>
						</div>
						<div v-for="i in currentOpening?.doors" :key="i" class="mx-1 inline-block">
							<span class="text-xs">СТ{{ i }}</span>
							<div class="glass border border-blue-300 h-24 sm:h-36 relative col-span-1 aspect-[9/16]">
								<span
									class="text-xs absolute top-1/2 rotate-[-90deg]"
									:class="{
										'left-[-8px]': currentOpening?.type == 'right' || (currentOpening?.type == 'center' && i <= currentOpening?.doors / 2),
										'right-[-6px]': currentOpening?.type == 'left' || (currentOpening?.type == 'center' && i > currentOpening?.doors / 2),
									}"
									>{{ getOpeningSketchDimensions(i).height }}</span
								>
								<span style="position: absolute; top: 0; left: 50%; transform: translateX(-50%)" class="text-xs">{{ getOpeningSketchDimensions(currentOpening?.type == "left" ? currentOpening?.doors - i + 1 : i).width }}</span>

								<DoorHandleSVG v-if="currentOpening?.type == 'left' && i == 1" type="left" class="absolute top-1/2 left-1.5 transform -translate-y-1/2" />
								<DoorHandleSVG v-else-if="currentOpening?.type == 'right' && i == currentOpening?.doors" type="right" class="absolute top-1/2 right-1.5 transform -translate-y-1/2" />
								<DoorHandleSVG
									v-else-if="currentOpening?.type == 'center' && (i == currentOpening?.doors / 2 || i == currentOpening?.doors / 2 + 1)"
									type="right"
									class="absolute top-1/2 transform -translate-y-1/2"
									:class="{ 'right-1.5': i == currentOpening?.doors / 2, 'left-1.5': i == currentOpening?.doors / 2 + 1 }"
								/>
							</div>
						</div>
					</div>
				</div>

				<div class="col-span-9 md:col-span-3 p-4 rounded-lg border">
					<div class="flex items-center justify-between mb-4 gap-4">
						<h3 class="text-xl text-muted-foreground font-semibold">Параметры проема</h3>
						<Button :variant="showSketchReference ? 'default' : 'outline'" size="icon" @click="showSketchReference = !showSketchReference">
							<CircleHelpIcon class="h-4 w-4" />
						</Button>
					</div>

					<div v-for="(value, key) in currentSketch" :key="key" class="flex flex-col gap-2 pb-4">
						<span class="font-medium">{{ key }}: {{ value[0] }}мм</span>
						<Slider v-model="currentSketch[key]" :default-value="[sketch_constraints[key].default]" :min="sketch_constraints[key].start" :max="sketch_constraints[key].end" :step="sketch_constraints[key].interval" />
						<span class="text-muted-foreground text-xs"> lorem ipsum dolor sit amet </span>
					</div>

					<div class="flex flex-col gap-2">
						<Button type="button" class="w-full" size="icon" @click="saveAndClose"> <SaveIcon class="mr-2 h-4 w-4" /> Сохранить </Button>
						<div class="flex flex-row gap-2 justify-between items-center">
							<Button type="button" class="w-full" variant="outline" @click="saveAndDownload"> <FileType2Icon class="mr-2 h-4 w-4" /> PDF </Button>
							<Button type="button" class="w-full" variant="outline" @click="downloadDXF"> <FileAxis3DIcon class="mr-2 h-4 w-4" /> DXF </Button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>
