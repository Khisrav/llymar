<script setup lang="ts">
import { EyeOff } from "lucide-vue-next";
import Button from "../ui/button/Button.vue";
import { useItemsStore } from "../../Stores/itemsStore";
import { currencyFormatter } from "../../Utils/currencyFormatter";
import QuantitySelector from "../QuantitySelector.vue";
import { computed, ref } from "vue";

const itemsStore = useItemsStore();
const cartItems = ref(itemsStore.cartItems);
</script>


<template>
	<div class="border p-2 md:p-4 rounded-2xl">
		<div class="flex items-center">
			<h2 class="text-xl font-bold text-muted-foreground">Дополнительно</h2>
			<Button variant="outline" size="icon" class="ml-auto rounded-lg" @click="">
				<EyeOff class="size-6" />
			</Button>
		</div>

		<div class="overflow-hidden grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-2 md:gap-4 mt-4 transition-all duration-1000">
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
						<img :src="item.img" class="rounded w-full" />
					</div>
					<div class="basis-2/3 flex flex-col justify-between gap-2 md:gap-4">
						<div class="flex justify-between items-center text-sm text-muted-foreground">
							<span>Цена:</span>
							<span class="font-bold text-primary">{{ currencyFormatter(item.retail_price) }}/{{ item.unit }}</span>
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

