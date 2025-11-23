<script setup lang="ts">
import { computed, ref } from "vue"
import { ShoppingCartIcon, ArrowLeft } from "lucide-vue-next"
import { Head, Link, usePage } from "@inertiajs/vue3"
import { router } from "@inertiajs/vue3"
import AuthenticatedHeaderLayout from "../../Layouts/AuthenticatedHeaderLayout.vue"
import Button from "../../Components/ui/button/Button.vue"
import { useItemsStore } from "../../Stores/itemsStore"
import { Category, Item, OpeningType, User } from "../../lib/types"
import CartItem from "../../Components/Cart/CartItem.vue"
import { currencyFormatter } from "../../Utils/currencyFormatter"
import Input from "../../Components/ui/input/Input.vue"
import Label from "../../Components/ui/label/Label.vue"
import { vMaska } from "maska/vue"
import { useOpeningStore } from "../../Stores/openingsStore"
import { RAL } from "ral-colors/index.js";
import Popover from "../../Components/ui/popover/Popover.vue"
import PopoverContent from "../../Components/ui/popover/PopoverContent.vue"
import Command from "../../Components/ui/command/Command.vue"
import CommandInput from "../../Components/ui/command/CommandInput.vue"
import CommandList from "../../Components/ui/command/CommandList.vue"
import CommandItem from "../../Components/ui/command/CommandItem.vue"
import PopoverTrigger from "../../Components/ui/popover/PopoverTrigger.vue"
import CommandGroup from "../../Components/ui/command/CommandGroup.vue"
import Select from "../../Components/ui/select/Select.vue"
import SelectContent from "../../Components/ui/select/SelectContent.vue"
import SelectItem from "../../Components/ui/select/SelectItem.vue"
import SelectTrigger from "../../Components/ui/select/SelectTrigger.vue"
import SelectValue from "../../Components/ui/select/SelectValue.vue"

const itemsStore = useItemsStore()
const openingsStore = useOpeningStore()
const open = ref(false)
const selectedRALColor = ref({ name: "Выберите цвет", HEX: "none" })
const selectedDealerId = ref<string>("")
const { user_default_factor, dealers, can_select_dealer, user_role } = usePage().props as any

// Debug logging
console.log('Cart debug:', { dealers, can_select_dealer, dealersLength: dealers?.length })

itemsStore.items = usePage().props.items as Item[]
itemsStore.additional_items = usePage().props.additional_items as { [key: number]: Item[] }
itemsStore.glasses = usePage().props.glasses as Item[]
itemsStore.services = usePage().props.services as Item[]
itemsStore.user = usePage().props.user as User
itemsStore.categories = usePage().props.categories as Category[]

// Initialize user's default factor
itemsStore.initializeUserFactor(user_default_factor || 'pz')



itemsStore.initiateCartItems()

const cartItemIDs = computed(() => Object.keys(itemsStore.cartItems).map(Number))

const item = (itemID: number): Item | null => itemsStore.getItemInfo(itemID) ?? null

const getOpeningName = (type: OpeningType): string => openingsStore.openingTypes[type]

// SNP for Surname Name Patronymic
const snp = ref({
	// surname: itemsStore.user.name.split(" ")[0],
	// name: itemsStore.user.name.split(" ")[1],
	// patronymic: itemsStore.user.name.split(" ")[2],
	surname: "",
	name: "",
	patronymic: "",
})

const order_info = computed(() => ({
	name: `${snp.value.surname || ""} ${snp.value.name || ""} ${snp.value.patronymic || ""}`.trim(),
	// phone: itemsStore.user.phone,
	// address: itemsStore.user.address,
	// email: itemsStore.user.email,
	phone: '',
	address: '',
	email: '',
	color: "",
}))

