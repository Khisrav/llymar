<script setup lang="ts">
import { Head, usePage, useForm } from "@inertiajs/vue3";
import { onMounted } from "vue";
import AuthenticatedHeaderLayout from "../../../Layouts/AuthenticatedHeaderLayout.vue";
import { Slider } from "../../../Components/ui/slider";
import { Separator } from "../../../Components/ui/separator";
import { RadioGroup, RadioGroupItem } from "../../../Components/ui/radio-group";
import { Label } from "../../../Components/ui/label";
import { Button } from "../../../Components/ui/button";
import { CircleHelpIcon, EraserIcon, FileAxis3DIcon, FileType2Icon, PlusIcon, TrashIcon } from "lucide-vue-next";
import { Item, ItemProperty, Opening, Order } from "../../../lib/types";
import { useOpeningStore } from "../../../Stores/openingsStore";
import { useSketcherStore } from "../../../Stores/sketcherStore";
import { toast } from "vue-sonner";
import { Toaster } from "../../../Components/ui/sonner";
import { Select, SelectTrigger, SelectValue, SelectContent, SelectItem, SelectGroup } from "../../../Components/ui/select";
import DoorHandleSVG from "../../../Components/Sketcher/DoorHandleSVG.vue";
import { Input } from "../../../Components/ui/input";

interface ItemWithProperties extends Item {
	item_properties: ItemProperty[];
}

// Get initial data from page props
const pageData = usePage().props;
const order = pageData.order as Order;
const openings = pageData.openings as Opening[];
const doorHandles = pageData.door_handles as ItemWithProperties[];
const allDoorHandles = pageData.all_door_handles as ItemWithProperties[];
const can_access_dxf = pageData.can_access_dxf as boolean;

// Initialize stores
const openingStore = useOpeningStore();
const sketcherStore = useSketcherStore();

onMounted(() => {
	// Initialize the sketcher store with page data
	sketcherStore.initializeStore({
		order,
		openings,
		doorHandles,
		canAccessDxf: can_access_dxf
	});
});

// Form handling for saving
const form = useForm({
	openings: [] as Array<Record<string, any>>,
});

// Override the saveAndClose method to use the actual form submission
sketcherStore.saveAndClose = (): Promise<boolean> => {
	return new Promise((resolve, reject) => {
		form.openings = sketcherStore.combinedOpenings;
		form.post("/app/order/sketch/save", {
			preserveScroll: true,
			onSuccess: () => {
				toast("Сохранено");
				resolve(true);
			},
			onError: (errors: any) => {
				console.error("Failed to save sketch:", errors);
				toast.error("Ошибка сохранения. Пожалуйста, проверьте консоль.");
				reject(errors);
			},
		});
	});
};
</script>

