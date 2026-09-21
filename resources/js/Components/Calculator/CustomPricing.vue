<script setup lang="ts">
import { ref, watch, computed, onMounted } from "vue"
import { usePage } from "@inertiajs/vue3"
import { useItemsStore } from "../../Stores/itemsStore"
import { useOpeningStore } from "../../Stores/openingsStore"
import { currencyFormatter } from "../../Utils/currencyFormatter"
import { DISPLAY_FACTORS, factorLabel } from "../../Utils/priceFactor"
import { Slider } from "../ui/slider"
import { Input } from "../ui/input"
import { Label } from "../ui/label"
import { RadioGroup, RadioGroupItem } from "../ui/radio-group"
import { 
	NumberField, 
    NumberFieldContent, 
    NumberFieldDecrement, 
    NumberFieldInput, 
    NumberFieldIncrement 
} from '../ui/number-field'

const itemsStore = useItemsStore()
const openingsStore = useOpeningStore()
const { can_access_factors } = usePage().props as any

const basePrice = computed(() => itemsStore.total_price.with_discount)
const allOpeningsArea = computed(() => openingsStore.openings.reduce((acc, o) => acc + o.width * o.height, 0) / 1000000)

const factorOptions = computed(() => DISPLAY_FACTORS.map((key) => ({
	key,
	label: factorLabel(key),
	price: itemsStore.totalPriceAt(key),
	selectable: key !== 'pz',
	isDiscount: key === itemsStore.recommendedFactor && key !== 'pz' && key !== 'p4',
})))

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
	<div class="border p-2 md:p-4 rounded-2xl bg-background w-full max-w-5xl mx-auto">
		<h2 class="text-xl font-bold text-muted-foreground block">Стоимость для КП</h2>

		<RadioGroup
			v-if="can_access_factors"
			class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-2"
			:model-value="itemsStore.selectedFactor"
			@update:model-value="(value) => itemsStore.setSelectedFactor(String(value))"
		>
			<Label
				v-for="option in factorOptions"
				:key="option.key"
				:for="`factor-${option.key}`"
				class="flex items-center gap-2 rounded-xl border p-3 cursor-pointer"
				:class="{
					'font-bold border-primary': option.isDiscount,
					'opacity-60 cursor-not-allowed': !option.selectable,
					'border-primary bg-primary/5': option.key === itemsStore.selectedFactor,
				}"
			>
				<RadioGroupItem :id="`factor-${option.key}`" :value="option.key" :disabled="!option.selectable" />
				<span>
					{{ option.label }} {{ currencyFormatter(option.price) }}
					<template v-if="option.isDiscount"> Скидка</template>
				</span>
			</Label>
		</RadioGroup>

		<div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
			<div>
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
			</div>

			<div>
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
	</div>
</template>