const checkout = () => {
	const formData = {
		// name: order_info.value.name,
		// phone: order_info.value.phone,
		// address: order_info.value.address,
		// email: order_info.value.email,
		cart_items: itemsStore.cartItems,
		openings: openingsStore.openings,
		total_price: itemsStore.total_price.with_discount,
		ral_code: selectedRALColor.value.name === "Выберите цвет" ? "" : selectedRALColor.value.name,
		selected_factor: itemsStore.selectedFactor,
		selected_dealer_id: selectedDealerId.value ? parseInt(selectedDealerId.value) : null,
	}

	if (selectedRALColor.value.HEX === "none" && itemsStore.cartItems[386]?.quantity > 0) {
		alert("Выберите цвет")
		return
	}

	router.post("/app/checkout", formData as any, {
		onSuccess: () => {
			sessionStorage.removeItem("openings")
			sessionStorage.removeItem("cartItems")
		},
		onError: (errors: any) => {
			console.log(errors)
		},
	})
}
</script>

<template>
	<Head title="Корзина" />
	<AuthenticatedHeaderLayout />
	<div class="container p-0 md:p-4 mb-8">
		<div class="p-4 md:p-8 md:mt-8 md:border rounded-2xl bg-background">
			<div class="flex items-center gap-4 mb-6">
				<Link href="/app/calculator"
					><Button size="icon" variant="outline"><ArrowLeft /></Button
				></Link>
				<!-- <h2 v-if="cartItemIDs.length > 0" class="text-3xl font-semibold">Корзина</h2> -->
				<h2 v-if="cartItemIDs.length > 0" class="text-3xl font-semibold">Заказ</h2>
			</div>
			<div v-if="cartItemIDs.length === 0" class="text-center py-8">
				<ShoppingCartIcon class="h-16 w-16 mx-auto mb-4" />
				<p class="text-lg">Ваша корзина пуста</p>
				<Link href="/app/calculator" class="inline-block mt-4">
					<Button variant="secondary"> <ArrowLeft class="mr-2" /> Перейти к калькулятору </Button>
				</Link>
			</div>
			<div v-else class="flex flex-col md:flex-row md:space-x-8">
				<div class="md:w-2/3">
					<ul class="divide-y divide-gray-200 dark:divide-gray-700">
						<li v-for="itemID in cartItemIDs" :key="itemID" class="py-2.5 flex items-center">
							<CartItem v-if="itemID != -1 && item(itemID)" :item="item(itemID)!" :disabled="itemsStore.glasses.some((glass) => glass.id === itemID)" />
						</li>
					</ul>
				</div>

				<div class="md:w-1/3 mt-8 md:mt-0 md:sticky md:top-20 md:self-start md:max-h-[calc(100vh-4rem)] md:overflow-y-auto">
					<div class="bg-slate-50 dark:bg-slate-900 rounded-lg p-4 md:p-6 space-y-4">
						<h3 class="text-lg font-semibold">Проемы</h3>
						<div v-for="opening in openingsStore.openings" :key="opening.type" class="">
							<div>
								<div>{{ getOpeningName(opening.type) }}</div>
								<div class="text-muted-foreground flex flex-row items-center justify-between">
									<span>{{ opening.width }} мм <span class="text-xs">✕</span> {{ opening.height }} мм</span>
									<span v-if="!['blind-glazing', 'triangle'].includes(opening.type)">{{ opening.doors }} ств.</span>
								</div>
							</div>
						</div>
					</div>
					<div class="bg-slate-50 dark:bg-slate-900 rounded-lg p-4 md:p-6 mt-6">
						<h3 class="text-lg font-semibold mb-4">Сводка по заказу</h3>
						<div class="flex justify-between mb-2">
							<p>Всего</p>
							<p>{{ currencyFormatter(itemsStore.total_price.with_discount) }}</p>
						</div>

						<div class="border-t border-gray-200 dark:border-gray-700 mt-4 pt-4 flex justify-between items-center">
							<p class="text-lg font-semibold">Итого</p>
							<p class="text-xl font-semibold">
								{{ currencyFormatter(itemsStore.total_price.with_discount) }}
							</p>
						</div>
					</div>

					<form @submit.prevent="checkout" class="bg-slate-50 dark:bg-slate-900 rounded-lg p-4 md:p-6 mt-4 md:mt-6 flex flex-col gap-4">
						<!-- Dealer Selector -->
						<div v-if="can_select_dealer && dealers && dealers.length > 0">
							<div class="space-y-4">
								<Label class="inline-block">Дилер <span v-if="user_role === 'ROP'" class="text-destructive dark:text-red-500">*</span></Label>
								<Select 
									:required="user_role === 'ROP'"
									:model-value="selectedDealerId" 
									@update:model-value="(value: string) => selectedDealerId = value"
								>
									<SelectTrigger>
										<SelectValue :placeholder="user_role === 'ROP' ? 'Выберите дилера (обязательно)' : 'Выберите дилера (необязательно)'" />
									</SelectTrigger>
									<SelectContent>
										<!-- <SelectItem value="" class="text-muted-foreground">
											Не выбран (текущий пользователь)
										</SelectItem> -->
										<SelectItem v-for="dealer in dealers" :key="dealer.id" :value="dealer.id.toString()">
											#{{ dealer.id }} - {{ dealer.name }}
										</SelectItem>
									</SelectContent>
								</Select>
							</div>
						</div>

						<!-- <h3 class="text-lg font-semibold">Информация о клиенте</h3>

						<div>
							<Label class="inline-block mb-2">Фамилия<span class="text-destructive dark:text-red-500">*</span></Label>
							<Input type="text" v-model="snp.surname" placeholder="Иванов" required />
						</div>

						<div>
							<Label class="inline-block mb-2">Имя<span class="text-destructive dark:text-red-500">*</span></Label>
							<Input type="text" v-model="snp.name" placeholder="Иван" required />
						</div>

						<div>
							<Label class="inline-block mb-2">Отчество</Label>
							<Input type="text" v-model="snp.patronymic" placeholder="Иванович" />
						</div>

						<div>
							<Label class="inline-block mb-2">Адрес<span class="text-destructive dark:text-red-500">*</span></Label>
							<Input type="text" v-model="order_info.address" placeholder="г. Москва, ул. Пушкинская, д. 1" required />
						</div>

						<div>
							<Label class="inline-block mb-2">Email<span class="text-destructive dark:text-red-500">*</span></Label>
							<Input type="email" v-model="order_info.email" placeholder="email@mail.ru" required />
						</div>

						<div>
							<Label class="inline-block mb-2">Телефон<span class="text-destructive dark:text-red-500">*</span></Label>
							<Input type="tel" v-model="order_info.phone" v-maska="'+7 (###) ###-##-##'" placeholder="+7 (999) 999-99-99" required />
						</div> -->

						<div v-if="itemsStore.cartItems[386]" class="flex items-center justify-center gap-4">
							<Label class="inline-block">Цвет<span class="text-destructive dark:text-red-500">*</span></Label>

							<div>
								<Popover v-model:open="open">
									<PopoverTrigger as-child>
										<Button variant="outline" type="button" :aria-expanded="open">
											<svg width="16" height="16" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
												<circle :style="{ fill: selectedRALColor?.HEX }" cx="50" cy="50" r="45" />
											</svg>
											<span>{{ selectedRALColor?.name || "Выбрать цвет" }}</span>
										</Button>
									</PopoverTrigger>
									<PopoverContent class="w-[300px] p-0">
										<Command>
											<CommandInput placeholder="Найти цвет" />
											<CommandList>
												<CommandGroup>
													<CommandItem
														v-for="colorName in Object.keys(RAL.classic)"
														:key="colorName"
														:value="colorName"
														@select="
															() => {
																selectedRALColor = { name: colorName, HEX: RAL.classic[colorName].HEX };
																open = false;
															}
														"
														class="gap-2"
													>
														<svg width="16" height="16" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
															<circle :style="{ fill: RAL.classic[colorName].HEX }" cx="50" cy="50" r="45" />
														</svg>
														<span>{{ colorName }}</span>
													</CommandItem>
												</CommandGroup>
											</CommandList>
										</Command>
									</PopoverContent>
								</Popover>
							</div>
						</div>

						<div>
							<Button type="submit">Заказать</Button>
							<p class="text-xs block mt-4">Нажимая кнопку "Оформить заказ", вы даете согласие на обработку персональных данных.</p>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</template>
