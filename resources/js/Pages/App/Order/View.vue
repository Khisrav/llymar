<script setup lang="ts">
import { Head, usePage, useForm } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import AuthenticatedHeaderLayout from "../../../Layouts/AuthenticatedHeaderLayout.vue";
import { Button } from "../../../Components/ui/button";
import { Input } from "../../../Components/ui/input";
import { Textarea } from "../../../Components/ui/textarea";
import { Label } from "../../../Components/ui/label";
import { 
	ArrowLeft, 
	EllipsisVerticalIcon, 
	FileTextIcon, 
	FileType2Icon, 
	FileAxis3DIcon, 
	ReceiptRussianRubleIcon, 
	ScrollTextIcon,
	EditIcon,
	SaveIcon,
	XIcon,
	PackageIcon,
	CalendarIcon,
	UserIcon,
	PhoneIcon,
	MapPinIcon,
	MessageSquareIcon,
	PaletteIcon,
	MailIcon,
	ChevronDownIcon,
	ChevronUpIcon,
	TruckIcon,
	TrashIcon
} from "lucide-vue-next";
import { OpeningImages, Order, User } from "../../../lib/types";
import { currencyFormatter } from "../../../Utils/currencyFormatter";
import StatusBadge from "../../../Components/StatusBadge.vue";
import { DropdownMenu, DropdownMenuTrigger, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator } from "../../../Components/ui/dropdown-menu";
import { toast } from "vue-sonner";
import { Toaster } from "../../../Components/ui/sonner";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "../../../Components/ui/card";

// Import RAL colors
// @ts-ignore - RAL library doesn't have proper types
import { RAL } from 'ral-colors/index.js';

// Get initial data from page props
const pageData = usePage().props;
const { user_role, can_access_sketcher, order, order_user }: { user_role: string; can_access_sketcher: boolean; order: Order; order_user: User } = pageData as any;
// Edit state
const isEditing = ref(false);

const opening_images: OpeningImages = {
    left: '/assets/openings/openings-left.jpg',
    right: '/assets/openings/openings-right.jpg',
    center: '/assets/openings/openings-center.jpg',
    'inner-left': '/assets/openings/openings-inner-left.jpg',
    'inner-right': '/assets/openings/openings-inner-right.jpg',
    'blind-glazing': '/assets/openings/openings-blind-glazing.jpg',
    triangle: '/assets/openings/openings-triangle.jpg',
}

const openingNames = {
    'inner-left': 'Входная группа левая',
    'inner-right': 'Входная группа правая',
    'blind-glazing': 'Глухое остекление',
    'triangle': 'Треугольник',
    'left': 'Левый проем',
    'right': 'Правый проем',
    'center': 'Центральный проем',
}

// Form for editing order details
const form = useForm({
	customer_name: order.customer_name || '',
	customer_email: order.customer_email || '',
	customer_phone: order.customer_phone || '',
	customer_address: order.customer_address || '',
	city: order.city || '',
	comment: order.comment || '',
});

// Collapsible sections state
const showOpenings = ref(false);
const showItems = ref(false);

// Computed properties
const canEdit = computed(() => order.status === 'created');
const canAccess = computed(() => order.user_id === pageData.auth?.user?.id || pageData.auth?.user?.roles?.some((role: any) => role.name === 'Super-Admin'));

const canDeleteOrder = ref(order.status == 'created')
const canDownloadSketchPDF_DXF = ref(order.status != 'created')
const canDownloadListPDF = ref(true) //i.e. always
const canDownloadBill = ref(order.status == 'created')

const formatDate = (dateString: string | null) => {
	if (!dateString) return '—';
	return new Date(dateString).toLocaleDateString("ru-RU", {
		year: "numeric",
		month: "short",
		day: "numeric",
		hour: "2-digit",
		minute: "2-digit",
	});
};

const getRalColor = (ralCode: string | null) => {
	if (!ralCode) return null;
	try {
		const color = RAL.classic[ralCode];
		return color ? { name: ralCode, hex: color.HEX } : null;
	} catch {
		return null;
	}
};

const startEditing = () => {
	isEditing.value = true;
	form.clearErrors();
};

const cancelEditing = () => {
	isEditing.value = false;
	form.reset();
	form.clearErrors();
};

