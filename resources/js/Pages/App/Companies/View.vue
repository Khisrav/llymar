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
	EditIcon,
	SaveIcon,
	XIcon,
	BuildingIcon,
	PhoneIcon,
	MailIcon,
	MapPinIcon,
	UserIcon,
	PercentIcon,
	GlobeIcon,
	TrashIcon,
	ReceiptTextIcon,
	WalletIcon,
	LandmarkIcon,
	PlusIcon
} from "lucide-vue-next";
import { Company } from "../../../lib/types";
import { DropdownMenu, DropdownMenuTrigger, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator } from "../../../Components/ui/dropdown-menu";
import { toast } from "vue-sonner";
import { Toaster } from "../../../Components/ui/sonner";
import { Card, CardContent, CardHeader, CardTitle } from "../../../Components/ui/card";
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from "../../../Components/ui/select";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "../../../Components/ui/table";
import { vMaska } from 'maska/vue';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from "../../../Components/ui/dialog";
import { Switch } from "../../../Components/ui/switch";
import { router } from "@inertiajs/vue3";

// Get initial data from page props
const pageData = usePage().props;
const { company }: { company: Company } = pageData as any;

// Edit state
const isEditing = ref(false);

// Bill management state
const isAddingBill = ref(false);
const editingBill = ref<number | null>(null);

const bossTitle = {
	'director': 'Директор',
	'ceo': 'Генеральный директор',
	'chief': 'Начальник',
	'supervisor': 'Руководитель',
};

// Computed properties
const hasBills = computed(() => company.company_bills && company.company_bills.length > 0);

// Check if user can manage bills (Super-Admin or Operator only)
const canManageBills = computed(() => {
	return (pageData as any).auth?.user?.roles?.some((role: any) => role.name === 'Super-Admin' || role.name === 'Operator') || false;
});

// Form for editing company details
const form = useForm({
	short_name: company.short_name || '',
	full_name: company.full_name || '',
	boss_name: company.boss_name || '',
	boss_title: company.boss_title || 'director' as 'director' | 'ceo' | 'chief' | 'supervisor',
	legal_address: company.legal_address || '',
	email: company.email || '',
	phone: company.phone || '',
	phone_2: company.phone_2 || '',
	phone_3: company.phone_3 || '',
	website: company.website || '',
	inn: company.inn || 0,
	kpp: company.kpp || 0,
	ogrn: company.ogrn || 0,
	vat: company.vat || 0,
	contact_person: company.contact_person || '',
});

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
	form.put(`/app/companies/${company.id}`, {
		preserveScroll: true,
		onSuccess: (page) => {
			isEditing.value = false;
			// Update the local company object with the new data
			Object.assign(company, page.props.company);
			toast.success("Компания успешно обновлена");
		},
		onError: () => {
			toast.error("Ошибка при обновлении компании");
		},
	});
};

const deleteCompany = () => {
	if (!confirm(`Вы уверены, что хотите удалить "${company.short_name}"?`)) {
		return;
	}

	form.delete(`/app/companies/${company.id}`, {
		onSuccess: () => {
			toast.success("Компания успешно удалена");
			window.location.href = '/app/companies';
		},
		onError: () => {
			toast.error("Ошибка при удалении компании");
		},
	});
};

// Bill form for creating/editing
const billForm = useForm({
	current_account: '',
	correspondent_account: '',
	bank_name: '',
	bank_address: '',
	bik: '',
});

const startAddingBill = () => {
	isAddingBill.value = true;
	editingBill.value = null;
	billForm.reset();
	billForm.clearErrors();
};

const startEditingBill = (bill: any) => {
	editingBill.value = bill.id;
	billForm.current_account = bill.current_account;
	billForm.correspondent_account = bill.correspondent_account;
	billForm.bank_name = bill.bank_name;
	billForm.bank_address = bill.bank_address;
	billForm.bik = bill.bik;
	billForm.clearErrors();
};

const cancelBillForm = () => {
	isAddingBill.value = false;
	editingBill.value = null;
	billForm.reset();
	billForm.clearErrors();
};

