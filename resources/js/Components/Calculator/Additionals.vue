<script setup lang="ts">
import { CrosshairIcon, CrossIcon, EyeOff, PlusIcon, XIcon } from "lucide-vue-next";
import Button from "../ui/button/Button.vue";
import { useItemsStore } from "../../Stores/itemsStore";
import { currencyFormatter } from "../../Utils/currencyFormatter";
import QuantitySelector from "../QuantitySelector.vue";
import { computed, ref } from "vue";
import Select from "../ui/select/Select.vue";
import SelectTrigger from "../ui/select/SelectTrigger.vue";
import SelectValue from "../ui/select/SelectValue.vue";
import SelectItem from "../ui/select/SelectItem.vue";
import SelectContent from "../ui/select/SelectContent.vue";
import Drawer from "../ui/drawer/Drawer.vue";
import { DrawerClose, DrawerTrigger } from "vaul-vue";
import DrawerContent from "../ui/drawer/DrawerContent.vue";
import DrawerHeader from "../ui/drawer/DrawerHeader.vue";
import DrawerTitle from "../ui/drawer/DrawerTitle.vue";
import DrawerDescription from "../ui/drawer/DrawerDescription.vue";
import DrawerFooter from "../ui/drawer/DrawerFooter.vue";
// import DrawerClose from "../ui/drawer/DrawerClose.vue";

const itemsStore = useItemsStore();
const cartItems = ref(itemsStore.cartItems);

const selectedGlass = ref(itemsStore.glasses[0].id);
</script>


<template>
	<div class="border p-2 md:p-4 rounded-2xl">
		<div class="flex items-center">
			<h2 class="text-xl font-bold text-muted-foreground">Детали</h2>
		</div>

		<div class="grid grid-cols-1 md:grid-cols-2 gap-2 md:gap-4">
			<div
				class="mt-4 flex flex-col md:flex-row gap-2 md:gap-4 justify-between p-2 md:p-4 bg-white dark:bg-slate-900 border rounded-xl hover:shadow-2xl hover:shadow-slate-100 dark:hover:shadow-slate-800 transition-all hover:z-10"
			>
				<div class="basis-1/3 order-2 md:order-1">
					<img :src="itemsStore.glasses[0].img" class="rounded-md w-full" />
				</div>
				<div class="flex-1 basis-2/3 overflow-hidden order-1 md:order-2">
					<Select v-model="selectedGlass">
						<SelectTrigger>
							<SelectValue placeholder="Выберите стекло" />
						</SelectTrigger>
						<SelectContent class="max-w-xs sm:max-w-max">
							<SelectItem v-for="glass in itemsStore.glasses" :value="glass.id">{{ glass.name }}</SelectItem>
						</SelectContent>
					</Select>
					
					<div>
						<div class="flex justify-between items-center text-muted-foreground my-1 md:my-2">
							<span>Цена:</span>
							<span class="font-bold text-primary">{{ currencyFormatter(itemsStore.glasses[0].retail_price) }}/{{ itemsStore.glasses[0].unit }}</span>
						</div>
						<div class="flex justify-between items-center text-muted-foreground my-1 md:my-2">
							<span>Кол-во:</span>
							<span class="font-bold text-primary">{{ 0 }} {{ itemsStore.glasses[0].unit }}</span>
						</div>
						<div class="flex justify-between items-center text-muted-foreground my-1 md:my-2">
							<span>Итого:</span>
							<span class="font-bold text-primary">{{ 1231230 }}</span>
						</div>
					</div>
				</div>
			</div>
			<div
				class="mt-4 p-2 md:p-4 bg-white dark:bg-slate-900 border rounded-xl hover:shadow-2xl hover:shadow-slate-100 dark:hover:shadow-slate-800 transition-all hover:z-10"
			>
				<div class="flex items-center justify-between gap-2 md:gap-4 mb-2 md:mb-4">
					<span class="font-medium">Выбранные услуги</span>
					<!-- <Button variant="outline" size="icon" class="">
						<PlusIcon class="size-4" />
					</Button> -->
				</div>
				<div class="flex items-center justify-between gap-2 md:gap-4 border-b pb-1">
					<span>Покраска</span>
					<div class="flex items-center gap-2 md:gap-4">
						<span class="font-bold text-primary">1238 руб</span>
						<Button variant="outline" size="icon" class="size-6 rounded-sm">
							<XIcon class="size-4" />
						</Button>
					</div>
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
						<img :src="item.img" class="rounded-md w-full" />
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

