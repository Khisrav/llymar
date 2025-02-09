<script setup lang="ts">
import { ref, computed, watch } from "vue"
import { useItemsStore } from "../../Stores/itemsStore"
import { useOpeningStore } from "../../Stores/openingsStore"
import { currencyFormatter } from "../../Utils/currencyFormatter"
import Slider from "../ui/slider/Slider.vue"
import Input from "../ui/input/Input.vue"

const itemsStore = useItemsStore()
const openingsStore = useOpeningStore()

const basePrice = itemsStore.total_price.with_discount
const allOpeningsArea = ref(openingsStore.openings.reduce((acc, opening) => acc + opening.width * opening.height, 0) / 1000000)
const percentage = ref(0)
const sliderValue = ref([0])

const markupPrice = computed({
	get() { return Math.floor(basePrice * (1 + percentage.value / 100)) },
	set(newPrice: number) { percentage.value = basePrice === 0 ? 0 : (newPrice / basePrice - 1) * 100 },
})

watch(sliderValue, ([val]) => { percentage.value = val })
watch(percentage, (val) => { sliderValue.value = [val] })
</script>

<template>
	<div class="border p-2 md:p-4 rounded-2xl bg-background w-full max-w-md mx-auto">
		<h2 class="text-xl font-bold text-muted-foreground block">Расчет стоимости</h2>

		<div class="mt-2">
			<div class="flex justify-between gap-4 mb-4">
				<div>Закупочная цена:</div>
				<div class="font-bold">{{ currencyFormatter(basePrice) }}</div>
			</div>
			<div class="flex justify-between gap-4 mb-4">
				<div>Наценка:</div>
				<div class="font-bold">{{ percentage.toFixed(2) }}%</div>
			</div>
			<div class="flex justify-between gap-4 mb-4">
				<Slider v-model="sliderValue" :min="-50" :max="50" :step="1" />
			</div>
			<div class="flex justify-between gap-4 mb-4">
				<div>Цена с наценкой за м<sup>2</sup>:</div>
				<div class="font-bold">{{ currencyFormatter(markupPrice / allOpeningsArea) }}/м<sup>2</sup></div>
			</div>
			<div class="flex justify-between items-center gap-4">
				<div>Цена с наценкой <b>{{ percentage.toFixed(2) }}%</b>:</div>
				<Input v-model="markupPrice" type="number" class="w-24 md:w-32" />
			</div>
		</div>
	</div>
</template>
