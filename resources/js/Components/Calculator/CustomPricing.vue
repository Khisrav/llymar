<script setup lang="ts">
import { ref, watch, computed, onMounted } from "vue"
import { useItemsStore } from "../../Stores/itemsStore"
import { useOpeningStore } from "../../Stores/openingsStore"
import { currencyFormatter } from "../../Utils/currencyFormatter"
import { Slider } from "../ui/slider"
import { Input } from "../ui/input"
import { 
	NumberField, 
	NumberFieldContent, 
	NumberFieldDecrement, 
	NumberFieldInput, 
	NumberFieldIncrement 
} from '../ui/number-field'

const itemsStore = useItemsStore()
const openingsStore = useOpeningStore()

const basePrice = computed(() => itemsStore.total_price.with_discount)
const allOpeningsArea = computed(() => openingsStore.openings.reduce((acc, o) => acc + o.width * o.height, 0) / 1000000)

// ---------- HELPER FUNCTIONS ----------
const totalPriceFromPercentage = (percentage: number) => basePrice.value * (1 + percentage / 100)
const percentageFromTotalPrice = (price: number) => basePrice.value === 0 ? 0 : (price / basePrice.value - 1) * 100

// ----- LOCAL REF FOR THE INPUT -----
const typedTotalPrice = ref(0);

// ----- HANDLE MARKUP PERCENTAGE UPDATE WITH PROPER PRECISION -----
const handleMarkupPercentageUpdate = (value: number) => {
	// If value is empty, undefined, or NaN, default to 0
	if (value == null || isNaN(value)) {
		itemsStore.markupPercentage = 0
		return
	}
	// Fix floating point precision by rounding to 3 decimal places
	// const roundedValue = Math.round(value * 1000) / 1000
	const roundedValue = value
	itemsStore.markupPercentage = roundedValue
}

onMounted(() => { typedTotalPrice.value = Math.round(totalPriceFromPercentage(itemsStore.markupPercentage)) })

// watch(typedTotalPrice, (newVal) => { 
// 	if (newVal == null || isNaN(newVal)) {
// 		itemsStore.markupPercentage = 0
// 		return
// 	}
	
// 	itemsStore.markupPercentage = percentageFromTotalPrice(newVal)
// })

const handleTypedTotalPriceBlur = () => {
	if (!typedTotalPrice.value) {
		typedTotalPrice.value = basePrice.value
	} else {
		itemsStore.markupPercentage = percentageFromTotalPrice(typedTotalPrice.value)
	}
}

watch(() => itemsStore.markupPercentage, (newPercent) => {
	typedTotalPrice.value = Math.round(totalPriceFromPercentage(newPercent))
})

// ----- WATCH BASE PRICE CHANGES -----
watch(basePrice, () => { typedTotalPrice.value = (totalPriceFromPercentage(itemsStore.markupPercentage)) })

// ----- SLIDER BINDING -----
const sliderValue = computed({
	get() { return [itemsStore.markupPercentage] },
	set([val]) { itemsStore.markupPercentage = parseFloat(val.toFixed(4)) },
})

const pricePerM2 = computed(() => {
	const area = allOpeningsArea.value
	if (!area) return 0
	return typedTotalPrice.value / area
})
</script>

<template>
	<div class="border p-2 md:p-4 rounded-2xl bg-background w-full max-w-md mx-auto">
		<h2 class="text-xl font-bold text-muted-foreground block">Стоимость для КП</h2>

		<div class="mt-2">
			<div class="flex justify-between gap-4 mb-4">
				<div>Закупочная цена:</div>
				<div class="font-bold">{{ currencyFormatter(basePrice) }}</div>
			</div>

			<div class="flex justify-between items-center gap-4 mb-4">
				<div>Наценка в %:</div>
				<NumberField 
					:model-value="itemsStore.markupPercentage" 
					@update:model-value="handleMarkupPercentageUpdate"
					:step="0.01"
					:default-value="0"
					class="w-24 md:w-32"
				>
					<NumberFieldContent>
						<NumberFieldDecrement />
						<NumberFieldInput class="h-9 text-center" />
						<NumberFieldIncrement />
					</NumberFieldContent>
				</NumberField>
			</div>

			<div class="flex justify-between gap-4 mb-4">
				<Slider v-model="sliderValue" :min="-100" :max="100" :step="0.01" />
			</div>

			<div class="flex justify-between gap-4 mb-4">
				<div>Цена с наценкой за м<sup>2</sup>:</div>
				<div class="font-bold">{{ currencyFormatter(pricePerM2) }}/м<sup>2</sup></div>
			</div>

			<div class="flex justify-between items-center gap-4">
				<div>
					Цена с наценкой <b>{{ itemsStore.markupPercentage.toFixed(4) }}%</b>:
				</div>
				<Input v-model="typedTotalPrice" @blur="handleTypedTotalPriceBlur" type="number" class="w-24 md:w-32" />
			</div>
		</div>
	</div>
</template>