const saveChanges = () => {
	form.put(`/app/orders/${order.id}`, {
		preserveScroll: true,
		onSuccess: (page) => {
			isEditing.value = false;
			// Update the local order object with the new data
			Object.assign(order, page.props.order);
			toast.success("Заказ успешно обновлен");
		},
		onError: () => {
			toast.error("Ошибка при обновлении заказа");
		},
	});
};

// Action methods (placeholders for future implementation)
const downloadBill = () => {
	window.open(`/orders/${order.id}/download-bill`, '_blank');
};

const downloadSketchPDF = () => {
	// Check if user has access to sketcher
	if (!can_access_sketcher) {
		toast.error("У вас нет доступа к чертежам");
		return;
	}
	
	try {
		toast.info("Подготовка PDF чертежа...");
		
		// Create a temporary link to handle the download
		const link = document.createElement('a');
		link.href = `/app/orders/${order.id}/sketch-pdf`;
		link.target = '_blank';
		link.style.display = 'none';
		document.body.appendChild(link);
		link.click();
		document.body.removeChild(link);
		
		// Success message will be shown after a short delay
		setTimeout(() => {
			toast.success("PDF чертеж готов к загрузке");
		}, 1000);
	} catch (error) {
		toast.error("Ошибка при загрузке PDF чертежа");
		console.error('Error downloading sketch PDF:', error);
	}
};

const downloadSketchDXF = () => {
	// Check if user has access to sketcher
	if (!can_access_sketcher) {
		toast.error("У вас нет доступа к чертежам");
		return;
	}
	
	try {
		toast.info("Подготовка DXF чертежа...");
		
		// Create a temporary link to handle the download
		const link = document.createElement('a');
		link.href = `/app/orders/${order.id}/sketch-dxf`;
		link.target = '_blank';
		link.style.display = 'none';
		document.body.appendChild(link);
		link.click();
		document.body.removeChild(link);
		
		// Success message will be shown after a short delay
		setTimeout(() => {
			toast.success("DXF чертеж готов к загрузке");
		}, 1000);
	} catch (error) {
		toast.error("Ошибка при загрузке DXF чертежа");
		console.error('Error downloading sketch DXF:', error);
	}
};

const downloadSpecification = () => {
	window.open(`/orders/${order.id}/list-pdf`, '_blank');
};

const openSketcher = () => {
	window.location.href = `/app/orders/sketcher/${order.id}`;
};

const deleteOrder = () => {
	if (!confirm('Вы уверены, что хотите удалить этот заказ?')) return;
	
	form.delete(`/app/order/${order.id}/delete`, {
		onSuccess: () => {
			toast.success("Заказ успешно удален");
			window.location.href = '/app/history';
		},
		onError: () => {
			toast.error("Ошибка при удалении заказа");
		},
	});
};

// Phone number formatting
const formatPhoneNumber = (value: string) => {
	// Remove all non-digit characters
	const digits = value.replace(/\D/g, '');
	
	// Apply Russian phone number format: +7 (XXX) XXX-XX-XX
	if (digits.length === 0) return '';
	if (digits.length <= 1) return '+7';
	if (digits.length <= 4) return `+7 (${digits.slice(1)}`;
	if (digits.length <= 7) return `+7 (${digits.slice(1, 4)}) ${digits.slice(4)}`;
	if (digits.length <= 9) return `+7 (${digits.slice(1, 4)}) ${digits.slice(4, 7)}-${digits.slice(7)}`;
	return `+7 (${digits.slice(1, 4)}) ${digits.slice(4, 7)}-${digits.slice(7, 9)}-${digits.slice(9, 11)}`;
};

const handlePhoneInput = (event: Event) => {
	const target = event.target as HTMLInputElement;
	const formatted = formatPhoneNumber(target.value);
	form.customer_phone = formatted;
	target.value = formatted;
};

const handleImageError = (event: Event) => {
	const img = event.target as HTMLImageElement;
	img.style.display = 'none';
};
</script>