<template>
	<Head title="Черталка" />
	<AuthenticatedHeaderLayout />

	<Toaster />

	<div class="container p-0 md:p-4">
		<div class="p-4 md:p-8 md:mt-8 mb-8 md:border rounded-2xl bg-background">
			<h2 class="text-3xl font-semibold mb-6">Чертеж</h2>
			<div class="grid grid-cols-1 md:grid-cols-12 gap-2 md:gap-4 mt-4">
				<div class="col-span-9">

					<div class="mb-4">
						<div class="flex items-center justify-between gap-4">
							<div>
								<h3 class="text-xl font-semibold text-muted-foreground">Проемы заказа</h3>
								<p class="text-xs text-muted-foreground">Выберите проем, параметры которого вы хотите изменить</p>
							</div>
						</div>

						<RadioGroup :model-value="String(sketcherStore.selectedOpeningID)" @update:model-value="(value) => sketcherStore.selectedOpeningID = Number(value)" class="grid grid-cols-1 md:grid-cols-4 gap-2 md:gap-4 mt-4">
							<div
								v-for="opening in sketcherStore.openings"
								:key="opening.id"
								class="border rounded-lg p-2 md:p-4"
								:class="{
									'border-primary': opening.id === sketcherStore.selectedOpeningID,
								}"
							>
								<div class="flex flex-col gap-2">
									<div class="flex items-center gap-2">
										<RadioGroupItem :value="String(opening.id)" :id="`opening-${opening.id}`" />
										<Label :for="`opening-${opening.id}`" class="w-full">
											{{ openingStore.openingTypes[opening.type] }}
										</Label>
									</div>
									<div class="text-sm text-muted-foreground flex justify-between items-center italic">
										<span>
											{{ opening.width }}мм
											<span class="text-xs">✕</span>
											{{ opening.height }}мм
										</span>
										<span>{{ opening.doors }} ств.</span>
									</div>
								</div>

								<div class="mt-2 flex items-center justify-between gap-2">
									<div class="flex-1 overflow-hidden">
										<Select
											:model-value="sketcherStore.selectedDoorHandles[opening.id || 0] ? String(sketcherStore.selectedDoorHandles[opening.id || 0]) : ''"
											@update:model-value="(value) => opening.id && sketcherStore.selectDoorHandle(opening.id, Number(value))"
										>
											<SelectTrigger>
												<SelectValue :placeholder="sketcherStore.selectedDoorHandles[opening.id || 0] ? sketcherStore.doorHandles.find(dh => dh.id === sketcherStore.selectedDoorHandles[opening.id || 0])?.name : 'Выберите ручку'" />
											</SelectTrigger>

											<SelectContent class="max-w-xs sm:max-w-max">
												<SelectGroup>
													<!-- <SelectLabel>Все ручки</SelectLabel> -->
													<SelectItem v-for="doorHandle in sketcherStore.doorHandles" :key="doorHandle.id" :value="String(doorHandle.id)">{{ doorHandle.name }}</SelectItem>
												</SelectGroup>
											</SelectContent>
										</Select>
									</div>
									<Button variant="outline" size="icon" @click="sketcherStore.clearSelectedDoorHandles(opening.id)">
										<EraserIcon class="h-4 w-4" />
									</Button>
								</div>
							</div>
						</RadioGroup>
					</div>

					<Separator class="my-4" />

					<div class="text-center">
						<div class="text-red-400">
							<span>Вид изнутри</span>
						</div>
						<div v-for="i in sketcherStore.currentOpening?.doors" :key="i" class="mx-1 inline-block">
							<span class="text-xs">СТ{{ i }}</span>
							<div class="glass border border-blue-300 h-24 sm:h-36 relative col-span-1 aspect-[9/16]">
								<span
									class="text-xs absolute top-1/2 rotate-[-90deg]"
									:class="{
										'left-[-8px]': sketcherStore.currentOpening?.type == 'right' || (sketcherStore.currentOpening?.type == 'center' && i <= sketcherStore.currentOpening?.doors / 2),
										'right-[-6px]': sketcherStore.currentOpening?.type == 'left' || (sketcherStore.currentOpening?.type == 'center' && i > sketcherStore.currentOpening?.doors / 2),
									}"
									>{{ sketcherStore.getOpeningSketchDimensions(i).height }}</span
								>
								<span style="position: absolute; top: 0; left: 50%; transform: translateX(-50%)" class="text-xs">{{ sketcherStore.getOpeningSketchDimensions(sketcherStore.currentOpening?.type == "left" ? sketcherStore.currentOpening?.doors - i + 1 : i).width }}</span>

								<DoorHandleSVG v-if="sketcherStore.currentOpening?.type == 'left' && i == 1" type="left" class="absolute top-1/2 left-1.5 transform -translate-y-1/2" />
								<DoorHandleSVG v-else-if="sketcherStore.currentOpening?.type == 'right' && i == sketcherStore.currentOpening?.doors" type="right" class="absolute top-1/2 right-1.5 transform -translate-y-1/2" />
								<DoorHandleSVG
									v-else-if="sketcherStore.currentOpening?.type == 'center' && (i == sketcherStore.currentOpening?.doors / 2 || i == sketcherStore.currentOpening?.doors / 2 + 1)"
									type="right"
									class="absolute top-1/2 transform -translate-y-1/2"
									:class="{
										'right-1.5': i == sketcherStore.currentOpening?.doors / 2,
										'left-1.5': i == sketcherStore.currentOpening?.doors / 2 + 1,
									}"
								/>
							</div>
						</div>
					</div>
					
					<div class="flex items-center justify-center mt-4">
						<img :src="`/assets/sketch-reference/${sketcherStore.currentOpening?.type}.jpg`" class="w-full max-w-md" alt="Sketch reference" />
					</div>
				</div>

				<div class="col-span-9 md:col-span-3 space-y-4">
					<div class="p-4 rounded-lg border">
						<div class="flex items-center justify-between gap-4">
							<h3 class="text-xl text-muted-foreground font-semibold">Свои ручки</h3>
							<Button variant="outline" size="icon" @click="sketcherStore.addCustomDoorHandle">
								<PlusIcon class="h-4 w-4" />
							</Button>
						</div>
						<template v-for="doorHandle in sketcherStore.doorHandles" :key="doorHandle.id">
							<div v-if="(doorHandle.id ?? 0) < 0" class="flex flex-col gap-1.5 mt-2">
								<div class="flex items-center justify-between gap-2">
									<div class="flex-1 overflow-hidden">
										<Input v-model="doorHandle.name" class="h-9 text-sm" />
									</div>
									<Button variant="outline" size="icon" @click="sketcherStore.removeCustomDoorHandle(doorHandle.id ?? 0)">
										<TrashIcon class="h-4 w-4" />
									</Button>
								</div>
							</div>
						</template>
					</div>

					<div class="p-4 rounded-lg border">
						<div class="flex items-center justify-between mb-4 gap-4">
							<h3 class="text-xl text-muted-foreground font-semibold">Параметры проема</h3>
						</div>

						<template v-for="(value, key) in sketcherStore.currentSketch" :key="key">
							<div v-if="!['g', 'd', 'i', 'mp'].includes(key)" class="flex flex-col gap-1.5 pb-3 mb-3 border-b border-gray-200 last:border-b-0 last:pb-0 last:mb-0">
								<div class="">
									<span class="font-medium">{{ key }}: </span>
									<span class="font-medium">{{ parseInt(String(value[0])) }}мм</span>
								</div>
								<Slider
									v-model="sketcherStore.currentSketch[key]"
									:default-value="[sketcherStore.sketch_constraints[key]?.default || 0]"
									:min="sketcherStore.sketch_constraints[key]?.start || 0"
									:max="sketcherStore.sketch_constraints[key]?.end || 100"
									:step="sketcherStore.sketch_constraints[key]?.interval || 1"
									class="my-1"
								/>
							</div>
						</template>

						<div class="flex items-center justify-between mb-4 gap-4">
							<h3 class="text-xl text-muted-foreground font-semibold">Параметры ручки</h3>
						</div>

						<template v-for="(value, key) in sketcherStore.currentSketch" :key="key">
							<div v-if="['g', 'd', 'i', 'mp'].includes(key)" class="flex flex-col gap-1.5 pb-3 mb-3 border-b border-gray-200 last:border-b-0 last:pb-0 last:mb-0">
								<div
									:class="{
										'opacity-50': sketcherStore.isSliderDisabled && (key == 'mp' || key == 'd'),
									}"
								>
									<span class="font-medium">{{ key }}: </span>
									<span class="font-medium">{{ parseInt(String(value[0])) }}мм</span>
								</div>
								<Slider
									v-model="sketcherStore.currentSketch[key]"
									:default-value="[sketcherStore.sketch_constraints[key]?.default || 0]"
									:min="sketcherStore.sketch_constraints[key]?.start || 0"
									:max="sketcherStore.sketch_constraints[key]?.end || 100"
									:step="sketcherStore.sketch_constraints[key]?.interval || 1"
									:disabled="sketcherStore.isSliderDisabled && (key == 'mp' || key == 'd')"
									class="my-1"
								/>
							</div>
						</template>

						<div class="flex flex-col gap-2">
							<!-- <Button type="button" class="w-full" size="icon" @click="saveAndClose"> <SaveIcon class="mr-2 h-4 w-4" /> Сохранить </Button> -->
							<div class="flex flex-row gap-2 justify-between items-center">
								<Button type="button" class="w-full" variant="outline" @click="sketcherStore.downloadPDF"> <FileType2Icon class="mr-2 h-4 w-4" /> PDF </Button>
								<Button v-if="sketcherStore.canAccessDxf" type="button" class="w-full" variant="outline" @click="sketcherStore.downloadDXF"> <FileAxis3DIcon class="mr-2 h-4 w-4" /> DXF </Button>
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