<script setup lang="ts">
import { ref } from 'vue';
import { useItemsStore } from '../../Stores/itemsStore';
import { currencyFormatter } from '../../Utils/currencyFormatter';
import Slider from '../ui/slider/Slider.vue';

const itemsStore = useItemsStore();
const percentage = ref(0);
</script>

<template>
        <div class="border p-2 md:p-4 rounded-2xl bg-background w-full max-w-md mx-auto">
            <h2 class="text-xl font-bold text-muted-foreground block">Расчет стоимости</h2>
            
            <div class="mt-2">
                <div class="flex justify-between gap-4">
                    <div>Закупочная цена: </div>
                    <div class="font-bold">{{ currencyFormatter(itemsStore.total_price.with_discount) }}</div>
                </div>
                <div class="flex justify-between gap-4">
                    <div>Наценка: </div>
                    <div class="font-bold">{{ percentage }}%</div>
                </div>
                <div class="flex justify-between gap-4">
                    <!-- <Slider v-model="percentage" :min="-50" :max="50" :default-value="0" :step="1" /> -->
                </div>
                <div class="flex justify-between gap-4">
                    <div>Цена с наценкой за м<sup>2</sup>: </div>
                    <div class="font-bold">{{ currencyFormatter(itemsStore.total_price.with_discount / 1) }}/м<sup>2</sup></div>
                </div>
                <div class="flex justify-between gap-4">
                    <div>Цена с наценкой <b>{{ percentage }}%</b>: </div>
                    <div class="font-bold">{{ currencyFormatter(itemsStore.total_price.with_discount / 1) }}/м<sup>2</sup></div>
                </div>
            </div>
        </div>
</template>