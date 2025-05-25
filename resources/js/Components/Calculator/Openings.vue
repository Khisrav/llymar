<script setup lang="ts">
import { useOpeningStore } from "../../Stores/openingsStore";
import Button from "../ui/button/Button.vue";
import Select from "../ui/select/Select.vue";
import { CirclePlusIcon, Trash2Icon } from "lucide-vue-next";
import SelectTrigger from "../ui/select/SelectTrigger.vue";
import SelectContent from "../ui/select/SelectContent.vue";
import SelectItem from "../ui/select/SelectItem.vue";
import SelectValue from "../ui/select/SelectValue.vue";
import Input from "../ui/input/Input.vue";
import { watch, computed } from "vue";
import { doorsSelectLimiter } from "../../Utils/doorsSelectLimiter";
import QuantitySelector from "../QuantitySelector.vue";
import { useItemsStore } from "../../Stores/itemsStore";
import Label from "../ui/label/Label.vue";
import NumberField from "../ui/number-field/NumberField.vue";
import NumberFieldContent from "../ui/number-field/NumberFieldContent.vue";
import NumberFieldDecrement from "../ui/number-field/NumberFieldDecrement.vue";
import NumberFieldInput from "../ui/number-field/NumberFieldInput.vue";
import NumberFieldIncrement from "../ui/number-field/NumberFieldIncrement.vue";
import type { Opening, OpeningType } from "../../lib/types";

interface GroupedOpening {
	opening: Opening;
	indices: number[];
	count: number;
}

const openingStore = useOpeningStore();
const itemsStore = useItemsStore();

const getOpeningKey = (opening: Opening): string => {
	return `${opening.type}-${opening.width}-${opening.height}-${opening.doors}`;
};

const groupedOpenings = computed((): GroupedOpening[] => {
	const groups = new Map<string, GroupedOpening>();

	openingStore.openings.forEach((opening: Opening, index: number) => {
		const key = getOpeningKey(opening);
		if (!groups.has(key)) {
			groups.set(key, {
				opening: { ...opening },
				indices: [],
				count: 0,
			});
		}
		const group = groups.get(key)!;
		group.indices.push(index);
		group.count++;
	});

	return Array.from(groups.values());
});

const updateGroupedOpening = <K extends keyof Opening>(group: GroupedOpening, field: K, value: Opening[K]): void => {
	group.indices.forEach((index: number) => {
		openingStore.openings[index][field] = value;
	});
	itemsStore.calculate();
};

const removeGroup = (group: GroupedOpening): void => {
	const sortedIndices = [...group.indices].sort((a: number, b: number) => b - a);
	sortedIndices.forEach((index: number) => {
		openingStore.removeOpening(index);
	});
};

const changeGroupQuantity = (group: GroupedOpening, newCount: number): void => {
	const currentCount = group.count;
	const difference = newCount - currentCount;

	if (difference > 0) {
		for (let i = 0; i < difference; i++) {
			openingStore.addOpening();
			const lastIndex = openingStore.openings.length - 1;
			Object.assign(openingStore.openings[lastIndex], group.opening);
		}
	} else if (difference < 0) { removeGroup(group) }
};

watch(
	() => openingStore.openings,
	(newOpenings: Opening[]) => {
		newOpenings.forEach((opening: Opening) => {
			watch(
				() => opening.type,
				(newType: keyof OpeningType, oldType?: keyof OpeningType) => {
					const { min, max, step } = doorsSelectLimiter(newType);

					if (opening.doors < min || opening.doors % step) {
						opening.doors = min;
					} else if (opening.doors > max) {
						opening.doors = max;
					}
				},
				{ immediate: true }
			);
		});
		itemsStore.calculate();
	},
	{ deep: true }
);
</script>

