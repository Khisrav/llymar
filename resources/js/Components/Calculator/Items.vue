<script setup lang="ts">
import { useItemsStore } from '../../Stores/itemsStore'
import { ref } from 'vue';
import { Eye, EyeOff, PlusIcon, RotateCcw, Check, X } from 'lucide-vue-next';
import { currencyFormatter } from '../../Utils/currencyFormatter';
import { quantityFormatter } from '../../Utils/quantityFormatter';
import { getImageSource } from '../../Utils/getImageSource';
import { Button } from '../ui/button';
import { Label } from '../ui/label';
import { 
    NumberField, 
    NumberFieldContent, 
    NumberFieldDecrement, 
    NumberFieldInput, 
    NumberFieldIncrement 
} from '../ui/number-field';
import { Item } from '../../lib/types';
import ItemImageModal from '../ItemImageModal.vue';

const itemsStore = useItemsStore()
const isItemsListHidden = ref(false)
const selectedItem = ref<Item | null>(null)
const isModalOpen = ref(false)

const addItemToCart = (itemId: number) => {
    if (!itemsStore.cartItems[itemId]) {
        itemsStore.cartItems[itemId] = { quantity: 1, checked: true }
        
        itemsStore.updateServicesQuantity(itemsStore.selectedServicesID)
    }
}

const openImageModal = (item: Item) => {
    selectedItem.value = item
    isModalOpen.value = true
}

const closeImageModal = () => {
    isModalOpen.value = false
    selectedItem.value = null
}
</script>

<template>
    <div class="border p-2 md:p-4 rounded-2xl bg-background">
        <div class="flex items-center">
            <h2 class="text-xl font-bold text-muted-foreground">Состав системы</h2>
            <Button variant="outline" size="icon" class="ml-auto rounded-lg" @click="isItemsListHidden = !isItemsListHidden">
                <Eye v-if="!isItemsListHidden" class="size-6" />
                <EyeOff v-else class="size-6" />
            </Button>
        </div>

        <div :class="isItemsListHidden ? 'hidden' : ''" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2 md:gap-4 mt-4 transition-all duration-1000">
            <div v-for="item in itemsStore.items" :key="item.id" class="flex flex-col justify-between gap-2 bg-white dark:bg-slate-900 p-2 md:p-4 border rounded-xl hover:shadow-2xl hover:shadow-slate-100 dark:hover:shadow-slate-800 transition-all hover:z-10">
                <div class="flex justify-between">
                    <div class="text-center font-bold text-lg text-primary font-mono">{{ item.vendor_code }}</div>
                    <!-- Check/Uncheck Button -->
                    <Button 
                        :variant="(itemsStore.cartItems[item.id || 0]?.checked ?? true) ? 'outline' : 'destructive'"
                        size="icon"
                        class="h-7 w-7 text-xs"
                        @click="itemsStore.toggleItemChecked(item.id || 0)"
                    >
                        <Check v-if="itemsStore.cartItems[item.id || 0]?.checked ?? true" class="w-3 h-3" />
                        <X v-else class="w-3 h-3" />
                        <!-- {{ itemsStore.cartItems[item.id || 0]?.checked !== false ? 'Вкл' : 'Выкл' }} -->
                    </Button>
                </div>

                <div>
                    <img 
                        :src="getImageSource(item.img || '')" 
                        class="rounded-md w-full cursor-pointer hover:opacity-80 transition-opacity"
                        @click="openImageModal(item)"
                        :alt="item.name"
                    >
                </div>

                <div>
                    <div class="text-center text-sm sm:text-base py-2"><span>{{ item.name }}</span></div>

                    <!-- Item Controls -->
                    <div class="flex gap-1 mb-2">
                        <!-- Reset Button -->
                        <!-- <Button 
                            variant="outline"
                            size="sm"
                            class="h-7 px-2"
                            @click="itemsStore.resetItem(item.id || 0)"
                            :disabled="!itemsStore.cartItems[item.id || 0]?.quantity"
                        >
                            <RotateCcw class="w-3 h-3" />
                        </Button> -->
                    </div>

                    <div class="">
                        <div class="flex items-center justify-between text-xs sm:text-xs">
                            <span class="text-muted-foreground">Цена:</span>
                            <span class="font-bold text-muted-foreground text-sm">{{ currencyFormatter(itemsStore.itemPrice(item.id || 0)) }}/{{ item.unit }}</span>
                        </div>
                        <!-- <div class="flex flex-col items-center justify-between text-xs sm:text-xs">
                            <span class="text-muted-foreground">Кол-во:</span>
                            <span class="font-bold text-muted-foreground text-sm">
                                {{ quantityFormatter(itemsStore.cartItems[item.id || 0]?.quantity ?? 0) }} {{ item.unit }}
                            </span>
                        </div> -->
                    </div>
    
                    <!-- Quantity Selector -->
                    <div class="mt-3">
                        <Label v-if="itemsStore.cartItems[item.id || 0]" class="text-center mb-1 text-muted-foreground text-xs block">Кол-во: </Label>
                        <NumberField 
                            v-if="itemsStore.cartItems[item.id || 0]" 
                            v-model="itemsStore.cartItems[item.id || 0].quantity"
                            @update:model-value="itemsStore.updateServicesQuantity(itemsStore.selectedServicesID)"
                            :min="0"
                            :max="1000"
                        >
                            <NumberFieldContent>
                                <NumberFieldDecrement />
                                <NumberFieldInput class="h-8 text-center" />
                                <NumberFieldIncrement />
                            </NumberFieldContent>
                        </NumberField>
                        <Button 
                            v-else 
                            variant="outline"
                            size="sm"
                            class="w-full shadow-none"
                            @click="addItemToCart(item.id || 0)"
                        > 
                            <PlusIcon class="w-4 h-4 mr-1" /> 
                            Добавить 
                        </Button>
                    </div>
        
                    <div class="text-center pt-2">
                        <span 
                            class="font-bold"
                            :class="itemsStore.cartItems[item.id || 0]?.checked === false ? 'text-muted-foreground line-through' : ''"
                        >
                            {{ currencyFormatter((itemsStore.cartItems[item.id || 0]?.quantity ?? 0) * itemsStore.itemPrice(item.id || 0)) }}
                        </span>
                    </div>
                </div>
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
