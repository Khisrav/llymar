<script setup lang="ts">
import { Head, Link, usePage } from "@inertiajs/vue3"
import AuthenticatedHeaderLayout from "../../Layouts/AuthenticatedHeaderLayout.vue"
import { ArrowLeft, DraftingCompassIcon, EllipsisVerticalIcon, FolderClockIcon, ReceiptRussianRubleIcon, ScrollTextIcon, TrashIcon, CalendarIcon, UserIcon, PhoneIcon, FileTextIcon, PaletteIcon } from "lucide-vue-next"
import { ref, computed } from "vue"
import { Order, Pagination } from "../../lib/types"
import { Table, TableHeader, TableRow, TableHead, TableBody, TableCell } from "../../Components/ui/table"
import { currencyFormatter } from "../../Utils/currencyFormatter"
import { Button } from "../../Components/ui/button"
import StatusBadge from "../../Components/StatusBadge.vue"
import { DropdownMenu, DropdownMenuTrigger, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator } from "../../Components/ui/dropdown-menu"
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from "../../Components/ui/dialog"
import axios from "axios"
import { toast } from "vue-sonner"
import { Toaster } from "../../Components/ui/sonner"
import { RAL } from "ral-colors/index.js"

const page = usePage() as any
const orders = ref(page.props.orders.data as Order[])
const pagination = ref(page.props.orders.links as Pagination[])
const deleteDialogOpen = ref(false)
const orderToDelete = ref<number | null>(null)

const hasOrders = computed(() => orders.value.length > 0)

const formatDate = (dateString: string) => {
	return new Date(dateString).toLocaleDateString("ru-RU", {
		year: "numeric",
		month: "short",
		day: "numeric",
	})
}

const getRalColor = (ralCode: string | null) => {
	if (!ralCode) return null
	try {
		// @ts-ignore - RAL library doesn't have types
		const color = RAL.classic[ralCode]
		return color ? { name: ralCode, hex: color.HEX } : null
	} catch {
		return null
	}
}

declare const window: any
const downloadListPDF = (order_id: number) => {
	return window.open("/orders/" + order_id + "/list-pdf", "_blank").focus()
}

const visitSketcherPage = (order_id: number) => {
	window.location.href = "/app/orders/sketcher/" + order_id
}

const createContract = (order_id: number) => {
	const order = orders.value.find(o => o.id === order_id)
	if (order) {
		window.open(`/admin/contracts/create?order_id=${order_id}&order_price=${order.total_price}`, "_blank")
	}
}

const viewContract = (order_id: number) => {
	const order = orders.value.find(o => o.id === order_id)
	if (order && order.contracts && order.contracts.length > 0) {
		window.open(`/admin/contracts/${order.contracts[0].id}/edit`, "_blank")
	}
}

const hasContract = (order: Order) => {
	return order.contracts && order.contracts.length > 0
}

const openDeleteDialog = (order_id: number) => {
	orderToDelete.value = order_id
	deleteDialogOpen.value = true
}

const closeDeleteDialog = () => {
	deleteDialogOpen.value = false
	orderToDelete.value = null
}

const confirmDelete = () => {
	if (orderToDelete.value) {
		deleteOrder(orderToDelete.value)
		closeDeleteDialog()
	}
}

const deleteOrder = (order_id: number) => {
	axios.delete("/app/order/" + order_id + "/delete").then(() => {
		// Remove order from the list instead of refreshing page
		orders.value = orders.value.filter(order => order.id !== order_id)
		toast("Заказ успешно удален")
	}).catch((error) => {
		console.error(error)
		toast("Произошла ошибка при удалении заказа")
	})
}
</script>

