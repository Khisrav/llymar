<script setup lang="ts">
import { useItemsStore } from "../../Stores/itemsStore";
import { currencyFormatter } from "../../Utils/currencyFormatter";
import { quantityFormatter } from "../../Utils/quantityFormatter";
import { computed, ref, watch } from "vue";
import { getImageSource } from "../../Utils/getImageSource";
import { PlusIcon, Check, X } from "lucide-vue-next";
import { Button } from "../ui/button";
import { Checkbox } from "../ui/checkbox";
import { Label } from "../ui/label";
import { 
	Select,
	SelectContent,
	SelectItem,
	SelectTrigger,
	SelectValue,
} from "../ui/select";
import { 
	NumberField, 
	NumberFieldContent, 
	NumberFieldDecrement, 
	NumberFieldInput, 
	NumberFieldIncrement 
} from '../ui/number-field';
import { Separator } from "../ui/separator";
import ItemImageModal from '../ItemImageModal.vue';
import { Item } from '../../lib/types';
import QuantitySelector from "../QuantitySelector.vue";

const itemsStore = useItemsStore();
const selectedGlass = ref<any>(itemsStore.glasses.find((glass) => glass.id === itemsStore.selectedGlassID) || null);
const selectedGhostGlass = ref<any>(null)
const selectedItem = ref<Item | null>(null);
const isModalOpen = ref(false);
const selectedGlassToAdd = ref<string>('')

const servicesByCategory = computed(() => {
	const llymarServices = itemsStore.services.filter((service) => service.category_id === 26);
	const otherServices = itemsStore.services.filter((service) => service.category_id !== 26);
	let categories = [...llymarServices.map((service) => service.category_id), ...otherServices.map((service) => service.category_id)];
	categories = [...new Set(categories)];

	return {
		[categories[0]]: llymarServices,
		[categories[1]]: otherServices
	}
})

watch(
	() => itemsStore.selectedGlassID,
	(newID) => {
		selectedGlass.value = itemsStore.glasses.find((glass) => glass.id === newID) || null;
	}
);

const toggleSelection = (serviceId: number) => {
	if (itemsStore.selectedServicesID.includes(serviceId)) {
		itemsStore.selectedServicesID = itemsStore.selectedServicesID.filter((id) => id !== serviceId);
	} else {
		itemsStore.selectedServicesID.push(serviceId);
	}
};

const step = (category_id: number) => {
	if (category_id == 30) return 3
	return 1
}

const addAdditionalItemToCart = (itemId: number, step: number) => {
	if (!itemsStore.cartItems[itemId]) {
		itemsStore.cartItems[itemId] = { quantity: step, checked: true }
	}
	
	itemsStore.updateServicesQuantity(itemsStore.selectedServicesID)
}

const openImageModal = (item: Item) => {
	selectedItem.value = item
	isModalOpen.value = true
}

const closeImageModal = () => {
	isModalOpen.value = false
	selectedItem.value = null
}

const getCategoryName = (categoryId: number | string) => {
	const id = typeof categoryId === 'string' ? parseInt(categoryId) : categoryId
	const category = itemsStore.categories.find(cat => cat.id === id)
	return category ? category.name : `Категория ${id}`
}

const availableGhostHandles = computed(() => {
	return itemsStore.ghost_handles.filter(handle => 
		!itemsStore.selectedGhostHandlesID.includes(handle.id || 0)
	)
})

const selectedGhostHandles = computed(() => {
	return itemsStore.ghost_handles.filter(handle => 
		itemsStore.selectedGhostHandlesID.includes(handle.id || 0)
	)
})

const selectedHandleToAdd = ref<string>('')

const addSelectedHandle = () => {
	if (selectedHandleToAdd.value) {
		const handleId = parseInt(selectedHandleToAdd.value)
		itemsStore.addGhostHandle(handleId)
		selectedHandleToAdd.value = '' // Reset selection
	}
}

const availableGhostGlasses = computed(() => {
	return itemsStore.glasses.filter(glass => 
		glass.id !== itemsStore.selectedGlassID && 
		!itemsStore.selectedGhostGlassesID.includes(glass.id || 0)
	)
})

const selectedGhostGlasses = computed(() => {
	return itemsStore.glasses.filter(glass => 
		itemsStore.selectedGhostGlassesID.includes(glass.id || 0)
	)
})

