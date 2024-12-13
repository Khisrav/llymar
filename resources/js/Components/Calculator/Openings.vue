<script setup lang="ts">
import { useOpeningStore } from '../../Stores/openingsStore';
import Button from '../ui/button/Button.vue';
import Select from '../ui/select/Select.vue';
import { CirclePlusIcon, Trash2Icon } from 'lucide-vue-next';
import SelectTrigger from '../ui/select/SelectTrigger.vue';
import SelectContent from '../ui/select/SelectContent.vue';
import SelectItem from '../ui/select/SelectItem.vue';
import SelectValue from '../ui/select/SelectValue.vue';
import Input from '../ui/input/Input.vue';
import { watch } from 'vue';
import { doorsSelectLimiter } from '../../Utils/doorsSelectLimiter';
import QuantitySelector from '../QuantitySelector.vue';
import { useItemsStore } from '../../Stores/itemsStore';

const openingStore = useOpeningStore();
const itemsStore = useItemsStore();

watch(() => openingStore.openings, (newOpenings) => {
    console.log('change detected');
    itemsStore.calculate();

    newOpenings.forEach((opening, index) => {
        watch(() => openingStore.openings[index].type, (newValue) => {
            console.log(`Opening ${index} type changed to: ${newValue}`);
            openingStore.openings[index].doors = doorsSelectLimiter(newValue).min;
        });
    });
}, { deep: true });
</script>

<template>
    <div>
        <h2 class="text-xl font-bold text-muted-foreground mb-4">Проемы</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-2 md:gap-4">
        <div v-for="(opening, index) in openingStore.openings" class="bg-white dark:bg-slate-900 p-2 md:p-4 border rounded-xl hover:shadow-2xl hover:shadow-slate-100 dark:hover:shadow-slate-800 transition-all hover:z-10">
            <div class="flex justify-between items-center gap-2">
                <Select v-model="openingStore.openings[index].type" class="h-9 block">
                    <SelectTrigger class="h-9 shadow-sm">
                        <SelectValue placeholder="Выберите проем"/>
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem v-for="(type, key) in openingStore.openingTypes" :key="key" :value="key">{{ type }}</SelectItem>
                    </SelectContent>
                </Select>
                <Button variant="outline" size="icon" @click="openingStore.removeOpening(index)">
                    <Trash2Icon class="size-4" />
                </Button>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-1 gap-2">
                <div class="flex items-center">
                    <img :src="openingStore.opening_images[opening.type]" class="w-full rounded md:mt-4">
                </div>
                <div>
                    <label class="text-center my-1 text-muted-foreground text-xs md:text-sm block">Размеры (ШxВ) в мм:</label>
                    <div class="flex items-center gap-2">
                        <Input v-model="openingStore.openings[index].width" type="number" max="12800" placeholder="Ширина" class="h-9 text-center" />
                        <span class="inline-block text-sm">&#10005;</span>
                        <Input v-model="openingStore.openings[index].height" type="number" placeholder="Высота" class="h-9 text-center" />
                    </div>

                    <div class="gap-2 mt-2">
                        <label class="text-center mb-1 text-muted-foreground text-xs md:text-sm block">Кол-во створок:</label>
                        <QuantitySelector
                            v-model="opening.doors"
                            :min="doorsSelectLimiter(openingStore.openings[index].type).min"
                            :max="doorsSelectLimiter(openingStore.openings[index].type).max"
                            :step="doorsSelectLimiter(openingStore.openings[index].type).step"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- add opening -->
        <button class="p-2 md:p-4 border-4 border-dashed rounded-xl text-center flex items-center justify-center hover:border-black hover:dark:border-white transition-all text-gray-300 dark:text-gray-600 font-bold text-xl hover:text-black hover:dark:text-white" @click="openingStore.addOpening">
            <div class="text-center flex flex-col items-center gap-2 p-6">
                <CirclePlusIcon class="size-12" />
                <span class="block">Добавить проем</span>
            </div>
        </button>
    </div>
    </div>
</template>