<template>
	<div class="border p-2 md:p-4 rounded-2xl bg-background">
		<h2 class="text-xl font-bold text-muted-foreground block">Проемы</h2>

		<div class="flex flex-col md:flex-row md:items-center gap-2 my-2">
			<Label>Высота по умолчанию: </Label>
			<div class="flex flex-row items-center gap-2">
				<Input type="number" v-model="openingStore.defaultHeight" placeholder="Высота для всех, мм" class="max-w-32 text-center" />
				<Button @click="openingStore.setDefaultHeightToAll()" variant="outline" class="h-10">Применить ко всем</Button>
			</div>
		</div>

		<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-2 md:gap-4">
			<div v-for="(group, groupIndex) in groupedOpenings" :key="groupIndex" class="bg-white dark:bg-slate-900 p-2 md:p-4 border rounded-xl hover:shadow-2xl hover:shadow-slate-100 dark:hover:shadow-slate-800 transition-all hover:z-10">
				<div class="flex justify-between items-center gap-2">
					<div class="flex-1 overflow-hidden">
						<Select :model-value="group.opening.type" @update:model-value="(value: keyof OpeningType) => updateGroupedOpening(group, 'type', value)" class="h-9 block text-sm">
							<SelectTrigger class="h-9 shadow-sm text-sm">
								<SelectValue placeholder="Выберите проем" class="text-sm" />
							</SelectTrigger>
							<SelectContent>
								<SelectItem v-for="(type, key) in openingStore.openingTypes" :key="key" :value="key" class="text-sm">
									{{ type }}
								</SelectItem>
							</SelectContent>
						</Select>
					</div>
					<Button v-if="groupedOpenings.length > 1" variant="outline" size="icon" @click="removeGroup(group)" class="shrink-0">
						<Trash2Icon class="size-4" />
					</Button>
				</div>

				<div class="grid grid-cols-1 md:grid-cols-1 gap-2">
					<div class="flex items-center">
						<img :src="openingStore.opening_images[group.opening.type]" class="w-full rounded-md mt-2 md:mt-4" />
					</div>
					<div>
						<label class="text-center my-1 text-muted-foreground text-xs md:text-sm block">Размеры (ШxВ) в мм:</label>
						<div class="flex items-center gap-2">
							<Input :model-value="group.opening.width" @update:model-value="(value: string) => updateGroupedOpening(group, 'width', Number(value))" type="number" step="100" max="12800" placeholder="Ширина" class="h-9 text-center" />
							<span class="inline-block text-sm">&#10005;</span>
							<Input :model-value="group.opening.height" @update:model-value="(value: string) => updateGroupedOpening(group, 'height', Number(value))" type="number" step="100" placeholder="Высота" class="h-9 text-center" />
						</div>

						<div class="gap-2 mt-2">
							<label class="text-center mb-1 text-muted-foreground text-xs md:text-sm block">Кол-во створок:</label>
							<QuantitySelector
								:model-value="group.opening.doors"
								@update:model-value="(value: number) => updateGroupedOpening(group, 'doors', value)"
								:min="doorsSelectLimiter(group.opening.type).min"
								:max="doorsSelectLimiter(group.opening.type).max"
								:step="doorsSelectLimiter(group.opening.type).step"
							/>

							<NumberField :model-value="group.count" @update:model-value="(value: number) => changeGroupQuantity(group, value)" id="quantity" :min="1" :max="99">
								<Label for="quantity" class="text-center my-1 text-muted-foreground text-xs md:text-sm block">Кол-во проемов</Label>
								<NumberFieldContent>
									<NumberFieldDecrement />
									<NumberFieldInput />
									<NumberFieldIncrement />
								</NumberFieldContent>
							</NumberField>
						</div>
					</div>
				</div>
			</div>

			<button
				class="p-2 md:p-4 border-4 border-dashed rounded-xl text-center flex items-center justify-center hover:border-black hover:dark:border-white transition-all text-gray-300 dark:text-gray-600 font-bold text-xl hover:text-black hover:dark:text-white"
				@click="openingStore.addOpening"
			>
				<div class="text-center flex flex-col items-center gap-2 p-6">
					<CirclePlusIcon class="size-12" />
					<span class="block">Добавить проем</span>
				</div>
			</button>
		</div>
	</div>
</template>