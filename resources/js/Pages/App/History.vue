<script setup lang="ts">
import { Head, Link, usePage } from "@inertiajs/vue3"
import AuthenticatedHeaderLayout from "../../Layouts/AuthenticatedHeaderLayout.vue"
import { ArrowLeft, DraftingCompassIcon, EllipsisVerticalIcon, FolderClockIcon, ReceiptRussianRubleIcon, ScrollTextIcon, TrashIcon, CalendarIcon, UserIcon, PhoneIcon, FileTextIcon, PaletteIcon, SearchIcon, FilterIcon, FileType2Icon, FileAxis3DIcon } from "lucide-vue-next"
import { ref, computed } from "vue"
import { Order, Pagination } from "../../lib/types"
import { Table, TableHeader, TableRow, TableHead, TableBody, TableCell } from "../../Components/ui/table"
import { currencyFormatter } from "../../Utils/currencyFormatter"
import { Button } from "../../Components/ui/button"
import StatusBadge from "../../Components/StatusBadge.vue"
import { DropdownMenu, DropdownMenuTrigger, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator } from "../../Components/ui/dropdown-menu"
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from "../../Components/ui/dialog"
import { Input } from "../../Components/ui/input"
import axios from "axios"
import { toast } from "vue-sonner"
import { Toaster } from "../../Components/ui/sonner"

// Import RAL colors
// @ts-ignore - RAL library doesn't have proper types
import { RAL } from 'ral-colors/index.js'

const { can_access_sketcher, user_role } = usePage().props as any

const page = usePage() as any
const orders = ref(page.props.orders.data as Order[])
const pagination = ref(page.props.orders.links as Pagination[])
const deleteDialogOpen = ref(false)
const orderToDelete = ref<number | null>(null)
const searchQuery = ref("")

const hasOrders = computed(() => orders.value.length > 0)

const filteredOrders = computed(() => {
  if (!searchQuery.value) return orders.value
  
  const query = searchQuery.value.toLowerCase()
  return orders.value.filter(order => 
    order.order_number?.toString().includes(query) ||
    order.customer_name?.toLowerCase().includes(query) ||
    order.customer_phone?.includes(query) ||
    order.ral_code?.toLowerCase().includes(query)
  )
})

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

const downloadSketchPDF = async (order_id: number) => {
	try {
		toast.info("Подготовка PDF чертежа...");
		
		const response = await axios.get(`/app/orders/${order_id}/sketch-pdf`, {
			responseType: "blob",
		});

		const fileBlob = new Blob([response.data], { type: "application/pdf" });
		const fileURL = URL.createObjectURL(fileBlob);

		const link = document.createElement("a");
		link.href = fileURL;
		link.setAttribute("download", `sketch_${order_id}.pdf`);
		document.body.appendChild(link);
		link.click();

		document.body.removeChild(link);
		URL.revokeObjectURL(fileURL);
		toast.success("PDF чертеж успешно загружен.");
	} catch (error) {
		console.error("Failed to download sketch PDF:", error);
		toast.error("Ошибка при загрузке PDF чертежа.");
	}
}

