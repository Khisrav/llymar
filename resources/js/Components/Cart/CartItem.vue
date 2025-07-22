<script setup lang="ts">
import { TrashIcon } from 'lucide-vue-next';
import { Item } from '../../lib/types'
import { useItemsStore } from '../../Stores/itemsStore'
import { getImageSource } from '../../Utils/getImageSource'
import { Button } from '../ui/button';
import { currencyFormatter } from '../../Utils/currencyFormatter';
import { quantityFormatter, parseQuantity } from '../../Utils/quantityFormatter';
import { 
    NumberField, 
    NumberFieldContent, 
    NumberFieldDecrement, 
    NumberFieldInput, 
    NumberFieldIncrement 
} from '../ui/number-field';
import { discountRate } from '../../Utils/discountRate';
import ItemImageModal from '../ItemImageModal.vue';
import { ref } from 'vue';

const itemsStore = useItemsStore()
const selectedItem = ref<Item | null>(null)
const isModalOpen = ref(false)

const props = defineProps<{ item: Item }>()

const itemPrice = itemsStore.itemPrice(props.item.id || 0)

const removeItem = (item_id: number) => {
    delete itemsStore.cartItems[item_id]
}

const openImageModal = () => {
    selectedItem.value = props.item
    isModalOpen.value = true
}

const closeImageModal = () => {
    isModalOpen.value = false
    selectedItem.value = null
}
</script>

<template>
	<img 
		:src="getImageSource(props.item.img || '') || '/placeholder.jpg'" 
		:alt="props.item.name || 'Item Image'" 
		class="w-20 md:w-24 rounded-md object-cover mr-4 cursor-pointer hover:opacity-80 transition-opacity" 
		@click="openImageModal"
	/>
	<div class="flex-1">
		<div class="flex justify-between text-xs md:text-base">
			<h3 class="font-medium" :class="itemsStore.cartItems[props.item.id || 0].checked ? '' : 'line-through text-gray-500'">
				{{ props.item.name || "Неизвестная деталь" }}
			</h3>
			<div>
			    <p class="font-medium ml-4" :class="itemsStore.cartItems[props.item.id || 0].checked ? '' : 'line-through text-gray-500'">{{
			        currencyFormatter(itemsStore.cartItems[props.item.id || 0].quantity * itemPrice)
			    }}</p>
			</div>
		</div>
		<p class="mt-1 text-xs md:text-sm text-gray-500 dark:text-gray-300">Цена: {{ currencyFormatter(itemPrice) }}/{{ props.item.unit }}</p>
		<p class="block md:hidden mt-1 text-xs md:text-sm">Всего: <b>{{ currencyFormatter(itemsStore.cartItems[props.item.id || 0].quantity * itemPrice) }}</b></p>
		<div class="flex justify-between gap-2 md:gap-4 items-center mt-2">
		    <div>
		        <NumberField v-model="itemsStore.cartItems[props.item.id || 0].quantity">
                    <NumberFieldContent>
                        <NumberFieldDecrement />
                        <NumberFieldInput class="h-8 md:h-auto" />
                        <NumberFieldIncrement />
                    </NumberFieldContent>
                </NumberField>
		    </div>

			<div>
			    <Button variant="outline" size="icon" class="size-8 md:size-10" @click="removeItem(props.item.id || 0)">
    				<TrashIcon class="h-4 w-4" />
    			</Button>
			</div>
		</div>
	</div>
	
	<!-- Image Modal -->
	<ItemImageModal
		:item="selectedItem"
		:is-open="isModalOpen"
		@close="closeImageModal"
	/>
</template>
