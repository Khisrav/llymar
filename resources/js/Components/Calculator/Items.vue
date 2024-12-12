<script setup lang="ts">
import Button from '../ui/button/Button.vue';
import { useItemsStore } from '../../Stores/itemsStore'
import { ref } from 'vue';
import { Eye, EyeOff } from 'lucide-vue-next';

const itemsStore = useItemsStore()
const isItemsListHidden = ref(true)
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

        <div v-show="!isItemsListHidden" class=" overflow-hidden grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2 md:gap-4 mt-4 transition-all duration-1000">
            <div v-for="item in itemsStore.items" :key="item.id" class="flex flex-col justify-between gap-2 bg-white dark:bg-slate-900 p-2 md:p-4 border rounded-xl hover:shadow-2xl hover:shadow-slate-100 dark:hover:shadow-slate-800 transition-all hover:z-10">
                <div class="text-center font-bold text-lg text-primary font-mono">{{ item.vendor_code }}</div>

                <div>
                    <img :src="itemsStore.base_url + item.img" class="rounded w-full">
                </div>

                <div class="text-center text-sm sm:text-base"><span>{{ item.name }}</span></div>

                <div>
                    <div class="flex items-center justify-between text-xs sm:text-sm">
                        <span class="text-muted-foreground">Цена:</span>
                        <span class="font-bold text-muted-foreground">{{ item.retail_price }} ₽/{{ item.unit }}</span>
                    </div>
                    <div class="flex items-center justify-between text-xs sm:text-sm">
                        <span class="text-muted-foreground">Кол-во:</span>
                        <span class="font-bold text-muted-foreground">
                            {{ itemsStore.cartItems[item.vendor_code as string]?.quantity ?? 0 }} {{ item.unit }}
                        </span>
                    </div>
                </div>

                <div class="text-center">
                    <span class="font-bold">
                        {{ (itemsStore.cartItems[item.vendor_code as string]?.quantity ?? 0) * item.retail_price }}₽
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>
