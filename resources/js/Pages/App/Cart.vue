<script setup lang="ts">
import { computed, ref } from "vue"
import { ShoppingCartIcon, ArrowLeft } from "lucide-vue-next"
import { Head, Link, usePage } from "@inertiajs/vue3"
import { router } from "@inertiajs/vue3"
import AuthenticatedHeaderLayout from "../../Layouts/AuthenticatedHeaderLayout.vue"
import Button from "../../Components/ui/button/Button.vue"
import { useItemsStore } from "../../Stores/itemsStore"
import { Category, Item, OpeningType, User, WholesaleFactor } from "../../lib/types"
import CartItem from "../../Components/Cart/CartItem.vue"
import { currencyFormatter } from "../../Utils/currencyFormatter"
import Input from "../../Components/ui/input/Input.vue"
import Label from "../../Components/ui/label/Label.vue"
import { vMaska } from "maska/vue"
import { useOpeningStore } from "../../Stores/openingsStore"
import Swal from "sweetalert2"

const itemsStore = useItemsStore()
const openingsStore = useOpeningStore();

itemsStore.items = usePage().props.items as Item[]
itemsStore.additional_items = usePage().props.additional_items as Item[]
itemsStore.glasses = usePage().props.glasses as Item[]
itemsStore.services = usePage().props.services as Item[]
itemsStore.user = usePage().props.user as User
itemsStore.categories = usePage().props.categories as Category[]
itemsStore.wholesale_factor = usePage().props.wholesale_factor as WholesaleFactor

itemsStore.initiateCartItems()

const cartItemIDs = computed(() => Object.keys(itemsStore.cartItems).map(Number))

const item = (itemID: number): Item | null => itemsStore.getItemInfo(itemID) ?? null

// SNP for Surname Name Patronymic
const snp = ref({
	surname: itemsStore.user.name.split(" ")[0],
	name: itemsStore.user.name.split(" ")[1],
	patronymic: itemsStore.user.name.split(" ")[2],
})

const getOpeningName = (type: OpeningType): string => {
	return openingsStore.openingTypes[type]
}

const order_info = computed(() => ({
	name: `${snp.value.surname || ""} ${snp.value.name || ""} ${snp.value.patronymic || ""}`.trim(),
	phone: itemsStore.user.phone,
	address: itemsStore.user.address,
	email: itemsStore.user.email,
}))

const checkout = () => {
	const formData = {
		name: order_info.value.name,
		phone: order_info.value.phone,
		address: order_info.value.address,
		email: order_info.value.email,
		cart_items: itemsStore.cartItems,
		openings: openingsStore.openings,
		total_price: itemsStore.total_price.with_discount,
	}

	router.post("/app/checkout", formData as any, {
		onSuccess: () => {
			sessionStorage.removeItem('openings')
			sessionStorage.removeItem('cartItems')
			
			Swal.fire({
				title: 'Заказ создан',
				icon: 'success',
				confirmButtonText: 'Закрыть',
			})
		},
		onError: (errors: any) => {
			console.log(errors)
			
			Swal.fire({
				title: 'Ошибка при создании заказа',
				icon: 'error',
				confirmButtonText: 'Закрыть',
			})
		},
	})
}
</script>

<template>
	<Head title="Корзина" />
	<AuthenticatedHeaderLayout />
	<div class="container p-0 md:p-4">
		<div class="p-4 md:p-8 md:mt-8 md:border rounded-2xl bg-background">
			<div class="flex items-center gap-4 mb-6">
				<Button as="a" href="/app/calculator" size="icon" variant="outline">
					<ArrowLeft />
				</Button>
				<h2 v-if="cartItemIDs.length > 0" class="text-3xl font-semibold">Ваша корзина</h2>
			</div>
			<div v-if="cartItemIDs.length === 0" class="text-center py-8">
				<ShoppingCartIcon class="h-16 w-16 mx-auto mb-4" />
				<p class="text-lg">Ваша корзина пуста</p>
				<Link href="/app/calculator" class="inline-block mt-4">
					<Button variant="secondary"> <ArrowLeft class="mr-2" /> Перейти к калькулятору </Button>
				</Link>
			</div>
			<div v-else class="flex flex-col md:flex-row md:space-x-8">
				<!-- Cart Items List -->
				<div class="md:w-2/3">
					<ul class="divide-y divide-gray-200 dark:divide-gray-700">
						<li v-for="itemID in cartItemIDs" :key="itemID" class="py-6 flex items-center">
							<CartItem :item="item(itemID) as any" />
						</li>
					</ul>
				</div>

				<!-- Order Summary -->
				<div class="md:w-1/3 mt-8 md:mt-0">
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
							<p class="">Всего</p>
							<p class="">{{ currencyFormatter(itemsStore.total_price.with_discount) }}</p>
						</div>
						<!-- Additional order details -->
						<div class="border-t border-gray-200 dark:border-gray-700 mt-4 pt-4 flex justify-between items-center">
							<p class="text-lg font-semibold">Итого</p>
							<p class="text-xl font-semibold">{{ currencyFormatter(itemsStore.total_price.with_discount) }}</p>
						</div>
					</div>

					<form @submit.prevent="checkout" class="bg-slate-50 dark:bg-slate-900 rounded-lg p-4 md:p-6 mt-4 md:mt-6 flex flex-col gap-4">
						<h3 class="text-lg font-semibold">Информация о клиенте</h3>

						<div>
							<Label class="inline-block mb-2"> Фамилия <span class="text-destructive dark:text-red-500">*</span> </Label>
							<Input type="text" v-model="snp.surname" placeholder="Иванов" required />
						</div>

						<div>
							<Label class="inline-block mb-2"> Имя <span class="text-destructive dark:text-red-500">*</span> </Label>
							<Input type="text" v-model="snp.name" placeholder="Иван" required />
						</div>

						<div>
							<Label class="inline-block mb-2">Отчество</Label>
							<Input type="text" v-model="snp.patronymic" placeholder="Иванович" />
						</div>

						<div>
							<Label class="inline-block mb-2"> Адрес <span class="text-destructive dark:text-red-500">*</span> </Label>
							<Input type="text" v-model="order_info.address" placeholder="г. Москва, ул. Пушкинская, д. 1" required />
						</div>

						<div>
							<Label class="inline-block mb-2"> Email <span class="text-destructive dark:text-red-500">*</span> </Label>
							<Input type="email" v-model="order_info.email" placeholder="email@mail.ru" required />
						</div>

						<div>
							<Label class="inline-block mb-2"> Телефон <span class="text-destructive dark:text-red-500">*</span> </Label>
							<Input type="tel" v-model="order_info.phone" v-maska="'+7 (###) ###-##-##'" placeholder="+7 (999) 999-99-99" required />
						</div>

						<div>
							<Button type="submit">Оформить заказ</Button>
							<p class="text-xs block mt-4">Нажимая кнопку "Оформить заказ", вы даете согласие на обработку персональных данных.</p>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</template>