<template>
	<Head :title="`Заказ №${order.order_number || order.id}`" />
	<AuthenticatedHeaderLayout />
	
	<Toaster />

	<div class="container mx-auto p-0 md:p-6 lg:p-8">
		<div class="bg-background md:border md:rounded-2xl md:shadow-sm overflow-hidden">
			<!-- Header Section -->
			<div class="p-4 md:p-8">
				<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
					<div class="flex items-center gap-3">
						<Button variant="outline" size="icon" @click="$inertia.visit('/app/history')">
							<ArrowLeft class="h-4 w-4" />
						</Button>
						<div class="p-2 bg-primary/10 rounded-lg">
							<PackageIcon class="h-6 w-6 text-primary" />
						</div>
						<div>
							<h1 class="text-2xl font-bold tracking-tight">Заказ №{{ order.order_number || order.id }}</h1>
							<p class="text-sm text-muted-foreground mt-1">
								Создан {{ formatDate(order.created_at || '') }}
							</p>
						</div>
					</div>
					
					<div class="flex items-center gap-3">
						<StatusBadge :status="order.status" />
						
						<!-- Action Dropdown -->
						<DropdownMenu>
							<DropdownMenuTrigger>
								<Button variant="outline" size="icon">
									<EllipsisVerticalIcon class="h-4 w-4" />
								</Button>
							</DropdownMenuTrigger>
							<DropdownMenuContent align="end">
								<DropdownMenuLabel>Действия</DropdownMenuLabel>
								<DropdownMenuSeparator />
								
								<DropdownMenuItem v-if="can_access_sketcher" @click="openSketcher">
									<EditIcon class="h-4 w-4" />
									<span>Чертеж</span>
								</DropdownMenuItem>
								
								<DropdownMenuItem v-if="canDownloadListPDF" @click="downloadSpecification">
									<ScrollTextIcon class="h-4 w-4" />
									<span>Спецификация</span>
								</DropdownMenuItem>
								
								<DropdownMenuItem v-if="canDownloadBill" @click="downloadBill">
									<ReceiptRussianRubleIcon class="h-4 w-4" />
									<span>Счет PDF</span>
								</DropdownMenuItem>
								
								<DropdownMenuItem v-if="canDownloadSketchPDF_DXF" @click="downloadSketchPDF">
									<FileType2Icon class="h-4 w-4" />
									<span>Чертеж PDF</span>
								</DropdownMenuItem>
								
								<DropdownMenuItem v-if="canDownloadSketchPDF_DXF" @click="downloadSketchDXF">
									<FileAxis3DIcon class="h-4 w-4" />
									<span>Чертеж DXF</span>
								</DropdownMenuItem>
								
								<DropdownMenuSeparator />
								
								<DropdownMenuItem 
									@click="deleteOrder" 
									:disabled="!canDeleteOrder"
									class="text-destructive focus:text-destructive hover:bg-destructive/10 disabled:opacity-50 disabled:cursor-not-allowed"
								>
									<TrashIcon class="h-4 w-4" />
									<span>Удалить заказ</span>
								</DropdownMenuItem>
							</DropdownMenuContent>
						</DropdownMenu>
					</div>
				</div>

				<!-- Order Details Grid -->
				<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
					
					<!-- Customer Information -->
					<Card class="lg:col-span-2">
						<CardHeader class="p-4">
							<div class="flex items-center justify-between">
								<CardTitle class="flex items-center gap-2">
									<UserIcon class="h-5 w-5" />
									О клиенте
								</CardTitle>
								<Button 
									v-if="canEdit && !isEditing" 
									variant="outline" 
									size="icon" 
									@click="startEditing"
								>
									<EditIcon class="h-4 w-4" />
								</Button>
								<div v-if="isEditing" class="flex gap-2">
									<Button variant="outline" size="sm" @click="cancelEditing">
										<XIcon class="h-4 w-4" />
									</Button>
									<Button size="sm" @click="saveChanges" :disabled="form.processing">
										<SaveIcon class="h-4 w-4" />
									</Button>
								</div>
							</div>
						</CardHeader>
						<CardContent class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4">
							<!-- Customer Name -->
							<div class="space-y-2">
								<Label class="text-sm font-medium flex items-center gap-2">
									<UserIcon class="h-4 w-4" />
									ФИО клиента
								</Label>
								<div v-if="!isEditing" class="text-muted-foreground">{{ order.customer_name }}</div>
								<Input 
									v-else 
									v-model="form.customer_name" 
									placeholder="ФИО клиента"
									:class="{ 'border-red-500': form.errors.customer_name }"
								/>
								<div v-if="form.errors.customer_name" class="text-red-500">
									{{ form.errors.customer_name }}
								</div>
							</div>

							<!-- Customer Email -->
							<div class="space-y-2">
								<Label class="text-sm font-medium flex items-center gap-2">
									<MailIcon class="h-4 w-4" />
									Email
								</Label>
								<div v-if="!isEditing">
									<a v-if="order.customer_email" :href="'mailto:' + order.customer_email" class="text-primary hover:underline">
										{{ order.customer_email }}
									</a>
									<span v-else class="text-muted-foreground">—</span>
								</div>
								<Input 
									v-else 
									v-model="form.customer_email" 
									type="email"
									placeholder="email@example.com"
									:class="{ 'border-red-500': form.errors.customer_email }"
								/>
								<div v-if="form.errors.customer_email" class="text-red-500">
									{{ form.errors.customer_email }}
								</div>
							</div>

							<!-- Phone Number -->
							<div class="space-y-2">
								<Label class="text-sm font-medium flex items-center gap-2">
									<PhoneIcon class="h-4 w-4" />
									Телефон
								</Label>
								<div v-if="!isEditing">
									<a :href="'tel:' + order.customer_phone" class="text-primary hover:underline font-mono">
										{{ order.customer_phone || '—' }}
									</a>
								</div>
								<Input 
									v-else 
									v-model="form.customer_phone" 
									type="tel" 
									placeholder="+7 (___) ___-__-__"
									maxlength="18"
									@input="handlePhoneInput"
									:class="{ 'border-red-500': form.errors.customer_phone }"
								/>
								<div v-if="form.errors.customer_phone" class="text-red-500">
									{{ form.errors.customer_phone }}
								</div>
							</div>

							<!-- Address -->
							<div class="space-y-2">
								<Label class="text-sm font-medium flex items-center gap-2">
									<MapPinIcon class="h-4 w-4" />
									Адрес
								</Label>
								<div v-if="!isEditing">{{ order.customer_address || '—' }}</div>
								<Textarea 
									v-else 
									v-model="form.customer_address" 
									placeholder="Адрес доставки"
									:rows="2"
									:class="{ 'border-red-500': form.errors.customer_address }"
								/>
								<div v-if="form.errors.customer_address" class="text-red-500">
									{{ form.errors.customer_address }}
								</div>
							</div>

							<!-- City -->
							<div class="space-y-2">
								<Label class="text-sm font-medium flex items-center gap-2">
									<MapPinIcon class="h-4 w-4" />
									Город
								</Label>
								<div v-if="!isEditing">{{ order.city || '—' }}</div>
								<Input 
									v-else 
									v-model="form.city" 
									placeholder="Город"
									:class="{ 'border-red-500': form.errors.city }"
								/>
								<div v-if="form.errors.city" class="text-red-500">
									{{ form.errors.city }}
								</div>
							</div>

							<!-- Comment -->
							<div class="space-y-2">
								<Label class="text-sm font-medium flex items-center gap-2">
									<MessageSquareIcon class="h-4 w-4" />
									Комментарий
								</Label>
								<div v-if="!isEditing">{{ order.comment || '—' }}</div>
								<Textarea 
									v-else 
									v-model="form.comment" 
									placeholder="Комментарий к заказу"
									:rows="3"
									:class="{ 'border-red-500': form.errors.comment }"
								/>
								<div v-if="form.errors.comment" class="text-red-500">
									{{ form.errors.comment }}
								</div>
							</div>
						</CardContent>
					</Card>

					<!-- Order Summary -->
					<Card>
						<CardHeader class="p-4">
							<CardTitle class="flex items-center gap-2">
								<PackageIcon class="h-5 w-5" />
								Детали заказа
							</CardTitle>
						</CardHeader>
						<CardContent class="space-y-4">
							<!-- Total Price -->
							<div class="p-4 bg-primary/5 rounded-lg border">
								<div class="text-sm text-muted-foreground">Общая сумма</div>
								<div class="text-2xl font-bold text-primary">{{ currencyFormatter(order.total_price) }}</div>
							</div>

							<!-- Order Details -->
							<div class="space-y-3">
								<div class="flex justify-between items-center">
									<span class="text-sm text-muted-foreground">Создан:</span>
									<span class="text-sm">{{ formatDate(order.created_at || '') }}</span>
								</div>
								
								<div class="flex justify-between items-center">
									<span class="text-sm text-muted-foreground">Принят:</span>
									<span class="text-sm">{{ formatDate(order.when_started_working_on_it || '') }}</span>
								</div>

								<div class="flex justify-between items-center">
									<span class="text-sm text-muted-foreground">Завершен:</span>
									<span class="text-sm">{{ formatDate(order.completed_at || '') }}</span>
								</div>

								<div class="flex justify-between items-center">
									<span class="text-sm text-muted-foreground">В работе:</span>
									<span class="text-sm">
										<template v-if="order.status === 'created'">—</template>
										<template v-else-if="order.status === 'completed' && order.completed_at && order.when_started_working_on_it">
											{{ Math.ceil((new Date(order.completed_at).getTime() - new Date(order.when_started_working_on_it).getTime()) / (1000 * 60 * 60 * 24)) }} дн.
										</template>
										<template v-else-if="order.when_started_working_on_it">
											{{ Math.ceil((Date.now() - new Date(order.when_started_working_on_it).getTime()) / (1000 * 60 * 60 * 24)) }} дн.
										</template>
										<template v-else>—</template>
									</span>
								</div>

								<div v-if="order.ral_code" class="flex justify-between items-center">
									<span class="text-sm text-muted-foreground">Цвет RAL:</span>
									<div class="flex items-center gap-2">
										<div 
											v-if="getRalColor(order.ral_code)"
											class="w-4 h-4 rounded border border-border shadow-sm" 
											:style="{ backgroundColor: getRalColor(order.ral_code)?.hex }"
										></div>
										<span class="text-sm font-mono">{{ order.ral_code }}</span>
									</div>
								</div>

								<div v-if="order.order_type" class="flex justify-between items-center">
									<span class="text-sm text-muted-foreground">Тип заказа:</span>
									<span class="text-sm">{{ order.order_type }}</span>
								</div>

								<div v-if="order.logistics_company" class="flex justify-between items-center">
									<span class="text-sm text-muted-foreground">Логистическая компания:</span>
									<span class="text-sm">{{ order.logistics_company }}</span>
								</div>

								<div v-if="order.user" class="flex flex-col gap-1.5 justify-between border p-2 rounded-lg">
									<span class="text-sm text-muted-foreground">Ответственный:</span>
									<div class="flex items-center gap-2">
										<UserIcon class="h-4 w-4" />
										<span class="text-sm">{{ order.user.name }}</span>
									</div>
									<div class="flex items-center gap-2">
										<PhoneIcon class="h-4 w-4" />
										<a :href="'tel:' + order.user.phone" class="text-sm text-primary hover:underline font-mono">
											{{ order.user.phone }}
										</a>
									</div>
								</div>
							</div>
						</CardContent>
					</Card>
				</div>

				<!-- Collapsible Sections -->
				<div class="space-y-6 mt-6">
					
					<!-- Openings Section -->
					<div v-if="order.order_openings && order.order_openings.length > 0" class="border p-2 md:p-4 rounded-2xl bg-background">
						<div class="flex items-center justify-between">
							<h2 class="text-xl font-bold text-muted-foreground flex items-center gap-2">
								<PackageIcon class="h-5 w-5" />
								Проемы ({{ order.order_openings.length }})
							</h2>
							<Button variant="outline" size="icon" @click="showOpenings = !showOpenings">
								<ChevronDownIcon v-if="!showOpenings" class="h-4 w-4" />
								<ChevronUpIcon v-else class="h-4 w-4" />
							</Button>
						</div>
						
						<div v-if="showOpenings" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-2 md:gap-4 mt-4">
							<div 
								v-for="opening in order.order_openings" 
								:key="opening.id" 
								class="bg-white dark:bg-slate-900 p-2 md:p-4 border rounded-xl hover:shadow-2xl hover:shadow-slate-100 dark:hover:shadow-slate-800 transition-all"
							>
								<div class="space-y-2">
									<div class="text-center space-y-1">
										<div class="text-sm md:text-base">{{ openingNames[opening.type] }}</div>
										<img :src="opening_images[opening.type]" class="w-full rounded-md" />
									</div>
									
									<div>
										<div class="text-center flex items-center justify-between gap-1">
											<label class="text-xs md:text-sm text-muted-foreground block">Размеры (мм):</label>
											<div class="text-sm">
												<span>{{ opening.width }}</span>
												<span>×</span>
												<span>{{ opening.height }}</span>
											</div>
										</div>
										
										<div class="text-center flex items-center justify-between gap-1">
											<label class="text-xs md:text-sm text-muted-foreground block">Створок:</label>
											<div class="text-sm">{{ opening.doors }} шт.</div>
										</div>
										
										<!-- <div v-if="opening.mp" class="text-center">
											<label class="text-xs md:text-sm text-muted-foreground block">MP:</label>
											<div class="text-sm font-semibold">{{ opening.mp }}мм</div>
										</div> -->
									</div>
									
									<div v-if="opening.a || opening.b || opening.c" class="border-t pt-2">
										<label class="text-xs text-muted-foreground block mb-1">Параметры:</label>
										<div class="grid grid-cols-3 gap-1 text-xs">
											<div v-if="opening.a" class="text-center">
												<span class="text-muted-foreground">A:</span> {{ opening.a }}мм
											</div>
											<div v-if="opening.b" class="text-center">
												<span class="text-muted-foreground">B:</span> {{ opening.b }}мм
											</div>
											<div v-if="opening.c" class="text-center">
												<span class="text-muted-foreground">C:</span> {{ opening.c }}мм
											</div>
											<div v-if="opening.d" class="text-center">
												<span class="text-muted-foreground">D:</span> {{ opening.d }}мм
											</div>
											<div v-if="opening.e" class="text-center">
												<span class="text-muted-foreground">E:</span> {{ opening.e }}мм
											</div>
											<div v-if="opening.f" class="text-center">
												<span class="text-muted-foreground">F:</span> {{ opening.f }}мм
											</div>
											<div v-if="opening.g" class="text-center">
												<span class="text-muted-foreground">G:</span> {{ opening.g }}мм
											</div>
											<div v-if="opening.i" class="text-center">
												<span class="text-muted-foreground">I:</span> {{ opening.i }}мм
											</div>
											<div v-if="opening.mp" class="text-center">
												<span class="text-muted-foreground">MP:</span> {{ opening.mp }}мм
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>

					<!-- Items Section -->
					<div v-if="order.order_items && order.order_items.length > 0" class="border p-2 md:p-4 rounded-2xl bg-background">
						<div class="flex items-center justify-between">
							<h2 class="text-xl font-bold text-muted-foreground flex items-center gap-2">
								<TruckIcon class="h-5 w-5" />
								Товары ({{ order.order_items.length }})
							</h2>
							<Button variant="outline" size="icon" @click="showItems = !showItems">
								<ChevronDownIcon v-if="!showItems" class="h-4 w-4" />
								<ChevronUpIcon v-else class="h-4 w-4" />
							</Button>
						</div>
						
						<div v-if="showItems" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 md:gap-4 mt-4">
							<div 
								v-for="orderItem in order.order_items" 
								:key="orderItem.id" 
								class="bg-white dark:bg-slate-900 p-2 md:p-4 border rounded-xl hover:shadow-2xl hover:shadow-slate-100 dark:hover:shadow-slate-800 transition-all"
							>
								<div class="space-y-3">
									<div class="flex items-start gap-3">
										<div v-if="orderItem.item?.img" class="shrink-0">
											<img 
												:src="`/storage/${orderItem.item.img}`" 
												:alt="orderItem.item?.name"
												class="w-12 h-12 rounded-lg object-cover border"
												@error="handleImageError"
											/>
										</div>
										<div v-if="orderItem.item?.img == '' || orderItem.item?.img == null" class="w-12 h-12 rounded-lg bg-gray-200 dark:bg-gray-700 border flex items-center justify-center">
											<PackageIcon class="w-6 h-6 text-gray-400" />
										</div>
										<div class="flex-1 min-w-0">
											<div class="font-medium text-sm leading-tight">
												{{ orderItem.item?.name || 'Товар не найден' }}
											</div>
											<div class="flex items-center gap-2 text-sm text-muted-foreground mt-1">
												<span>{{ orderItem.item?.vendor_code || '—' }}</span> <span class="text-muted-foreground" style="font-size: 6px;">●</span> <span>{{ orderItem.quantity }} {{ orderItem.item?.unit || 'шт' }}</span>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template> 