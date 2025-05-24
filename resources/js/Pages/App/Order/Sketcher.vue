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
import { CircleHelpIcon, DownloadIcon, EraserIcon, FileAxis3DIcon, FileType2Icon, PlusIcon, SaveIcon, TrashIcon } from "lucide-vue-next";
import { Item, ItemProperty, Opening, Order } from "../../../lib/types";
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
import Input from "../../../Components/ui/input/Input.vue";

//add item_properties to Item
interface ItemWithProperties extends Item {
	properties: ItemProperty[];
}

// Retrieve order and openings from Inertia props.
const order = ref(usePage().props.order as Order);
const openings = ref(usePage().props.openings as Opening[]);
const doorHandles = ref(usePage().props.door_handles as ItemWithProperties[]);
const doorHandlesProperties = ref(usePage().props.door_handles_properties as ItemProperty[]);
const can_access_dxf = ref(usePage().props.can_access_dxf as any);

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
	a: { start: 3, end: 25, default: 14, interval: 1 },
	b: { start: 14, end: 25, default: 17, interval: 1 },
	c: { start: 13, end: 13, default: 13, interval: 1 },
	d: { start: 8, end: 55, default: 6, interval: 1 },
	e: { start: 20, end: 80, default: 30, interval: 1 },
	// f: { start: 5, end: 20, default: 14, interval: 1 },
	g: { start: 30, end: 80, default: 55, interval: 1 },
	i: { start: 20, end: 1000, default: 550, interval: 1 },
	mp: { start: 100, end: 1000, default: 0, interval: 1 },
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
		// f: [opening.f as number],
		g: [opening.g as number],
		i: [opening.i as number],
		mr: [100],
	};
	selectedDoorHandles.value[opening.id as number] = opening.door_handle_item_id;
});

onMounted(() => {
	if (openings.value.length > 0) {
		selectedOpeningID.value = openings.value[0].id as number;
	}
});

const getMR = (item_id: number): number => {
	doorHandles.value.forEach((dh) => {
		if (dh.id === item_id) {
			// return dh.mr;
		}
	})
	return 100
}

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

const saveAndClose = (): Promise<boolean> => {
    return new Promise((resolve, reject) => {
        form.openings = combinedOpenings.value;
        form.post("/app/order/sketch/save", {
            preserveScroll: true,
            onSuccess: () => {
                toast("Сохранено");
                resolve(true); // Resolve the promise on successful save
            },
            onError: (errors: any) => { // Catch errors from form.post
                console.error("Failed to save sketch:", errors);
                toast.error("Ошибка сохранения. Пожалуйста, проверьте консоль.");
                reject(errors); // Reject the promise if save fails
            },
        });
    });
};

