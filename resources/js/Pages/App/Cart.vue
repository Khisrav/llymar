<script setup lang="ts">
import { computed } from "vue"
import { ShoppingCartIcon, ArrowLeft } from "lucide-vue-next"
import { Head, Link, usePage } from "@inertiajs/vue3"
import AuthenticatedHeaderLayout from "../../Layouts/AuthenticatedHeaderLayout.vue"
import Button from "../../Components/ui/button/Button.vue"
import { useItemsStore } from "../../Stores/itemsStore"
import { Item } from "../../lib/types"
import CartItem from "../../Components/Cart/CartItem.vue"
import { currencyFormatter } from "../../Utils/currencyFormatter"

const itemsStore = useItemsStore()
itemsStore.items = usePage().props.items as Item[]
itemsStore.additional_items = usePage().props.additional_items as Item[]
itemsStore.glasses = usePage().props.glasses as Item[]
itemsStore.services = usePage().props.services as Item[]
itemsStore.user_discount = usePage().props.user_discount as number
itemsStore.initiateCartItems()

const cartItemIDs = computed(() => Object.keys(itemsStore.cartItems).map(Number))

const item = (itemID: number): Item | null => itemsStore.getItemInfo(itemID) ?? null

const subtotal = computed(() => {
    return cartItemIDs.value.reduce((sum, id) => {
	    const cartItem = itemsStore.cartItems[id]
	    const itemInfo = item(id)
	    if (cartItem && itemInfo) {
	        return sum + cartItem.quantity * (itemInfo.retail_price || 0)
	    }
	    return sum
    }, 0)
})

const checkout = () => {
    console.log("Proceeding to checkout...")
}
</script>

<template>
	<Head title="Корзина" />
	<AuthenticatedHeaderLayout />
	<div class="container p-4 rounded-xl">
		<div class="p-6 md:p-8 md:mt-8 border rounded-2xl">
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
					<ul class="divide-y divide-gray-200">
						<li v-for="itemID in cartItemIDs" :key="itemID" class="py-6 flex items-center">
							<CartItem :item="item(itemID)" />
						</li>
					</ul>
				</div>

				<!-- Order Summary -->
				<div class="md:w-1/3 mt-8 md:mt-0">
					<div class="bg-slate-50 dark:bg-slate-900 rounded-lg p-6">
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
						<div class="border-t border-gray-200 mt-4 pt-4 flex justify-between items-center">
							<p class="text-lg font-semibold">Итого</p>
							<p class="text-xl font-semibold">{{ currencyFormatter(itemsStore.total_price.with_discount) }}</p>
						</div>
						<!-- <Button @click="checkout" class="mt-6">Proceed to Checkout</Button> -->
					</div>
				</div>
			</div>
		</div>
	</div>
</template>
