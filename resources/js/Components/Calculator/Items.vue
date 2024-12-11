<script setup lang="ts">
import { useItemsStore } from '../../Stores/itemsStore'

const itemsStore = useItemsStore()
</script>

<template>
    <div class="border p-2 md:p-4 rounded-2xl">
        <h2 class="text-xl font-bold text-muted-foreground mb-4">Детали</h2>
        
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 lg:grid-cols-6 gap-2 md:gap-4">
            <div v-for="item in itemsStore.items" :key="item.id" class="bg-white dark:bg-slate-900 p-2 md:p-4 border rounded-xl hover:shadow-2xl hover:shadow-slate-100 dark:hover:shadow-slate-800 transition-all hover:z-10">
                <div class="text-center font-bold">{{ item.vendor_code }}</div>
                
                <div>
                    <img :src="itemsStore.base_url + item.img">
                </div>
                
                <div class="text-center"><span>{{ item.name }}</span></div>
                
                <div>
                    <div>
                        <span class="text-muted-foreground">Цена за {{ item.unit }}</span>
                        <span>{{ item.retail_price }} ₽</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>