const downloadDXF = async () => {
    if (!can_access_dxf.value) { // Use .value for refs
        toast.warning("У вас нет доступа для скачивания DXF.");
        return;
    }
    
    try {
        // Wait for saveAndClose to complete successfully
        await saveAndClose(); 
        
        // If saveAndClose was successful, proceed to download DXF
        toast.info("Данные сохранены. Начинается загрузка DXF...");

        const response = await axios.post(
            `/orders/${order.value.id}/dxf`,
            {
                openings: combinedOpenings.value, // Send the latest data
                saveData: false, // Data is already saved by saveAndClose
            },
            {
                responseType: "blob",
            }
        );
        
        const fileBlob = new Blob([response.data], { type: "application/dxf" });
        const fileURL = URL.createObjectURL(fileBlob);

        const link = document.createElement("a");
        link.href = fileURL;
        link.setAttribute("download", `sketch_${order.value.id}.dxf`); // More specific filename
        document.body.appendChild(link);
        link.click();

        document.body.removeChild(link);
        URL.revokeObjectURL(fileURL);
        toast.success("DXF файл успешно загружен.");

    } catch (error) {
        console.error("Failed to save or download DXF:", error);
        if (axios.isAxiosError(error)) {
            toast.error("Ошибка при загрузке DXF файла.");
        }
    }
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

const addCustomDoorHandle = () => {
	doorHandles.value.push({
		id: -Date.now(),
		name: "Своя ручка",
		img: "",
		purchase_price: 0,
		discount: 0,
		item_properties: [
			{
				id: -Date.now() - 1000,
				item_id: -Date.now(),
				name: "d",
				value: "0",
			},
			{
				id: -Date.now() - 1001,
				item_id: -Date.now(),
				name: "g",
				value: "0",
			},
			{
				id: -Date.now() - 1002,
				item_id: -Date.now(),
				name: "i",
				value: "0",
			},
			{
				id: -Date.now() - 1003,
				item_id: -Date.now(),
				name: "MP",
				value: "0",
			}
		],
	});
}
</script>

<template>
	<Head title="Черталка" />
	<AuthenticatedHeaderLayout />

	<Toaster />

	<div class="container p-0 md:p-4">
		<div class="p-4 md:p-8 md:mt-8 md:border rounded-2xl bg-background">
			<h2 class="text-3xl font-semibold mb-6">Чертеж</h2>

			<div class="mb-4">
				<div class="flex items-center justify-between gap-4">
					<div>
						<h3 class="text-xl font-semibold text-muted-foreground">Проемы заказа</h3>
						<p class="text-xs text-muted-foreground">Выберите проем, параметры которого вы хотите изменить</p>
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
											<!-- <SelectLabel>Все ручки</SelectLabel> -->
											<SelectItem v-for="doorHandle in doorHandles" :key="doorHandle.id":value="doorHandle.id as any">{{ doorHandle.name }}</SelectItem>
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

				<div class="col-span-9 md:col-span-3 space-y-4">
					<div class="p-4 rounded-lg border">
						<div class="flex items-center justify-between gap-4">
							<h3 class="text-xl text-muted-foreground font-semibold">Свои ручки</h3>
							<Button variant="outline" size="icon" @click="addCustomDoorHandle">
								<PlusIcon class="h-4 w-4" />
							</Button>
						</div>
						<template v-for="doorHandle in doorHandles" :key="doorHandle.id">
							<div v-if="doorHandle.id < 0" class="flex flex-col gap-1.5 mt-2">
								<div class="flex items-center justify-between gap-2">
									<div class="flex-1 overflow-hidden">
										<Input v-model="doorHandle.name" class="h-9 text-sm" />
									</div>
									<Button variant="outline" size="icon">
										<TrashIcon class="h-4 w-4" />
									</Button>
								</div>
							</div>
						</template>
					</div>
				
					<div class="p-4 rounded-lg border">
						<div class="flex items-center justify-between mb-4 gap-4">
							<h3 class="text-xl text-muted-foreground font-semibold">Параметры проема</h3>
							<Button :variant="showSketchReference ? 'default' : 'outline'" size="icon" @click="showSketchReference = !showSketchReference">
								<CircleHelpIcon class="h-4 w-4" />
							</Button>
						</div>
	
						<template v-for="(value, key) in currentSketch" :key="key">
					        <div v-if="!['g', 'd', 'i', 'mp'].includes(key)"
					             class="flex flex-col gap-1.5 pb-3 mb-3 border-b border-gray-200 last:border-b-0 last:pb-0 last:mb-0">
					            <div class="">
					                <span class="font-medium">{{ key }}: </span>
					                <span class="font-medium">{{ parseInt(value[0] as any) }}мм</span>
					            </div>
					            <Slider v-model="currentSketch[key]" 
					                    :default-value="[sketch_constraints[key]?.default || 0]" 
					                    :min="sketch_constraints[key]?.start || 0" 
					                    :max="sketch_constraints[key]?.end || 100" 
					                    :step="sketch_constraints[key]?.interval || 1" 
					                    class="my-1"/>
					        </div>
					    </template>
					    
						<div class="flex items-center justify-between mb-4 gap-4">
							<h3 class="text-xl text-muted-foreground font-semibold">Параметры ручки</h3>
						</div>
	
						<template v-for="(value, key) in currentSketch" :key="key">
					        <div v-if="['g', 'd', 'i', 'mp'].includes(key)"
					             class="flex flex-col gap-1.5 pb-3 mb-3 border-b border-gray-200 last:border-b-0 last:pb-0 last:mb-0">
					            <div class="">
					                <span class="font-medium">{{ key }}: </span>
					                <span class="font-medium">{{ parseInt(value[0] as any) }}мм</span>
					            </div>
					            <Slider v-model="currentSketch[key]" 
					                    :default-value="[sketch_constraints[key]?.default || 0]" 
					                    :min="sketch_constraints[key]?.start || 0" 
					                    :max="sketch_constraints[key]?.end || 100" 
					                    :step="sketch_constraints[key]?.interval || 1" 
					                    class="my-1"/>
					        </div>
					    </template>
					    
					    <div class="flex flex-col gap-2">
							<!-- <Button type="button" class="w-full" size="icon" @click="saveAndClose"> <SaveIcon class="mr-2 h-4 w-4" /> Сохранить </Button> -->
							<div class="flex flex-row gap-2 justify-between items-center">
								<Button type="button" class="w-full" variant="outline" @click="saveAndDownload"> <FileType2Icon class="mr-2 h-4 w-4" /> PDF </Button>
								<Button v-if="can_access_dxf" type="button" class="w-full" variant="outline" @click="downloadDXF"> <FileAxis3DIcon class="mr-2 h-4 w-4" /> DXF </Button>
								<!-- доступ к DXF -->
								<!-- <Button v-else type="button" class="w-full" variant="outline" @click="toast('Нет доступа к DXF')"> <FileAxis3DIcon class="mr-2 h-4 w-4" /> DXF </Button> -->
							</div>
						</div>
					</div>
					
				</div>
			</div>
		</div>
	</div>
</template>
