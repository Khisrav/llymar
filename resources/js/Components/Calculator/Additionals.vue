<script setup lang="ts">
import { useItemsStore } from "../../Stores/itemsStore";
import { currencyFormatter } from "../../Utils/currencyFormatter";
import QuantitySelector from "../QuantitySelector.vue";
import { ref, watch } from "vue";
import Select from "../ui/select/Select.vue";
import SelectTrigger from "../ui/select/SelectTrigger.vue";
import SelectValue from "../ui/select/SelectValue.vue";
import SelectItem from "../ui/select/SelectItem.vue";
import SelectContent from "../ui/select/SelectContent.vue";
import { getImageSource } from "../../Utils/getImageSource";
import Checkbox from '../ui/checkbox/Checkbox.vue';

const itemsStore = useItemsStore();
const selectedGlass = ref<any>(itemsStore.glasses.find(glass => glass.id === itemsStore.selectedGlassID) || null);

watch(() => itemsStore.selectedGlassID, (newID) => {
    selectedGlass.value = itemsStore.glasses.find(glass => glass.id === newID) || null;
});

const toggleSelection = (serviceId: number) => {
    if (itemsStore.selectedServicesID.includes(serviceId)) {
        itemsStore.selectedServicesID = itemsStore.selectedServicesID.filter(id => id !== serviceId);
    } else {
        itemsStore.selectedServicesID.push(serviceId);
    }
};
</script>

<template>
	<div class="border p-2 md:p-4 rounded-2xl">
		<div class="flex items-center mb-4">
			<h2 class="text-xl font-bold text-muted-foreground">Детали</h2>
		</div>

		<div class="grid grid-cols-1 md:grid-cols-2 gap-2 md:gap-4">
			<div class="p-2 md:p-4 bg-white dark:bg-slate-900 border rounded-xl hover:shadow-2xl hover:shadow-slate-100 dark:hover:shadow-slate-800 transition-all hover:z-10">
				<div>
					<Select v-model="itemsStore.selectedGlassID">
						<SelectTrigger>
							<SelectValue placeholder="Выберите стекло" />
						</SelectTrigger>
						<SelectContent class="max-w-xs sm:max-w-max">
							<SelectItem v-for="glass in itemsStore.glasses" :key="glass.id" :value="glass.id">{{ glass.name }}</SelectItem>
						</SelectContent>
					</Select>
				</div>
				<div class="mt-4 flex flex-row gap-2 md:gap-4 justify-between">
					<div class="basis-1/4 order-2 md:order-1">
						<img v-if="selectedGlass" :src="getImageSource(selectedGlass.img as string)" class="rounded-md w-full" />
					</div>
					<div class="flex-1 basis-3/4 order-1 md:order-2">
						<div class="flex flex-col justify-between h-full">
							<div class="flex justify-between items-center text-muted-foreground my-1 md:my-2">
								<span>Цена:</span>
								<span v-if="selectedGlass" class="font-bold text-primary">
									{{ currencyFormatter(selectedGlass.retail_price) }}/{{ selectedGlass.unit }}
								</span>
							</div>
							<div class="flex justify-between items-center text-muted-foreground my-1 md:my-2">
								<span>Кол-во:</span>
								<span v-if="selectedGlass" class="font-bold text-primary">
									{{ itemsStore.cartItems[itemsStore.selectedGlassID].quantity }} {{ selectedGlass.unit }}
								</span>
							</div>
							<div class="flex justify-between items-center text-muted-foreground my-1 md:my-2">
								<span>Итого:</span>
								<span v-if="selectedGlass" class="font-bold text-primary">
									{{ currencyFormatter(selectedGlass.retail_price * itemsStore.cartItems[itemsStore.selectedGlassID].quantity) }}
								</span>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div
				class="p-2 md:p-4 bg-white dark:bg-slate-900 border rounded-xl hover:shadow-2xl hover:shadow-slate-100 dark:hover:shadow-slate-800 transition-all hover:z-10"
			>
				<div v-if="itemsStore.services.length">
					<div class="flex items-center justify-between gap-2 md:gap-4 mb-2 md:mb-4">
						<span class="font-semibold">Выбранные услуги</span>
						<!-- <Button variant="outline" size="icon" class="">
							<PlusIcon class="size-4" />
						</Button> -->
					</div>
					<div v-for="service in itemsStore.services" :key="service.id" class="flex items-center justify-between gap-2 md:gap-4 py-1" :class="{'border-b': service !== itemsStore.services[itemsStore.services.length - 1]}">
						<p><span class="font-mono">{{ service.vendor_code ? service.vendor_code + ' - ' : '' }}</span>{{ service.name }}</p>
						<div class="flex items-center gap-2 md:gap-4">
							<span class="font-bold text-primary">{{ currencyFormatter(service.retail_price) }}</span>
							<Checkbox :checked="itemsStore.selectedServicesID.includes(service.id)" @click="toggleSelection(service.id)" />
						</div>
					</div>
				</div>
				<div v-else class="flex w-full h-full items-center justify-center">
					<p class="text-muted-foreground">Нет доступных услуг для выбора</p>
				</div>
			</div>
		</div>

		<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-2 md:gap-4 mt-4 transition-all duration-1000">
			<div
				v-for="item in itemsStore.additional_items"
				:key="item.vendor_code"
				class="flex flex-col justify-between p-2 md:p-4 bg-white dark:bg-slate-900 border rounded-xl hover:shadow-2xl hover:shadow-slate-100 dark:hover:shadow-slate-800 transition-all hover:z-10"
			>
				<p class="font-medium mb-2 md:mb-4">
					<span>{{ item.vendor_code ? item.vendor_code + " - " : "" }} {{ item.name }}</span>
				</p>
				<div class="flex flex-row gap-2 md:gap-4">
					<div class="basis-1/3">
						<img :src="getImageSource(item.img as string)" class="rounded-md w-full" />
					</div>
					<div class="basis-2/3 flex flex-col justify-between gap-2">
						<div class="flex justify-between items-center text-sm text-muted-foreground">
							<span>Цена:</span>
							<span class="font-bold text-primary">{{ currencyFormatter(item.retail_price) }}/{{ item.unit }}</span>
						</div>
						<div class="flex justify-between items-center text-sm text-muted-foreground">
							<span>Итого:</span>
							<span class="font-bold text-primary">{{ currencyFormatter(item.retail_price * itemsStore.cartItems[item.id].quantity) }}</span>
						</div>

						<QuantitySelector
							:min="0"
							:max="100"
							:step="1"
							v-model="itemsStore.cartItems[item.id].quantity"
						/>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>
