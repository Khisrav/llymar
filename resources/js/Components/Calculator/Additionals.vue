<script setup lang="ts">
import { EyeOff, PrinterIcon } from "lucide-vue-next";
import Button from "../ui/button/Button.vue";
import { useItemsStore } from "../../Stores/itemsStore";
import NumberField from "../ui/number-field/NumberField.vue";
import Label from "../ui/label/Label.vue";
import NumberFieldContent from "../ui/number-field/NumberFieldContent.vue";
import NumberFieldDecrement from "../ui/number-field/NumberFieldDecrement.vue";
import NumberFieldInput from "../ui/number-field/NumberFieldInput.vue";
import NumberFieldIncrement from "../ui/number-field/NumberFieldIncrement.vue";
import { currencyFormatter } from "../../Utils/currencyFormatter";
import QuantitySelector from "../QuantitySelector.vue";

const itemsStore = useItemsStore();
</script>

<template>
	<div class="border p-2 md:p-4 rounded-2xl">
		<div class="flex items-center">
			<h2 class="text-xl font-bold text-muted-foreground">Дополнительно</h2>
			<Button variant="outline" size="icon" class="ml-auto rounded-lg" @click="">
				<!-- <Eye v-if="!isItemsListHidden" class="size-6" /> -->
				<EyeOff class="size-6" />
			</Button>
		</div>

		<div class="overflow-hidden grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-2 md:gap-4 mt-4 transition-all duration-1000">
			<div v-for="item in itemsStore.additional_items" :key="item.vendor_code" class="flex flex-col justify-between p-2 md:p-4 bg-white dark:bg-slate-900 border rounded-xl hover:shadow-2xl hover:shadow-slate-100 dark:hover:shadow-slate-800 transition-all hover:z-10">
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
                        
                        <QuantitySelector :min="0" :max="100" :step="1" />
						<!-- <NumberField id="age" :default-value="0" :step="1" :min="0" :format-options="{
						    minimumFractionDigits: 0,
                            maximumFractionDigits: 0,
						}">
							<Label for="age">Age</Label>
							<NumberFieldContent>
								<NumberFieldDecrement />
								<NumberFieldInput />
								<NumberFieldIncrement />
							</NumberFieldContent>
						</NumberField> -->
					</div>
				</div>
			</div>
		</div>
	</div>
</template>
