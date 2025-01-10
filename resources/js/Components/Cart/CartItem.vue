<script setup lang="ts">
import { TrashIcon } from 'lucide-vue-next';
import { Item } from '../../lib/types'
import { useItemsStore } from '../../Stores/itemsStore'
import { getImageSource } from '../../Utils/getImageSource'
import Button from '../ui/button/Button.vue';
import { currencyFormatter } from '../../Utils/currencyFormatter';
import NumberField from '../ui/number-field/NumberField.vue';
import NumberFieldContent from '../ui/number-field/NumberFieldContent.vue';
import NumberFieldDecrement from '../ui/number-field/NumberFieldDecrement.vue';
import NumberFieldInput from '../ui/number-field/NumberFieldInput.vue';
import NumberFieldIncrement from '../ui/number-field/NumberFieldIncrement.vue';
import { discountRate } from '../../Utils/discountRate';

const itemsStore = useItemsStore()

const props = defineProps<{ item: Item }>()

const removeItem = (item_id: number) => {
    delete itemsStore.cartItems[item_id]
}
</script>

<template>
	<img :src="getImageSource(props.item.img as string) || '/placeholder.jpg'" :alt="props.item.name || 'Item Image'" class="h-24 w-24 rounded-md object-cover mr-4" />
	<div class="flex-1">
		<div class="flex justify-between">
			<h3 class="text-sm md:text-base font-medium">
				{{ props.item.name || "Unknown Item" }}
			</h3>
			<div>
			    <p v-if="discountRate(props.item.discount || itemsStore.user.discount) === 0" class="hidden md:block line-through text-muted-foreground">{{ 
			        currencyFormatter(itemsStore.cartItems[props.item.id].quantity * props.item.retail_price)
			    }}</p>
			    <p class="hidden md:block font-medium">{{
			        currencyFormatter(itemsStore.cartItems[props.item.id].quantity * props.item.retail_price * discountRate(props.item.discount || itemsStore.user.discount))
			    }}</p>
			</div>
		</div>
		<p class="mt-1 text-sm text-gray-500 dark:text-gray-300">Цена: {{ currencyFormatter(props.item.retail_price) }}/{{ props.item.unit }}</p>
		<p class="block md:hidden mt-1 text-sm">Всего: <b>{{ currencyFormatter(itemsStore.cartItems[props.item.id].quantity * props.item.retail_price) }}</b></p>
		<div class="flex justify-between gap-2 md:gap-4 items-center mt-2">
		    <div>
		        <NumberField v-model="itemsStore.cartItems[props.item.id].quantity">
                    <NumberFieldContent>
                        <NumberFieldDecrement />
                        <NumberFieldInput />
                        <NumberFieldIncrement />
                    </NumberFieldContent>
                </NumberField>
		    </div>

			<div>
			    <Button variant="outline" size="icon" class="size-10" @click="removeItem(props.item.id)">
    				<TrashIcon class="h-4 w-4" />
    			</Button>
			</div>
		</div>
	</div>
</template>
