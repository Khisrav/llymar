<script setup lang="ts">
import Button from '../ui/button/Button.vue';
import { useItemsStore } from '../../Stores/itemsStore'
import { ref } from 'vue';
import { Eye, EyeOff } from 'lucide-vue-next';
import { currencyFormatter } from '../../Utils/currencyFormatter';
import { getImageSource } from '../../Utils/getImageSource';

const itemsStore = useItemsStore()
const isItemsListHidden = ref(false)
</script>

<template>
    <div class="border p-2 md:p-4 rounded-2xl">
        <div class="flex items-center">
            <h2 class="text-xl font-bold text-muted-foreground">Авторасчет</h2>
            <Button variant="outline" size="icon" class="ml-auto rounded-lg" @click="isItemsListHidden = !isItemsListHidden">
                <Eye v-if="!isItemsListHidden" class="size-6" />
                <EyeOff v-else class="size-6" />
            </Button>
        </div>

        <div :class="isItemsListHidden ? 'hidden' : ''" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2 md:gap-4 mt-4 transition-all duration-1000">
            <div v-for="item in itemsStore.items" :key="item.id" class="flex flex-col justify-between gap-2 bg-white dark:bg-slate-900 p-2 md:p-4 border rounded-xl hover:shadow-2xl hover:shadow-slate-100 dark:hover:shadow-slate-800 transition-all hover:z-10">
                <div class="text-center font-bold text-lg text-primary font-mono">{{ item.vendor_code }}</div>

                <div>
                    <img :src="getImageSource(item.img as string)" class="rounded-md w-full">
                </div>

                <div>
                    <div class="text-center text-sm sm:text-base py-2"><span>{{ item.name }}</span></div>

                    <div class="flex items-center justify-between">
                        <div class="flex flex-col items-center justify-between text-xs sm:text-xs">
                            <span class="text-muted-foreground">Цена:</span>
                            <span class="font-bold text-muted-foreground text-sm">{{ currencyFormatter(item.retail_price) }}/{{ item.unit }}</span>
                        </div>
                        <div class="flex flex-col items-center justify-between text-xs sm:text-xs">
                            <span class="text-muted-foreground">Кол-во:</span>
                            <span class="font-bold text-muted-foreground text-sm">
                                {{ itemsStore.cartItems[item.id as number]?.quantity ?? 0 }} {{ item.unit }}
                            </span>
                        </div>
                    </div>
    
                    <div class="text-center pt-2">
                        <span class="font-bold">
                            {{ currencyFormatter((itemsStore.cartItems[item.id as number]?.quantity ?? 0) * item.retail_price) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