const addSelectedGlass = () => {
	if (selectedGlassToAdd.value) {
		const glassId = parseInt(selectedGlassToAdd.value)
		itemsStore.addGhostGlass(glassId)
		selectedGlassToAdd.value = '' // Reset selection
	}
}

const totalPriceWithGhostGlass = (glassId: number) => {
	// const regularGlassTotalprice = itemsStore.itemPrice(selectedGlass.value?.id || 0) * (itemsStore.cartItems[selectedGlass.value?.id || 0]?.quantity || 0)
	const ghostGlassTotalprice = itemsStore.itemPrice(glassId) * (itemsStore.cartItems[glassId]?.quantity || 0)
	
	// return itemsStore.total_price.with_discount - regularGlassTotalprice + ghostGlassTotalprice
	return ghostGlassTotalprice
}
</script>

<template>
	<div class="border p-2 md:p-4 rounded-2xl bg-background mb-8">
		<div class="flex items-center mb-4">
			<h2 class="text-xl font-bold text-muted-foreground">Детали</h2>
		</div>

		<div class="grid grid-cols-1 sm:grid-cols-2 gap-2 md:gap-8">
			<div class="p-2 md:p-4 bg-white dark:bg-slate-900 border rounded-xl hover:shadow-2xl hover:shadow-slate-100 dark:hover:shadow-slate-800 transition-all hover:z-10">
				<div class="flex justify-between items-center mb-2">
					<p class="font-semibold">Стекло</p>
					<!-- Check/Uncheck Button for Glass -->
					<Button 
						v-if="selectedGlass && itemsStore.selectedGlassID !== -1"
						:variant="(itemsStore.cartItems[itemsStore.selectedGlassID]?.checked ?? true) ? 'outline' : 'destructive'"
						size="icon"
						class="h-7 w-7 text-xs"
						@click="itemsStore.toggleItemChecked(itemsStore.selectedGlassID)"
					>
						<Check v-if="itemsStore.cartItems[itemsStore.selectedGlassID]?.checked ?? true" class="w-3 h-3" />
						<X v-else class="w-3 h-3" />
					</Button>
				</div>
				<div class="flex flex-col md:flex-row gap-2 md:gap-4 justify-between">
					<div class="flex-1 basis-2/3 overflow-hidden">
						<div>
							<Select :model-value="itemsStore.selectedGlassID.toString()" @update:modelValue="(value) => itemsStore.selectedGlassID = parseInt(value)">
								<SelectTrigger>
									<SelectValue placeholder="Выберите стекло" />
								</SelectTrigger>
								<SelectContent class="max-w-xs sm:max-w-max">
									<SelectItem value="-1">Без стекла</SelectItem>
									<SelectItem v-for="glass in itemsStore.glasses" :key="glass.id" :value="(glass.id || 0).toString()">{{ glass.name }}</SelectItem>
								</SelectContent>
							</Select>
						</div>
						<div v-if="selectedGlass" class="flex flex-col justify-between mt-2 gap-1 md:gap-2">
							<div class="flex justify-between items-center text-muted-foreground">
								<span>Цена:</span>
								<span v-if="selectedGlass" class="font-bold text-primary"> {{ currencyFormatter(itemsStore.itemPrice(itemsStore.selectedGlassID)) }}/{{ selectedGlass.unit }} </span>
							</div>
							<div class="flex justify-between items-center text-muted-foreground">
								<span>Кол-во:</span>
								<span v-if="selectedGlass" class="font-bold text-primary"> {{ quantityFormatter(itemsStore.cartItems[itemsStore.selectedGlassID]?.quantity || 0) }} {{ selectedGlass.unit }} </span>
							</div>
							<div class="flex justify-between items-center text-muted-foreground">
								<span>Вес:</span>
								<span v-if="selectedGlass" class="font-bold text-primary"> {{ quantityFormatter(selectedGlass.weight ? selectedGlass.weight * (itemsStore.cartItems[itemsStore.selectedGlassID]?.quantity || 0) : 0, 3) }} кг</span>
							</div>
							<div class="flex justify-between items-center text-muted-foreground">
								<span>Итого:</span>
								<span 
									v-if="selectedGlass" 
									class="font-bold"
									:class="itemsStore.cartItems[itemsStore.selectedGlassID]?.checked === false ? 'text-muted-foreground line-through' : 'text-primary'"
								>
									{{ currencyFormatter(itemsStore.itemPrice(itemsStore.selectedGlassID) * (itemsStore.cartItems[itemsStore.selectedGlassID]?.quantity || 0)) }}
								</span>
							</div>
							<div v-if="itemsStore.cartItems[itemsStore.selectedGlassID]?.checked === false" class="text-xs text-muted-foreground text-center mt-1">
								(не учитывается)
							</div>
						</div>
					</div>
					<div v-if="selectedGlass" class="basis-1/3">
						<img 
							:src="getImageSource(selectedGlass.img || '')" 
							class="rounded-md border text-xs w-full cursor-pointer hover:opacity-80 transition-opacity" 
							@click="openImageModal(selectedGlass)"
							:alt="selectedGlass.name"
						/>
					</div>
				</div>

				<div class="flex flex-col gap-2 mt-6">
					<div class="flex justify-between items-center">
						<span class="font-semibold">Доп. стекла для КП</span>
						<span class="text-xs text-muted-foreground">(Для КП, не учитываются в цене)</span>
					</div>

					<!-- Add glass selector -->
					<div v-if="availableGhostGlasses.length > 0" class="flex justify-between items-center gap-2">
						<div class="flex-1 overflow-hidden">
							<Select :model-value="selectedGlassToAdd" @update:modelValue="(value) => selectedGlassToAdd = value || ''">
								<SelectTrigger class="h-9 text-sm shadow-sm">
									<SelectValue placeholder="Выберите стекло для добавления" />
								</SelectTrigger>
								<SelectContent>
									<SelectItem v-for="glass in availableGhostGlasses" :key="glass.id" :value="(glass.id || 0).toString()">
										{{ glass.name }}
									</SelectItem>
								</SelectContent>
							</Select>
						</div>
						<Button
							@click="addSelectedGlass"
							:disabled="!selectedGlassToAdd"
							size="icon"
							variant="outline"
							class="shrink-0"
						>
							<PlusIcon class="size-4" />
						</Button>
					</div>
					
					<!-- Selected ghost glasses with quantity controls -->
					<div v-if="selectedGhostGlasses.length > 0" class="space-y-2">
						<div class="space-y-2">
							<div v-for="glass in selectedGhostGlasses" :key="glass.id" class="flex flex-col md:flex-row items-center justify-between gap-2 p-2 border rounded-lg bg-muted/30">
								<div class="flex-1 w-full">
									<div class="font-medium text-muted-foreground text-sm">{{ glass.name }}</div>
									<div class="text-xs text-muted-foreground">
										{{ quantityFormatter(itemsStore.cartItems[glass.id || 0]?.quantity || 0) }} {{ glass.unit }}
									</div>
								</div>
								<div class="flex items-center justify-between gap-2 w-full md:w-auto">
									<div class="text-xs text-muted-foreground text-right">
										<div>{{ currencyFormatter(itemsStore.itemPrice(glass.id || 0)) }}/{{ glass.unit }}</div>
										<div class="font-medium">{{ currencyFormatter(totalPriceWithGhostGlass(glass.id || 0)) }}</div>
									</div>
									<Button 
										variant="outline" 
										size="icon" 
										@click="itemsStore.removeGhostGlass(glass.id || 0)"
										class="shrink-0"
									>
										<X class="size-4" />
									</Button>
								</div>
							</div>
						</div>
					</div>
					
					<div v-if="itemsStore.glasses.length === 0" class="text-center text-muted-foreground text-sm py-4">
						Нет доступных стекол
					</div>
					
					<div v-else-if="availableGhostGlasses.length === 0 && selectedGhostGlasses.length === 0" class="text-center text-muted-foreground text-sm py-2">
						Все стекла добавлены
					</div>
				</div>
					
				<div class="flex flex-col gap-2 mt-6">
					<div class="flex justify-between items-center">
						<span class="font-semibold">Доп. ручки для КП</span>
						<span class="text-xs text-muted-foreground">(не учитываются в цене)</span>
					</div>
					
					<!-- Add handle selector -->
					<div v-if="availableGhostHandles.length > 0" class="flex justify-between items-center gap-2">
						<div class="flex-1 overflow-hidden">
							<Select :model-value="selectedHandleToAdd" @update:modelValue="(value) => selectedHandleToAdd = value || ''">
								<SelectTrigger class="h-9 text-sm shadow-sm">
									<SelectValue placeholder="Выберите ручку для добавления" />
								</SelectTrigger>
								<SelectContent>
									<SelectItem v-for="handle in availableGhostHandles" :key="handle.id" :value="(handle.id || 0).toString()">
										{{ handle.name }}
									</SelectItem>
								</SelectContent>
							</Select>
						</div>
						<Button
							@click="addSelectedHandle"
							:disabled="!selectedHandleToAdd"
							size="icon"
							variant="outline"
							class="shrink-0"
						>
							<PlusIcon class="size-4" />
						</Button>
					</div>
					
					<!-- Selected handles with quantity controls -->
					<div v-if="selectedGhostHandles.length > 0" class="space-y-2">
						<!-- <span class="text-sm text-muted-foreground">Выбранные ручки:</span> -->
						<div class="space-y-2">
							<div v-for="handle in selectedGhostHandles" :key="handle.id" class="flex flex-col md:flex-row items-center justify-between gap-2 p-2 border rounded-lg bg-muted/30">
								<div class="flex-1 w-full">
									<div class="font-medium text-muted-foreground text-sm">{{ handle.name }}</div>
								</div>
								<div class="flex items-center justify-between gap-2 w-full md:w-auto">
									<div class="flex items-center gap-4">
										<div class="flex items-center gap-2">
											<QuantitySelector v-model="itemsStore.cartItems[handle.id || 0].quantity" :min="1" :max="100" :step="1" :unit="handle.unit || 'шт.'" />
										</div>
									</div>
									<Button 
										variant="outline" 
										size="icon" 
										@click="itemsStore.removeGhostHandle(handle.id || 0)"
										class="shrink-0"
									>
										<X class="size-4" />
									</Button>
								</div>
							</div>
						</div>
					</div>
					
					<div v-if="itemsStore.ghost_handles.length === 0" class="text-center text-muted-foreground text-sm py-4">
						Нет доступных ручек
					</div>
					
					<div v-else-if="availableGhostHandles.length === 0 && selectedGhostHandles.length === 0" class="text-center text-muted-foreground text-sm py-2">
						Все ручки добавлены
					</div>
				</div>
			</div>

			<div class="p-2 md:p-4 bg-white dark:bg-slate-900 border rounded-xl hover:shadow-2xl hover:shadow-slate-100 dark:hover:shadow-slate-800 transition-all hover:z-10">
				<div v-if="itemsStore.services.length" class="flex flex-col gap-2">
					<div>
						<span class="font-semibold">Услуги</span>
					</div>
					<div
						v-for="(services, categoryId, idx) in servicesByCategory"
						:key="categoryId"
						class="flex flex-col gap-2"
					>
						<Separator
							v-if="idx !== 0"
							class="my-0.5"
						></Separator>
						<div v-for="service in services" :key="service.id">
							<div class="flex flex-row gap-4 justify-between border rounded-lg p-2 w-full text-sm">
								<div class="flex justify-between items-start w-full">
									<div>
										<p class="font-semibold">
											<span>{{ service.vendor_code ? service.vendor_code + " - " : "" }} {{ service.name }}</span>
										</p>
										<div class="flex flex-row gap-4 items-center">
											<p class="text-muted-foreground text-xs">{{ currencyFormatter(itemsStore.itemPrice(service.id || 0)) }}/{{ service.unit }}</p>
											<span 
												v-if="itemsStore.selectedServicesID.includes(service.id || 0)"
												@click="itemsStore.toggleItemChecked(service.id || 0)" 
												class="text-blue-400 underline decoration-dotted text-xs hover:cursor-pointer select-none"
											>
												{{ itemsStore.cartItems[service.id || 0]?.checked === false ? "Учитывать" : "Не учитывать" }}
											</span>
										</div>
									</div>
									<div class="flex flex-row gap-2 items-center">
										<div class="text-right" v-if="itemsStore.selectedServicesID.includes(service.id || 0)">
											<p 
												class="font-semibold text-sm"
												:class="itemsStore.cartItems[service.id || 0]?.checked === false ? 'text-muted-foreground line-through' : ''"
											>
												{{ currencyFormatter(itemsStore.itemPrice(service.id || 0) * (itemsStore.cartItems[service.id || 0]?.quantity || 0)) }}
											</p>
											<p class="font-semibold text-xs text-muted-foreground">{{ quantityFormatter(itemsStore.cartItems[service.id || 0]?.quantity || 0) }} {{ service.unit }}</p>
										</div>
										<Checkbox :checked="itemsStore.selectedServicesID.includes(service.id || 0)" @click="toggleSelection(service.id || 0)" />
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div v-else class="flex w-full h-full items-center justify-center">
					<p class="text-accent-foreground">Нет доступных услуг для выбора</p>
				</div>
			</div>
		</div>

		<div class="flex flex-col gap-2 md:gap-4 mt-4 transition-all duration-1000">
			<div v-for="(categoryItems, categoryId) in itemsStore.additional_items" :key="categoryId" class="space-y-4">
				<h3 class="text-base font-semibold text-muted-foreground">{{ getCategoryName(categoryId) }}</h3>
				<div class="grid grid-cols-1 md:grid-cols-4 gap-2 md:gap-4">
				<div v-for="item in categoryItems" :key="item.vendor_code" class="flex flex-col justify-between p-2 md:p-4 bg-white dark:bg-slate-900 border rounded-xl hover:shadow-2xl hover:shadow-slate-100 dark:hover:shadow-slate-800 transition-all hover:z-10">
					<div class="flex justify-between items-start mb-2 md:mb-4">
						<p class="font-medium text-sm md:text-base flex-1" :class="itemsStore.cartItems[item.id || 0]?.checked === false ? 'text-muted-foreground line-through' : ''">
							<span>{{ item.vendor_code ? item.vendor_code + " - " : "" }} {{ item.name }}</span>
						</p>
						<!-- Check/Uncheck Button for Additional Item -->
						<Button 
							v-if="itemsStore.cartItems[item.id || 0]"
							:variant="(itemsStore.cartItems[item.id || 0]?.checked ?? true) ? 'outline' : 'destructive'"
							size="icon"
							class="h-6 w-6 text-xs ml-2"
							@click="itemsStore.toggleItemChecked(item.id || 0)"
						>
							<Check v-if="itemsStore.cartItems[item.id || 0]?.checked ?? true" class="w-3 h-3" />
							<X v-else class="w-3 h-3" />
						</Button>
					</div>
					<div class="flex flex-row gap-2 md:gap-4">
						<div class="basis-1/3">
							<img 
								:src="getImageSource(item.img || '')" 
								class="rounded-md border text-xs w-full cursor-pointer hover:opacity-80 transition-opacity" 
								@click="openImageModal(item)"
								:alt="item.name"
							/>
						</div>
						<div class="basis-2/3 flex flex-col justify-between gap-2">
							<div class="flex justify-between items-center text-sm text-muted-foreground">
								<span>Цена:</span>
								<span class="font-bold text-primary">{{ currencyFormatter(itemsStore.itemPrice(item.id || 0)) }}/{{ item.unit }}</span>
							</div>
							<div class="flex justify-between items-center text-sm text-muted-foreground">
								<span>Итого:</span>
								<span 
									class="font-bold"
									:class="itemsStore.cartItems[item.id || 0]?.checked === false ? 'text-muted-foreground line-through' : 'text-primary'"
								>
									{{ currencyFormatter(itemsStore.itemPrice(item.id || 0) * (itemsStore.cartItems[item.id || 0]?.quantity || 0)) }}
								</span>
							</div>
							<div>
								<NumberField 
									v-if="itemsStore.cartItems[item.id || 0]" 
									v-model="itemsStore.cartItems[item.id || 0].quantity"
									:min="0"
									:max="100"
									@update:modelValue="() => {
										if (categoryId == 30) {
											itemsStore.updateServicesQuantity(itemsStore.selectedServicesID)
										}
									}"
									:step="step(+categoryId)"
								>
									<NumberFieldContent>
										<NumberFieldDecrement />
										<NumberFieldInput class="h-8 text-center" />
										<NumberFieldIncrement />
									</NumberFieldContent>
								</NumberField>
								<Button v-else class="w-full" @click="addAdditionalItemToCart(item.id || 0, step(+categoryId))"> 
									<PlusIcon class="w-4 h-4 mr-1" /> Добавить 
								</Button>
							</div>
						</div>
					</div>
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