<template>
	<Head title="История заказов" />
	<AuthenticatedHeaderLayout />
	
	<Toaster />

	<div class="container mx-auto p-0 md:p-6 lg:p-8 ">
		<div class="bg-background md:border md:rounded-2xl md:shadow-sm overflow-hidden">
			<!-- Header Section -->
			<div class="p-4 md:p-6">
				<div class="flex items-center justify-between">
					<div>
						<h1 class="text-2xl font-bold tracking-tight">История заказов</h1>
					</div>
					<!-- <div class="hidden md:block">
						<FolderClockIcon class="h-10 w-10 text-muted-foreground" />
					</div> -->
				</div>
			</div>

			<!-- Empty State -->
			<div v-if="!hasOrders" class="p-8 md:p-16">
				<div class="text-center max-w-md mx-auto">
					<div class="mb-6">
						<FolderClockIcon class="h-20 w-20 mx-auto text-muted-foreground/50" />
					</div>
					<h3 class="text-xl font-semibold mb-2">Заказов пока нет</h3>
					<p class="text-muted-foreground mb-6">Создайте свой первый заказ, используя калькулятор</p>
					<Link href="/app/calculator">
						<Button size="lg" class="gap-2">
							<ArrowLeft class="h-4 w-4" />
							Перейти к калькулятору
						</Button>
					</Link>
				</div>
			</div>

			<!-- Orders Content -->
			<div v-else class="p-4 md:p-6 lg:p-8" style="padding-top: 0px !important;">
				<!-- Mobile Cards View -->
				<div class="md:hidden space-y-4">
					<div v-for="order in orders" :key="order.id" class="border rounded-lg p-4 bg-card">
						<div class="flex items-start justify-between mb-3">
							<div class="flex items-center gap-2">
								<span class="text-sm font-mono text-muted-foreground">№</span>
								<span class="font-semibold">{{ order.order_number || order.id }}</span>
							</div>
							<StatusBadge :status="order.status" />
						</div>
						
						<div class="space-y-3">
							<div class="flex items-center gap-2 text-sm">
								<CalendarIcon class="h-4 w-4 text-muted-foreground" />
								<span>{{ formatDate(order.created_at || '') }}</span>
							</div>
							
							<div class="flex items-center gap-2 text-sm">
								<UserIcon class="h-4 w-4 text-muted-foreground" />
								<span class="font-medium">{{ order.customer_name }}</span>
							</div>
							
							<div class="flex items-center gap-2 text-sm">
								<PhoneIcon class="h-4 w-4 text-muted-foreground" />
								<a :href="'tel:' + order.customer_phone" class="text-primary hover:underline">
									{{ order.customer_phone }}
								</a>
							</div>

							<!-- RAL Color Display for Mobile -->
							<div v-if="order.ral_code && getRalColor(order.ral_code)" class="flex items-center gap-2 text-sm">
								<PaletteIcon class="h-4 w-4 text-muted-foreground" />
								<div class="flex items-center gap-2">
									<div 
										class="w-4 h-4 rounded border border-border" 
										:style="{ backgroundColor: getRalColor(order.ral_code)?.hex }"
									></div>
									<span class="text-xs">{{ order.ral_code }}</span>
								</div>
							</div>
							
							<div class="flex items-center justify-between pt-2 border-t">
								<div class="text-lg font-semibold">
									{{ currencyFormatter(order.total_price) }}
								</div>
								<DropdownMenu>
									<DropdownMenuTrigger>
										<Button variant="outline" size="sm">
											<EllipsisVerticalIcon class="h-4 w-4" />
										</Button>
									</DropdownMenuTrigger>
									<DropdownMenuContent align="end">
										<DropdownMenuLabel>Действия</DropdownMenuLabel>
										<DropdownMenuSeparator />
										<DropdownMenuItem @click="visitSketcherPage(order.id)">
											<DraftingCompassIcon class="h-4 w-4" />
											<span>Чертеж</span>
										</DropdownMenuItem>
										<DropdownMenuItem @click="downloadListPDF(order.id)">
											<ScrollTextIcon class="h-4 w-4" />
											<span>Спецификация</span>
										</DropdownMenuItem>
										<DropdownMenuItem>
											<ReceiptRussianRubleIcon class="h-4 w-4" />
											<span>Счет</span>
										</DropdownMenuItem>
										<!-- <DropdownMenuSeparator />
										<DropdownMenuItem v-if="!hasContract(order)" @click="createContract(order.id)">
											<FileTextIcon class="h-4 w-4" />
											<span>Создать договор</span>
										</DropdownMenuItem>
										<DropdownMenuItem v-else @click="viewContract(order.id)">
											<FileTextIcon class="h-4 w-4" />
											<span>Просмотр договора</span>
										</DropdownMenuItem> -->
										<DropdownMenuSeparator />
										<DropdownMenuItem @click="openDeleteDialog(order.id)" class="text-destructive focus:text-destructive hover:bg-destructive/10">
											<TrashIcon class="h-4 w-4" />
											<span>Удалить</span>
										</DropdownMenuItem>
									</DropdownMenuContent>
								</DropdownMenu>
							</div>
						</div>
					</div>
				</div>

				<!-- Desktop Table View -->
				<div class="hidden md:block">
					<div class="rounded-lg border overflow-hidden">
						<Table>
							<TableHeader>
								<TableRow class="bg-muted/20">
									<TableHead class="font-semibold">№ заказа</TableHead>
									<TableHead class="font-semibold">Дата</TableHead>
									<TableHead class="font-semibold">Получатель</TableHead>
									<TableHead class="font-semibold">Цвет RAL</TableHead>
									<TableHead class="font-semibold">Сумма</TableHead>
									<TableHead class="font-semibold">Статус</TableHead>
									<TableHead class="font-semibold text-right">Действия</TableHead>
								</TableRow>
							</TableHeader>
							<TableBody>
								<TableRow v-for="order in orders" :key="order.id" class="group hover:bg-muted/30 transition-colors">
									<TableCell class="font-mono">{{ order.order_number || order.id }}</TableCell>
									<TableCell class="text-muted-foreground">{{ formatDate(order.created_at || '') }}</TableCell>
									<TableCell>
										<div class="space-y-1">
											<p class="font-medium">{{ order.customer_name }}</p>
											<a :href="'tel:' + order.customer_phone" class="text-sm text-primary hover:underline font-mono">
												{{ order.customer_phone }}
											</a>
										</div>
									</TableCell>
									<TableCell>
										<div v-if="order.ral_code && getRalColor(order.ral_code)" class="flex items-center gap-2">
											<div 
												class="w-5 h-5 rounded border border-border" 
												:style="{ backgroundColor: getRalColor(order.ral_code)?.hex }"
											></div>
											<span class="text-sm">{{ order.ral_code }}</span>
										</div>
										<span v-else class="text-muted-foreground text-sm">—</span>
									</TableCell>
									<TableCell class="font-semibold">{{ currencyFormatter(order.total_price) }}</TableCell>
									<TableCell>
										<StatusBadge :status="order.status" />
									</TableCell>
									<TableCell class="text-right">
										<DropdownMenu>
											<DropdownMenuTrigger>
												<Button variant="ghost" size="icon" class="">
													<EllipsisVerticalIcon class="h-4 w-4" />
												</Button>
											</DropdownMenuTrigger>
											<DropdownMenuContent align="end">
												<DropdownMenuLabel>Действия</DropdownMenuLabel>
												<DropdownMenuSeparator />
												<DropdownMenuItem @click="visitSketcherPage(order.id)">
													<DraftingCompassIcon class="h-4 w-4" />
													<span>Чертеж</span>
												</DropdownMenuItem>
												<DropdownMenuItem @click="downloadListPDF(order.id)">
													<ScrollTextIcon class="h-4 w-4" />
													<span>Спецификация</span>
												</DropdownMenuItem>
												<DropdownMenuItem>
													<ReceiptRussianRubleIcon class="h-4 w-4" />
													<span>Счет</span>
												</DropdownMenuItem>
												<!-- <DropdownMenuSeparator />
												<DropdownMenuItem v-if="!hasContract(order)" @click="createContract(order.id)">
													<FileTextIcon class="h-4 w-4" />
													<span>Создать договор</span>
												</DropdownMenuItem>
												<DropdownMenuItem v-else @click="viewContract(order.id)">
													<FileTextIcon class="h-4 w-4" />
													<span>Просмотр договора</span>
												</DropdownMenuItem> -->
												<DropdownMenuSeparator />
												<DropdownMenuItem @click="openDeleteDialog(order.id)" class="text-destructive focus:text-destructive hover:bg-destructive/10 focus:bg-destructive/10">
													<TrashIcon class="h-4 w-4" />
													<span>Удалить</span>
												</DropdownMenuItem>
											</DropdownMenuContent>
										</DropdownMenu>
									</TableCell>
								</TableRow>
							</TableBody>
						</Table>
					</div>
				</div>

				<!-- Pagination -->
				<div class="mt-6 md:mt-8 flex justify-center">
					<div class="flex items-center gap-1">
						<template v-for="link in pagination" :key="link.label">
							<Button 
								v-if="link.url"
								:disabled="!link.url" 
								:variant="link.active ? 'default' : 'outline'" 
								size="sm"
								@click="$inertia.visit(link.url || '#')"
								class="min-w-[2.5rem]"
							>
								<span v-html="link.label" />
							</Button>
							<span v-else class="px-3 py-1.5 text-sm text-muted-foreground" v-html="link.label" />
						</template>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Delete Confirmation Dialog -->
	<Dialog v-model:open="deleteDialogOpen">
		<DialogContent class="sm:max-w-md">
			<DialogHeader>
				<DialogTitle>Подтвердите удаление</DialogTitle>
				<DialogDescription>
					Вы действительно хотите удалить заказ №{{ orders.find(o => o.id === orderToDelete)?.order_number || orderToDelete }}? 
					Это действие нельзя отменить.
				</DialogDescription>
			</DialogHeader>
			<DialogFooter class="flex gap-2">
				<Button variant="outline" @click="closeDeleteDialog">
					Отмена
				</Button>
				<Button variant="destructive" @click="confirmDelete">
					Удалить
				</Button>
			</DialogFooter>
		</DialogContent>
	</Dialog>
</template>
