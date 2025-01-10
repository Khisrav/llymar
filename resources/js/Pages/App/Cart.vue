<script setup lang="ts">
import { computed, ref } from "vue"
import { ShoppingCartIcon, ArrowLeft } from "lucide-vue-next"
import { Head, Link, usePage } from "@inertiajs/vue3"
import AuthenticatedHeaderLayout from "../../Layouts/AuthenticatedHeaderLayout.vue"
import Button from "../../Components/ui/button/Button.vue"
import { useItemsStore } from "../../Stores/itemsStore"
import { Item, User } from "../../lib/types"
import CartItem from "../../Components/Cart/CartItem.vue"
import { currencyFormatter } from "../../Utils/currencyFormatter"
import Input from "../../Components/ui/input/Input.vue"
import Label from "../../Components/ui/label/Label.vue"
import { vMaska } from "maska/vue"

const itemsStore = useItemsStore()
itemsStore.items = usePage().props.items as Item[]
itemsStore.additional_items = usePage().props.additional_items as Item[]
itemsStore.glasses = usePage().props.glasses as Item[]
itemsStore.services = usePage().props.services as Item[]
itemsStore.user = usePage().props.user as User
itemsStore.initiateCartItems()

const cartItemIDs = computed(() => Object.keys(itemsStore.cartItems).map(Number))

const item = (itemID: number): Item | null => itemsStore.getItemInfo(itemID) ?? null

//snp for Surname Name Patronymic
const snp = ref({
	surname: itemsStore.user.name.split(" ")[0],
	name: itemsStore.user.name.split(" ")[1],
	patronymic: itemsStore.user.name.split(" ")[2],
})

const order_info = computed(() => ({
	name: (snp.value.surname || '') + " " + (snp.value.name || '') + " " + (snp.value.patronymic || ''),
	phone: itemsStore.user.phone,
	address: itemsStore.user.address,
	email: itemsStore.user.email,
}))

const checkout = () => {
    console.log("Proceeding to checkout...")
}
</script>

<template>
	<Head title="Корзина" />
	<AuthenticatedHeaderLayout />
	<div class="container p-0 md:p-4 rounded-xl">
		<div class="p-4 md:p-8 md:mt-8 md:border rounded-2xl">
			<h2 v-if="cartItemIDs.length > 0" class="text-3xl font-semibold mb-6">Ваша корзина</h2>
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
							<CartItem :item="item(itemID)" />
						</li>
					</ul>
				</div>

				<!-- Order Summary -->
				<div class="md:w-1/3 mt-8 md:mt-0">
					<div class="bg-slate-50 dark:bg-slate-900 rounded-lg p-4 md:p-6">
						<h3 class="text-lg font-semibold mb-4">Сводка по заказу</h3>
						<div class="flex justify-between mb-2">
							<p class="">Всего</p>
							<p class="">{{ currencyFormatter(itemsStore.total_price.without_discount) }}</p>
						</div>
						<div class="flex justify-between mb-2">
							<p class="">Скидка</p>
							<p class="">{{ currencyFormatter(itemsStore.total_price.with_discount - itemsStore.total_price.without_discount) }}</p>
						</div>
						<!-- Additional order details -->
						<div class="border-t border-gray-200 dark:border-gray-700 mt-4 pt-4 flex justify-between items-center">
							<p class="text-lg font-semibold">Итого</p>
							<p class="text-xl font-semibold">{{ currencyFormatter(itemsStore.total_price.with_discount) }}</p>
						</div>
						<!-- <Button @click="checkout" class="mt-6">Proceed to Checkout</Button> -->
					</div>

					<form @submit.prevent="checkout" class="bg-slate-50 dark:bg-slate-900 rounded-lg p-4 md:p-6 mt-4 md:mt-6 flex flex-col gap-4">
						<h3 class="text-lg font-semibold">Информация о клиенте</h3>

						<div>
							<Label class="inline-block mb-2">Фамилия <span class="text-destructive dark:text-red-500">*</span></Label>
							<Input type="text" v-model="snp.surname" placeholder="Иванов" required />
						</div>

						<div>
							<Label class="inline-block mb-2">Имя <span class="text-destructive dark:text-red-500">*</span></Label>
							<Input type="text" v-model="snp.name" placeholder="Иван" required />
						</div>

						<div>
							<Label class="inline-block mb-2">Отчество</Label>
							<Input type="text" v-model="snp.patronymic" placeholder="Иванович" />
						</div>

						<div>
							<Label class="inline-block mb-2">Адрес <span class="text-destructive dark:text-red-500">*</span></Label>
							<Input type="text" v-model="order_info.address" placeholder="г. Москва, ул. Пушкинская, д. 1" required />
						</div>

						<div>
							<Label class="inline-block mb-2">Email <span class="text-destructive dark:text-red-500">*</span></Label>
							<Input type="email" v-model="order_info.email" placeholder="email@mail.ru" required />
						</div>

						<div>
							<Label class="inline-block mb-2">Телефон <span class="text-destructive dark:text-red-500">*</span></Label>
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
