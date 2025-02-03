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
import Checkbox from "../ui/checkbox/Checkbox.vue";
import Button from "../ui/button/Button.vue";
import { PlusIcon } from "lucide-vue-next";

const itemsStore = useItemsStore();
const selectedGlass = ref<any>(itemsStore.glasses.find((glass) => glass.id === itemsStore.selectedGlassID) || null);

watch(() => itemsStore.selectedGlassID, (newID) => {
		selectedGlass.value = itemsStore.glasses.find((glass) => glass.id === newID) || null;
	}
);

const toggleSelection = (serviceId: number) => {
	if (itemsStore.selectedServicesID.includes(serviceId)) {
		itemsStore.selectedServicesID = itemsStore.selectedServicesID.filter((id) => id !== serviceId);
	} else {
		itemsStore.selectedServicesID.push(serviceId);
	}
};
</script>

<template>
	<div class="border p-2 md:p-4 rounded-2xl bg-background">
		<div class="flex items-center mb-4">
			<h2 class="text-xl font-bold text-muted-foreground">Детали</h2>
		</div>

		<div class="grid grid-cols-1 sm:grid-cols-2 gap-2 md:gap-8">
			<div
				class="p-2 md:p-4 bg-white dark:bg-slate-900 border rounded-xl hover:shadow-2xl hover:shadow-slate-100 dark:hover:shadow-slate-800 transition-all hover:z-10">
				<p class="font-semibold mb-2">Стекло</p>
				<div class="flex flex-col md:flex-row gap-2 md:gap-4 justify-between">
					<div class="flex-1 basis-2/3 overflow-hidden">
						<div>
							<Select v-model="itemsStore.selectedGlassID">
								<SelectTrigger>
									<SelectValue placeholder="Выберите стекло" />
								</SelectTrigger>
								<SelectContent class="max-w-xs sm:max-w-max">
									<SelectItem value="-1">Без стекла</SelectItem>
									<SelectItem v-for="glass in itemsStore.glasses" :key="glass.id" :value="glass.id">{{ glass.name }}</SelectItem>
								</SelectContent>
							</Select>
						</div>
						<div v-if="selectedGlass" class="flex flex-col justify-between mt-2">
							<div class="flex justify-between items-center text-muted-foreground">
								<span>Цена:</span>
								<span v-if="selectedGlass" class="font-bold text-accent-foreground"> {{
									currencyFormatter(itemsStore.itemPrice(itemsStore.selectedGlassID)) }}/{{
										selectedGlass.unit }} </span>
							</div>
							<div class="flex justify-between items-center text-muted-foreground my-1 md:my-2">
								<span>Кол-во:</span>
								<span v-if="selectedGlass" class="font-bold text-accent-foreground"> {{
									itemsStore.cartItems[itemsStore.selectedGlassID].quantity }} {{ selectedGlass.unit
									}} </span>
							</div>
							<div class="flex justify-between items-center text-muted-foreground">
								<span>Итого:</span>
								<span v-if="selectedGlass" class="font-bold text-accent-foreground">
									{{ currencyFormatter(itemsStore.itemPrice(itemsStore.selectedGlassID) *
										itemsStore.cartItems[itemsStore.selectedGlassID].quantity) }}
								</span>
							</div>
						</div>
					</div>
					<div v-if="selectedGlass" class="basis-1/3">
						<img :src="getImageSource(selectedGlass.img as string)" class="rounded-md w-full" />
					</div>
				</div>
			</div>

			<div
				class="p-2 md:p-4 bg-white dark:bg-slate-900 border rounded-xl hover:shadow-2xl hover:shadow-slate-100 dark:hover:shadow-slate-800 transition-all hover:z-10">
				<div v-if="itemsStore.services.length" class="flex flex-col gap-2">
					<div>
						<span class="font-semibold">Услуги</span>
					</div>
					<div v-for="service in itemsStore.services" :key="service.id">
						<div class="flex flex-row gap-4 justify-between border rounded-lg p-2 w-full text-sm">
							<div>
								<p class="font-semibold">
									<span class="font-mono">{{ service.vendor_code ? service.vendor_code + " - " : "" }}</span>{{ service.name }}
								</p>
								<p class="text-muted-foreground text-xs">{{ currencyFormatter(itemsStore.itemPrice(service.id)) }}/{{ service.unit }}</p>
							</div>
							<div class="flex flex-row gap-4 items-center">
								<div class="text-right" v-if="itemsStore.selectedServicesID.includes(service.id)">
									<p class="font-semibold text-sm">{{ currencyFormatter(itemsStore.itemPrice(service.id) * (itemsStore.cartItems[service.id] ? itemsStore.cartItems[service.id].quantity : 0)) }}</p>
									<p class="font-semibold text-xs text-muted-foreground">{{ itemsStore.cartItems[service.id] ? itemsStore.cartItems[service.id].quantity : 0 }} {{ service.unit }}</p>
								</div>
								<Checkbox :checked="itemsStore.selectedServicesID.includes(service.id)" @click="toggleSelection(service.id)" />
							</div>
						</div>
					</div>
				</div>
				<div v-else class="flex w-full h-full items-center justify-center">
					<p class="text-accent-foreground">Нет доступных услуг для выбора</p>
				</div>
			</div>
		</div>

		<div
			class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-2 md:gap-4 mt-4 transition-all duration-1000">
			<div v-for="item in itemsStore.additional_items" :key="item.vendor_code"
				class="flex flex-col justify-between p-2 md:p-4 bg-white dark:bg-slate-900 border rounded-xl hover:shadow-2xl hover:shadow-slate-100 dark:hover:shadow-slate-800 transition-all hover:z-10">
				<p class="font-medium mb-2 md:mb-4 text-sm md:text-base">
					<span>{{ item.vendor_code ? item.vendor_code + " - " : "" }} {{ item.name }}</span>
				</p>
				<div class="flex flex-row gap-2 md:gap-4">
					<div class="basis-1/3">
						<img :src="getImageSource(item.img as string)" class="rounded-md w-full" />
					</div>
					<div class="basis-2/3 flex flex-col justify-between gap-2">
						<div class="flex justify-between items-center text-sm text-muted-foreground">
							<span>Цена:</span>
							<span class="font-bold text-accent-foreground">{{
								currencyFormatter(itemsStore.itemPrice(item.id)) }}/{{ item.unit }}</span>
						</div>
						<div class="flex justify-between items-center text-sm text-muted-foreground">
							<span>Итого:</span>
							<span class="font-bold text-accent-foreground">{{
								currencyFormatter(itemsStore.itemPrice(item.id) * (itemsStore.cartItems[item.id] ? itemsStore.cartItems[item.id].quantity : 0))
							}}</span>
						</div>
						<div>
							<QuantitySelector v-if="itemsStore.cartItems[item.id]" :min="0" :max="100" :step="1" v-model="itemsStore.cartItems[item.id].quantity" />
							<Button v-else class="w-full" @click="() => (itemsStore.cartItems[item.id] = { quantity: 1 })">
								<PlusIcon /> Добавить
							</Button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>