const downloadSketchDXF = async (order_id: number) => {
	try {
		toast.info("Подготовка DXF чертежа...");
		
		const response = await axios.get(`/app/orders/${order_id}/sketch-dxf`, {
			responseType: "blob",
		});

		const fileBlob = new Blob([response.data], { type: "application/dxf" });
		const fileURL = URL.createObjectURL(fileBlob);

		const link = document.createElement("a");
		link.href = fileURL;
		link.setAttribute("download", `sketch_${order_id}.dxf`);
		document.body.appendChild(link);
		link.click();

		document.body.removeChild(link);
		URL.revokeObjectURL(fileURL);
		toast.success("DXF чертеж успешно загружен.");
	} catch (error) {
		console.error("Failed to download sketch DXF:", error);
		if (axios.isAxiosError(error) && error.response?.status === 403) {
			toast.error("У вас нет доступа для скачивания DXF.");
		} else {
			toast.error("Ошибка при загрузке DXF чертежа.");
		}
	}
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

const canDeleteOrder = (order: Order) => order.status == 'created'
const canDownloadSketchPDF_DXF = (order: Order) => order.status != 'created'
const canDownloadListPDF = (order: Order) => true //i.e. always
const canDownloadBill = (order: Order) => order.status == 'created'


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

const downloadBill = (order_id: number) => {
	window.open("/orders/" + order_id + "/download-bill", "_blank").focus()
}
</script>

<template>
	<Head title="История заказов" />
	<AuthenticatedHeaderLayout />
	
	<Toaster />

	<div class="container mx-auto p-0 md:p-6 lg:p-8">
		<div class="bg-background md:border md:rounded-2xl md:shadow-sm overflow-hidden">
			<!-- Header Section -->
			<div class="p-4 md:p-8">
				<div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
					<div class="flex items-center gap-3">
						<div class="p-2 bg-primary/10 rounded-lg">
							<FolderClockIcon class="h-6 w-6 text-primary" />
						</div>
						<div>
							<h1 class="text-2xl font-bold tracking-tight">Заказы</h1>
							<p class="text-sm text-muted-foreground mt-1">
								{{ hasOrders ? `${orders.length} заказов` : 'Управляйте своими заказами' }}
							</p>
						</div>
					</div>
					
					<!-- Search Bar -->
					<div v-if="hasOrders" class="relative w-full md:w-80">
						<SearchIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
						<Input
							v-model="searchQuery"
							placeholder="Поиск по номеру, клиенту, телефону..."
							class="pl-10"
						/>
					</div>
				</div>
			</div>

			<!-- Empty State -->
			<div v-if="!hasOrders" class="p-8 md:p-16">
				<div class="text-center max-w-md mx-auto">
					<div class="mb-6">
						<div class="mx-auto w-20 h-20 bg-muted/50 rounded-full flex items-center justify-center mb-4">
							<FolderClockIcon class="h-10 w-10 text-muted-foreground" />
						</div>
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

			<!-- No Search Results -->
			<div v-else-if="hasOrders && filteredOrders.length === 0" class="p-8 md:p-16">
				<div class="text-center max-w-md mx-auto">
					<div class="mb-6">
						<div class="mx-auto w-16 h-16 bg-muted/50 rounded-full flex items-center justify-center mb-4">
							<SearchIcon class="h-8 w-8 text-muted-foreground" />
						</div>
					</div>
					<h3 class="text-lg font-semibold mb-2">Ничего не найдено</h3>
					<p class="text-muted-foreground mb-4">Попробуйте изменить поисковый запрос</p>
					<Button variant="outline" @click="searchQuery = ''">
						Очистить поиск
					</Button>
				</div>
			</div>

			<!-- Orders Content -->
			<div v-else class="p-4 md:p-6 lg:p-8" style="padding-top: 0px !important;">
				<!-- Mobile Cards View -->
				<div class="md:hidden space-y-4">
					<div 
						v-for="(order, index) in filteredOrders" 
						:key="order.id" 
						class="border rounded-xl p-4 bg-card transition-all duration-200 hover:border-border/60"
						:style="{ animationDelay: `${index * 50}ms` }"
					>
						<div class="flex items-start justify-between mb-4">
							<div class="flex items-center gap-2">
								<span class="text-xs font-mono text-muted-foreground bg-muted px-2 py-1 rounded">№</span>
								<Link :href="`/app/orders/${order.id}`" class="font-semibold text-lg hover:text-primary transition-colors border-b-2 border-primary border-dotted">
									{{ order.order_number || order.id }}
								</Link>
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
								<a :href="'tel:' + order.customer_phone" class="text-primary hover:underline font-mono">
									{{ order.customer_phone }}
								</a>
							</div>

							<!-- RAL Color Display for Mobile -->
							<div v-if="order.ral_code && getRalColor(order.ral_code)" class="flex items-center gap-2 text-sm">
								<PaletteIcon class="h-4 w-4 text-muted-foreground" />
								<div class="flex items-center gap-2">
									<div 
										class="w-4 h-4 rounded border border-border shadow-sm" 
										:style="{ backgroundColor: getRalColor(order.ral_code)?.hex }"
									></div>
									<span class="text-xs font-mono">{{ order.ral_code }}</span>
								</div>
							</div>
							
							<div class="flex items-center justify-between pt-3 border-t">
								<div class="text-lg font-bold text-primary">
									{{ currencyFormatter(order.total_price) }}
								</div>
								<DropdownMenu>
									<DropdownMenuTrigger>
										<Button variant="outline" size="sm" class="h-8 w-8 p-0">
											<EllipsisVerticalIcon class="h-4 w-4" />
										</Button>
									</DropdownMenuTrigger>
									<DropdownMenuContent align="end">
										<DropdownMenuLabel>Действия</DropdownMenuLabel>
										<DropdownMenuSeparator />
										<DropdownMenuItem v-if="can_access_sketcher" @click="visitSketcherPage(order.id)">
											<DraftingCompassIcon class="h-4 w-4" />
											<span>Чертеж</span>
										</DropdownMenuItem>
										<DropdownMenuItem @click="downloadSketchPDF(order.id)">
											<FileType2Icon class="h-4 w-4" />
											<span>Чертеж PDF</span>
										</DropdownMenuItem>
										<DropdownMenuItem @click="downloadSketchDXF(order.id)">
											<FileAxis3DIcon class="h-4 w-4" />
											<span>Чертеж DXF</span>
										</DropdownMenuItem>
										<DropdownMenuSeparator />
										<DropdownMenuItem @click="downloadListPDF(order.id)">
											<ScrollTextIcon class="h-4 w-4" />
											<span>Спецификация</span>
										</DropdownMenuItem>
										<DropdownMenuItem @click="downloadBill(order.id)">
											<ReceiptRussianRubleIcon class="h-4 w-4" />
											<span>Оплатить</span>
										</DropdownMenuItem>
										<DropdownMenuSeparator v-if="canDeleteOrder(order)" />
										<DropdownMenuItem v-if="canDeleteOrder(order)" @click="openDeleteDialog(order.id)" class="text-destructive focus:text-destructive hover:bg-destructive/10">
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
					<div class="rounded-lg border overflow-hidden shadow-sm">
						<Table>
							<TableHeader>
								<TableRow class="bg-muted/30 hover:bg-muted/30">
									<TableHead class="font-semibold text-sm"></TableHead>
									<TableHead class="font-semibold text-sm">№ заказа <br> Дата</TableHead>
									<TableHead v-if="!['Manager', 'Workman'].includes(user_role)" class="font-semibold text-sm">Закреплен за</TableHead>
									<TableHead class="font-semibold text-sm">Получатель</TableHead>
									<TableHead class="font-semibold text-sm">Цвет RAL</TableHead>
									<TableHead class="font-semibold text-sm">Сумма</TableHead>
									<TableHead class="font-semibold text-sm">Статус</TableHead>
								</TableRow>
							</TableHeader>
							<TableBody>
								<TableRow 
									v-for="order in filteredOrders" 
									:key="order.id" 
									class="group hover:bg-muted/20 transition-colors duration-150"
								>
									<TableCell class="">
										<DropdownMenu>
											<DropdownMenuTrigger>
												<Button variant="outline" size="icon" class="h-8 w-8">
													<EllipsisVerticalIcon class="h-4 w-4" />
												</Button>
											</DropdownMenuTrigger>
											<DropdownMenuContent align="end">
												<DropdownMenuLabel>Действия</DropdownMenuLabel>
												<DropdownMenuSeparator />
												<DropdownMenuItem v-if="can_access_sketcher" @click="visitSketcherPage(order.id)">
													<DraftingCompassIcon class="h-4 w-4" />
													<span>Чертеж</span>
												</DropdownMenuItem>
												<DropdownMenuItem v-if="canDownloadSketchPDF_DXF(order)" @click="downloadSketchPDF(order.id)">
													<FileType2Icon class="h-4 w-4" />
													<span>Чертеж PDF</span>
												</DropdownMenuItem>
												<DropdownMenuItem v-if="canDownloadSketchPDF_DXF(order)" @click="downloadSketchDXF(order.id)">
													<FileAxis3DIcon class="h-4 w-4" />
													<span>Чертеж DXF</span>
												</DropdownMenuItem>
												<DropdownMenuSeparator />
												<DropdownMenuItem v-if="canDownloadListPDF(order)" @click="downloadListPDF(order.id)">
													<ScrollTextIcon class="h-4 w-4" />
													<span>Спецификация</span>
												</DropdownMenuItem>
												<DropdownMenuItem v-if="canDownloadBill(order)" @click="downloadBill(order.id)">
													<ReceiptRussianRubleIcon class="h-4 w-4" />
													<span>Оплатить</span>
												</DropdownMenuItem>
												<DropdownMenuSeparator v-if="canDeleteOrder(order)" />
												<DropdownMenuItem v-if="canDeleteOrder(order)" @click="openDeleteDialog(order.id)" class="text-destructive focus:text-destructive hover:bg-destructive/10 focus:bg-destructive/10">
													<TrashIcon class="h-4 w-4" />
													<span>Удалить</span>
												</DropdownMenuItem>
											</DropdownMenuContent>
										</DropdownMenu>
									</TableCell>
									<TableCell class="">
										<Link :href="`/app/orders/${order.id}`" class="font-mono font-medium hover:text-primary transition-colors border-b-2 border-primary border-dotted">
											{{ order.order_number || order.id }}
										</Link>
										<br>
										<span class="text-muted-foreground text-sm">{{ formatDate(order.created_at || '') }}</span>
									</TableCell>
									<TableCell v-if="!['Manager', 'Workman'].includes(user_role)" class="text-muted-foreground text-sm">
										<div class="space-y-1">
											<p class="">{{ order.user?.name }}</p>
											<a :href="'tel:' + order.user?.phone" class="text-sm text-primary hover:underline font-mono">
												{{ order.user?.phone }}
											</a>
										</div>
									</TableCell>
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
												class="w-5 h-5 rounded border border-border shadow-sm" 
												:style="{ backgroundColor: getRalColor(order.ral_code)?.hex }"
											></div>
											<span class="text-sm font-mono">{{ order.ral_code }}</span>
										</div>
										<span v-else class="text-muted-foreground text-sm">—</span>
									</TableCell>
									<TableCell class="font-bold text-primary">
										{{ currencyFormatter(order.total_price) }}</TableCell>
									<TableCell>
										<StatusBadge :status="order.status" />
									</TableCell>
								</TableRow>
							</TableBody>
						</Table>
					</div>
				</div>

				<!-- Pagination -->
				<div v-if="pagination.length > 3" class="mt-6 md:mt-8 flex justify-center">
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