const saveBill = () => {
	if (editingBill.value) {
		// Update existing bill
		billForm.put(`/app/companies/${company.id}/bills/${editingBill.value}`, {
			preserveScroll: true,
			onSuccess: (page) => {
				// Update local company bills
				Object.assign(company, page.props.company);
				cancelBillForm();
				toast.success("Банковский счет успешно обновлен");
			},
			onError: () => {
				toast.error("Ошибка при обновлении счета");
			},
		});
	} else {
		// Create new bill
		billForm.post(`/app/companies/${company.id}/bills`, {
			preserveScroll: true,
			onSuccess: (page) => {
				// Update local company bills
				Object.assign(company, page.props.company);
				cancelBillForm();
				toast.success("Банковский счет успешно добавлен");
			},
			onError: () => {
				toast.error("Ошибка при добавлении счета");
			},
		});
	}
};

const deleteBill = (billId: number) => {
	if (!confirm('Вы уверены, что хотите удалить этот счет?')) {
		return;
	}

	billForm.delete(`/app/companies/${company.id}/bills/${billId}`, {
		preserveScroll: true,
		onSuccess: (page) => {
			// Update local company bills
			Object.assign(company, page.props.company);
			toast.success("Банковский счет успешно удален");
		},
		onError: () => {
			toast.error("Ошибка при удалении счета");
		},
	});
};

const toggleMainCompany = () => {
	router.post(`/app/companies/${company.id}/toggle-main`, {}, {
		preserveScroll: true,
		onSuccess: (page: any) => {
			// Update the local company object
			Object.assign(company, page.props.company);
			toast.success("Основная компания успешно установлена");
		},
		onError: () => {
			toast.error("Ошибка при установке основной компании");
		},
	});
};
</script>

<template>
	<Head :title="`${company.short_name}`" />
	<AuthenticatedHeaderLayout />
	
	<Toaster />

	<div class="container mx-auto p-0 md:p-6 lg:p-8">
		<div class="bg-background md:border md:rounded-2xl md:shadow-sm overflow-hidden">
			<!-- Header Section -->
			<div class="p-4 md:p-8">
				<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
					<div class="flex items-center gap-3">
						<Button variant="outline" size="icon" @click="$inertia.visit('/app/companies')">
							<ArrowLeft class="h-4 w-4" />
						</Button>
						<div class="p-2 bg-primary/10 rounded-lg">
							<BuildingIcon class="h-6 w-6 text-primary" />
						</div>
						<div>
							<h1 class="text-2xl font-bold tracking-tight">{{ company.short_name || company.full_name }}</h1>
							<p v-if="company.short_name && company.short_name !== company.full_name" class="text-sm text-muted-foreground mt-1">{{ company.full_name }}</p>
						</div>
					</div>
					
					<div class="flex items-center gap-3">
						<!-- Main Company Toggle -->
						<div class="flex items-center gap-2 px-3 py-2 rounded-lg border bg-card">
							<Label for="main-toggle" class="text-sm cursor-pointer">
								Основная
							</Label>
							<Switch 
								id="main-toggle"
								:checked="company.is_main" 
								@update:checked="toggleMainCompany"
							/>
						</div>

						<!-- Action Dropdown -->
						<DropdownMenu>
							<DropdownMenuTrigger>
								<Button variant="outline" size="icon">
									<EllipsisVerticalIcon class="h-4 w-4" />
								</Button>
							</DropdownMenuTrigger>
							<DropdownMenuContent align="end">
								<DropdownMenuLabel>Действия</DropdownMenuLabel>
								<!-- <DropdownMenuSeparator /> -->
								
								<!-- <DropdownMenuItem disabled>
									<ReceiptTextIcon class="h-4 w-4" />
									<span>Счета</span>
								</DropdownMenuItem> -->
								
								<DropdownMenuSeparator />
								
								<DropdownMenuItem 
									@click="deleteCompany"
									class="text-destructive focus:text-destructive hover:bg-destructive/10"
								>
									<TrashIcon class="h-4 w-4" />
									<span>Удалить</span>
								</DropdownMenuItem>
							</DropdownMenuContent>
						</DropdownMenu>
					</div>
				</div>

				<!-- Company Details Grid -->
				<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
					
					<!-- Company Information -->
					<Card class="lg:col-span-2">
						<CardHeader class="p-4">
							<div class="flex items-center justify-between">
								<CardTitle class="flex items-center gap-2">
									<BuildingIcon class="h-5 w-5" />
									Информация о компании
								</CardTitle>
								<Button 
									v-if="!isEditing" 
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
							<!-- Short Name -->
							<div class="space-y-2">
								<Label class="text-sm font-medium flex items-center gap-2">
									<BuildingIcon class="h-4 w-4" />
									Короткое название
								</Label>
								<div v-if="!isEditing" class="text-muted-foreground">{{ company.short_name || '—' }}</div>
								<Input 
									v-else 
									v-model="form.short_name" 
									placeholder="ООО «Компания»"
									:class="{ 'border-red-500': form.errors.short_name }"
								/>
								<div v-if="form.errors.short_name" class="text-red-500 text-sm">
									{{ form.errors.short_name }}
								</div>
							</div>

							<!-- Full Name -->
							<div class="space-y-2">
								<Label class="text-sm font-medium flex items-center gap-2">
									<BuildingIcon class="h-4 w-4" />
									Полное название <span class="text-destructive">*</span>
								</Label>
								<div v-if="!isEditing" class="text-muted-foreground">{{ company.full_name }}</div>
								<Input 
									v-else 
									v-model="form.full_name" 
									placeholder="Общество с ограниченной ответственностью «Компания»"
									:class="{ 'border-red-500': form.errors.full_name }"
								/>
								<div v-if="form.errors.full_name" class="text-red-500 text-sm">
									{{ form.errors.full_name }}
								</div>
							</div>

							<!-- Boss Name -->
							<div class="space-y-2">
								<Label class="text-sm font-medium flex items-center gap-2">
									<UserIcon class="h-4 w-4" />
									Ф.И.О. руководителя
								</Label>
								<div v-if="!isEditing" class="text-muted-foreground">{{ company.boss_name || '—' }}</div>
								<Input 
									v-else 
									v-model="form.boss_name" 
									placeholder="Иванов Иван Иванович"
									:class="{ 'border-red-500': form.errors.boss_name }"
								/>
								<div v-if="form.errors.boss_name" class="text-red-500 text-sm">
									{{ form.errors.boss_name }}
								</div>
							</div>

							<!-- Boss Title -->
							<div class="space-y-2">
								<Label class="text-sm font-medium flex items-center gap-2">
									<UserIcon class="h-4 w-4" />
									Должность руководителя
								</Label>
								<div v-if="!isEditing" class="text-muted-foreground">
									{{ company.boss_title ? bossTitle[company.boss_title] : '—' }}
								</div>
								<Select v-else v-model="form.boss_title">
									<SelectTrigger>
										<SelectValue />
									</SelectTrigger>
									<SelectContent>
										<SelectGroup>
											<SelectItem value="director">Директор</SelectItem>
											<SelectItem value="ceo">Генеральный директор</SelectItem>
											<SelectItem value="chief">Начальник</SelectItem>
											<SelectItem value="supervisor">Руководитель</SelectItem>
										</SelectGroup>
									</SelectContent>
								</Select>
							</div>

							<!-- Phone -->
							<div class="space-y-2">
								<Label class="text-sm font-medium flex items-center gap-2">
									<PhoneIcon class="h-4 w-4" />
									Телефон
								</Label>
								<div v-if="!isEditing">
									<a v-if="company.phone" :href="'tel:' + company.phone" class="text-primary hover:underline font-mono">
										{{ company.phone }}
									</a>
									<span v-else class="text-muted-foreground">—</span>
								</div>
								<Input 
									v-else 
									v-model="form.phone" 
									v-maska="'+7 (###) ### ##-##'"
									placeholder="+7 (___) ___-__-__"
									:class="{ 'border-red-500': form.errors.phone }"
								/>
								<div v-if="form.errors.phone" class="text-red-500 text-sm">
									{{ form.errors.phone }}
								</div>
							</div>

							<!-- Phone 2 -->
							<div class="space-y-2">
								<Label class="text-sm font-medium flex items-center gap-2">
									<PhoneIcon class="h-4 w-4" />
									Телефон 2
								</Label>
								<div v-if="!isEditing">
									<a v-if="company.phone_2" :href="'tel:' + company.phone_2" class="text-primary hover:underline font-mono">
										{{ company.phone_2 }}
									</a>
									<span v-else class="text-muted-foreground">—</span>
								</div>
								<Input 
									v-else 
									v-model="form.phone_2" 
									v-maska="'+7 (###) ### ##-##'"
									placeholder="+7 (___) ___-__-__"
									:class="{ 'border-red-500': form.errors.phone_2 }"
								/>
								<div v-if="form.errors.phone_2" class="text-red-500 text-sm">
									{{ form.errors.phone_2 }}
								</div>
							</div>

							<!-- Email -->
							<div class="space-y-2">
								<Label class="text-sm font-medium flex items-center gap-2">
									<MailIcon class="h-4 w-4" />
									Email
								</Label>
								<div v-if="!isEditing">
									<a v-if="company.email" :href="'mailto:' + company.email" class="text-primary hover:underline">
										{{ company.email }}
									</a>
									<span v-else class="text-muted-foreground">—</span>
								</div>
								<Input 
									v-else 
									v-model="form.email" 
									type="email"
									placeholder="email@example.com"
									:class="{ 'border-red-500': form.errors.email }"
								/>
								<div v-if="form.errors.email" class="text-red-500 text-sm">
									{{ form.errors.email }}
								</div>
							</div>

							<!-- Website -->
							<div class="space-y-2">
								<Label class="text-sm font-medium flex items-center gap-2">
									<GlobeIcon class="h-4 w-4" />
									Сайт
								</Label>
								<div v-if="!isEditing">
									<a v-if="company.website" :href="company.website" target="_blank" class="text-primary hover:underline break-all">
										{{ company.website }}
									</a>
									<span v-else class="text-muted-foreground">—</span>
								</div>
								<Input 
									v-else 
									v-model="form.website" 
									type="url"
									placeholder="https://company.com"
									:class="{ 'border-red-500': form.errors.website }"
								/>
								<div v-if="form.errors.website" class="text-red-500 text-sm">
									{{ form.errors.website }}
								</div>
							</div>

							<!-- Legal Address -->
							<div class="space-y-2 md:col-span-2">
								<Label class="text-sm font-medium flex items-center gap-2">
									<MapPinIcon class="h-4 w-4" />
									Юридический адрес
								</Label>
								<div v-if="!isEditing" class="text-muted-foreground">{{ company.legal_address || '—' }}</div>
								<Textarea 
									v-else 
									v-model="form.legal_address" 
									placeholder="г. Москва, ул. Примерная, д. 1"
									:rows="2"
									:class="{ 'border-red-500': form.errors.legal_address }"
								/>
								<div v-if="form.errors.legal_address" class="text-red-500 text-sm">
									{{ form.errors.legal_address }}
								</div>
							</div>

							<!-- Contact Person -->
							<div class="space-y-2 md:col-span-2">
								<Label class="text-sm font-medium flex items-center gap-2">
									<UserIcon class="h-4 w-4" />
									Контактное лицо
								</Label>
								<div v-if="!isEditing" class="text-muted-foreground">{{ company.contact_person || '—' }}</div>
								<Input 
									v-else 
									v-model="form.contact_person" 
									placeholder="Петров Петр Петрович"
									:class="{ 'border-red-500': form.errors.contact_person }"
								/>
								<div v-if="form.errors.contact_person" class="text-red-500 text-sm">
									{{ form.errors.contact_person }}
								</div>
							</div>
						</CardContent>
					</Card>

					<!-- Company Details -->
					<Card>
						<CardHeader class="p-4">
							<CardTitle class="flex items-center gap-2">
								<ReceiptTextIcon class="h-5 w-5" />
								Реквизиты
							</CardTitle>
						</CardHeader>
						<CardContent class="space-y-4 p-4">
							<!-- INN -->
							<div class="space-y-2">
								<Label class="text-sm font-medium">ИНН <span class="text-destructive">*</span></Label>
								<div v-if="!isEditing" class="text-muted-foreground font-mono">{{ company.inn }}</div>
								<Input 
									v-else 
									v-model.number="form.inn" 
									type="number"
									placeholder="1234567890"
									:class="{ 'border-red-500': form.errors.inn }"
								/>
								<div v-if="form.errors.inn" class="text-red-500 text-sm">
									{{ form.errors.inn }}
								</div>
							</div>

							<!-- KPP -->
							<div class="space-y-2">
								<Label class="text-sm font-medium">КПП</Label>
								<div v-if="!isEditing" class="text-muted-foreground font-mono">{{ company.kpp || '—' }}</div>
								<Input 
									v-else 
									v-model.number="form.kpp" 
									type="number"
									placeholder="123456789"
									:class="{ 'border-red-500': form.errors.kpp }"
								/>
								<div v-if="form.errors.kpp" class="text-red-500 text-sm">
									{{ form.errors.kpp }}
								</div>
							</div>

							<!-- OGRN -->
							<div class="space-y-2">
								<Label class="text-sm font-medium">ОГРН</Label>
								<div v-if="!isEditing" class="text-muted-foreground font-mono">{{ company.ogrn || '—' }}</div>
								<Input 
									v-else 
									v-model.number="form.ogrn" 
									type="number"
									placeholder="1234567890123"
									:class="{ 'border-red-500': form.errors.ogrn }"
								/>
								<div v-if="form.errors.ogrn" class="text-red-500 text-sm">
									{{ form.errors.ogrn }}
								</div>
							</div>

							<!-- VAT -->
							<div class="space-y-2">
								<Label class="text-sm font-medium flex items-center gap-2">
									<PercentIcon class="h-4 w-4" />
									НДС (%)
								</Label>
								<div v-if="!isEditing" class="p-4 bg-primary/5 rounded-lg border">
									<div class="text-2xl font-bold text-primary">{{ company.vat }}%</div>
								</div>
								<Select v-else :model-value="String(form.vat)" @update:model-value="(value) => form.vat = Number(value)">
									<SelectTrigger>
										<SelectValue />
									</SelectTrigger>
									<SelectContent>
										<SelectGroup>
											<SelectItem value="0">Без НДС</SelectItem>
											<SelectItem value="5">5%</SelectItem>
											<SelectItem value="7">7%</SelectItem>
											<SelectItem value="10">10%</SelectItem>
											<SelectItem value="20">20%</SelectItem>
										</SelectGroup>
									</SelectContent>
								</Select>
							</div>
						</CardContent>
					</Card>
				</div>

				<!-- Company Bills Section -->
				<div class="mt-6 space-y-6">
					<div class="flex items-center justify-between">
						<h2 class="text-xl font-semibold tracking-tight flex items-center gap-2">
							<WalletIcon class="h-5 w-5" />
							Банковские счета
							<span v-if="hasBills" class="text-sm text-muted-foreground">({{ company.company_bills?.length }})</span>
						</h2>
						<Button v-if="canManageBills" @click="startAddingBill" size="sm">
							<PlusIcon class="h-4 w-4" />
							Добавить счет
						</Button>
					</div>

					<!-- Empty State -->
					<Card v-if="!hasBills" class="text-center py-12">
						<CardContent class="flex flex-col items-center justify-center">
							<div class="mx-auto w-20 h-20 bg-muted/50 rounded-full flex items-center justify-center mb-4">
								<LandmarkIcon class="h-10 w-10 text-muted-foreground" />
							</div>
							<h3 class="text-xl font-semibold mb-2">Банковских счетов пока нет</h3>
							<p class="text-muted-foreground mb-6 max-w-sm">
								Добавьте банковский счет для этой компании
							</p>
						</CardContent>
					</Card>

					<!-- Bills Content -->
					<div v-else>
						<!-- Mobile Cards View -->
						<div class="md:hidden space-y-4">
							<Card
								v-for="bill in company.company_bills"
								:key="bill.id"
								class="transition-all duration-200 hover:border-border/60 shadow-sm"
							>
								<CardHeader class="pb-3">
									<div class="flex items-start justify-between gap-2">
										<div class="flex-1 min-w-0">
											<CardTitle class="text-lg mb-1 flex items-center gap-2">
												<LandmarkIcon class="h-5 w-5" />
												{{ bill.bank_name }}
											</CardTitle>
											<p class="text-sm text-muted-foreground">{{ bill.bank_address }}</p>
										</div>
										<DropdownMenu v-if="canManageBills">
											<DropdownMenuTrigger as-child>
												<Button variant="ghost" size="icon">
													<EllipsisVerticalIcon class="h-4 w-4" />
												</Button>
											</DropdownMenuTrigger>
											<DropdownMenuContent align="end">
												<DropdownMenuItem @click="startEditingBill(bill)">
													<EditIcon class="h-4 w-4" />
													<span>Редактировать</span>
												</DropdownMenuItem>
												<DropdownMenuSeparator />
												<DropdownMenuItem 
													@click="deleteBill(bill.id)"
													class="text-destructive focus:text-destructive hover:bg-destructive/10"
												>
													<TrashIcon class="h-4 w-4" />
													<span>Удалить</span>
												</DropdownMenuItem>
											</DropdownMenuContent>
										</DropdownMenu>
									</div>
								</CardHeader>
								<CardContent class="space-y-3">
									<div class="space-y-2">
										<div class="flex items-start justify-between text-sm">
											<span class="text-muted-foreground">Расчетный счет:</span>
											<span class="font-mono text-right">{{ bill.current_account }}</span>
										</div>
										
										<div class="flex items-start justify-between text-sm">
											<span class="text-muted-foreground">Корр. счет:</span>
											<span class="font-mono text-right">{{ bill.correspondent_account }}</span>
										</div>
										
										<div class="flex items-start justify-between text-sm">
											<span class="text-muted-foreground">БИК:</span>
											<span class="font-mono text-right">{{ bill.bik }}</span>
										</div>
									</div>
								</CardContent>
							</Card>
						</div>

						<!-- Desktop Table View -->
						<div class="hidden md:block">
							<Card>
								<Table>
									<TableHeader>
										<TableRow class="bg-muted/30 hover:bg-muted/30">
											<TableHead class="font-semibold">Банк</TableHead>
											<TableHead class="font-semibold">Расчетный счет</TableHead>
											<TableHead class="font-semibold">Корр. счет</TableHead>
											<TableHead class="font-semibold">БИК</TableHead>
											<TableHead v-if="canManageBills" class="font-semibold w-[100px]">Действия</TableHead>
										</TableRow>
									</TableHeader>
									
									<TableBody>
										<TableRow 
											v-for="bill in company.company_bills" 
											:key="bill.id" 
											class="hover:bg-muted/30"
										>
											<TableCell class="max-w-xs">
												<div class="font-medium">{{ bill.bank_name }}</div>
												<div class="text-sm text-muted-foreground line-clamp-1">{{ bill.bank_address }}</div>
											</TableCell>
											<TableCell>
												<div class="font-mono text-sm">{{ bill.current_account }}</div>
											</TableCell>
											<TableCell>
												<div class="font-mono text-sm">{{ bill.correspondent_account }}</div>
											</TableCell>
											<TableCell>
												<div class="font-mono text-sm">{{ bill.bik }}</div>
											</TableCell>
											<TableCell v-if="canManageBills">
												<div class="flex items-center gap-1">
													<Button 
														variant="ghost" 
														size="icon"
														@click="startEditingBill(bill)"
													>
														<EditIcon class="h-4 w-4" />
													</Button>
													<Button 
														variant="ghost" 
														size="icon"
														@click="deleteBill(bill.id)"
														class="text-destructive hover:text-destructive hover:bg-destructive/10"
													>
														<TrashIcon class="h-4 w-4" />
													</Button>
												</div>
											</TableCell>
										</TableRow>
									</TableBody>
								</Table>
							</Card>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Add/Edit Bill Dialog -->
	<Dialog :open="isAddingBill || editingBill !== null" @update:open="(open) => !open && cancelBillForm()">
		<DialogContent class="max-w-2xl max-h-[90vh] overflow-y-auto">
			<DialogHeader>
				<DialogTitle>
					{{ editingBill ? 'Редактировать банковский счет' : 'Добавить банковский счет' }}
				</DialogTitle>
				<DialogDescription>
					{{ editingBill ? 'Обновите информацию о банковском счете' : 'Введите информацию о новом банковском счете' }}
				</DialogDescription>
			</DialogHeader>

			<div class="grid gap-4 py-4">
				<!-- Bank Name -->
				<div class="space-y-2">
					<Label for="bank_name" class="flex items-center gap-2">
						<LandmarkIcon class="h-4 w-4" />
						Название банка
					</Label>
					<Input 
						id="bank_name"
						v-model="billForm.bank_name" 
						placeholder="ПАО «Сбербанк»"
						:class="{ 'border-red-500': billForm.errors.bank_name }"
					/>
					<div v-if="billForm.errors.bank_name" class="text-red-500 text-sm">
						{{ billForm.errors.bank_name }}
					</div>
				</div>

				<!-- Bank Address -->
				<div class="space-y-2">
					<Label for="bank_address" class="flex items-center gap-2">
						<MapPinIcon class="h-4 w-4" />
						Адрес банка
					</Label>
					<Textarea 
						id="bank_address"
						v-model="billForm.bank_address" 
						placeholder="г. Москва, ул. Вавилова, д. 19"
						:rows="2"
						:class="{ 'border-red-500': billForm.errors.bank_address }"
					/>
					<div v-if="billForm.errors.bank_address" class="text-red-500 text-sm">
						{{ billForm.errors.bank_address }}
					</div>
				</div>

				<!-- Current Account -->
				<div class="space-y-2">
					<Label for="current_account">Расчетный счет</Label>
					<Input 
						id="current_account"
						v-model="billForm.current_account" 
						placeholder="40702810123456789012"
						class="font-mono"
						:class="{ 'border-red-500': billForm.errors.current_account }"
					/>
					<div v-if="billForm.errors.current_account" class="text-red-500 text-sm">
						{{ billForm.errors.current_account }}
					</div>
				</div>

				<!-- Correspondent Account -->
				<div class="space-y-2">
					<Label for="correspondent_account">Корреспондентский счет</Label>
					<Input 
						id="correspondent_account"
						v-model="billForm.correspondent_account" 
						placeholder="30101810400000000225"
						class="font-mono"
						:class="{ 'border-red-500': billForm.errors.correspondent_account }"
					/>
					<div v-if="billForm.errors.correspondent_account" class="text-red-500 text-sm">
						{{ billForm.errors.correspondent_account }}
					</div>
				</div>

				<!-- BIK -->
				<div class="space-y-2">
					<Label for="bik">БИК</Label>
					<Input 
						id="bik"
						v-model="billForm.bik" 
						placeholder="044525225"
						class="font-mono"
						:class="{ 'border-red-500': billForm.errors.bik }"
					/>
					<div v-if="billForm.errors.bik" class="text-red-500 text-sm">
						{{ billForm.errors.bik }}
					</div>
				</div>
			</div>

			<DialogFooter>
				<Button variant="outline" @click="cancelBillForm" :disabled="billForm.processing">
					Отмена
				</Button>
				<Button @click="saveBill" :disabled="billForm.processing">
					<SaveIcon class="h-4 w-4" />
					{{ editingBill ? 'Сохранить изменения' : 'Добавить счет' }}
				</Button>
			</DialogFooter>
		</DialogContent>
	</Dialog>
</template>

