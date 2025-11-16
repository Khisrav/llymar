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
	LandmarkIcon
} from "lucide-vue-next";
import { Company } from "../../../lib/types";
import { DropdownMenu, DropdownMenuTrigger, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator } from "../../../Components/ui/dropdown-menu";
import { toast } from "vue-sonner";
import { Toaster } from "../../../Components/ui/sonner";
import { Card, CardContent, CardHeader, CardTitle } from "../../../Components/ui/card";
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from "../../../Components/ui/select";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "../../../Components/ui/table";
import { vMaska } from 'maska/vue';

// Get initial data from page props
const pageData = usePage().props;
const { company }: { company: Company } = pageData as any;

// Edit state
const isEditing = ref(false);

const bossTitle = {
	'director': 'Директор',
	'ceo': 'Генеральный директор',
	'chief': 'Начальник',
	'supervisor': 'Руководитель',
};

// Computed properties
const hasBills = computed(() => company.company_bills && company.company_bills.length > 0);

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
							<h1 class="text-2xl font-bold tracking-tight">{{ company.short_name }}</h1>
							<p class="text-sm text-muted-foreground mt-1">{{ company.full_name }}</p>
						</div>
					</div>
					
					<div class="flex items-center gap-3">
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
								<div v-if="!isEditing" class="text-muted-foreground">{{ company.short_name }}</div>
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
									Полное название
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
									<a :href="'tel:' + company.phone" class="text-primary hover:underline font-mono">
										{{ company.phone }}
									</a>
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
								<Label class="text-sm font-medium">ИНН</Label>
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
										</TableRow>
									</TableHeader>
									
									<TableBody>
										<TableRow v-for="bill in company.company_bills" :key="bill.id" class="hover:bg-muted/30">
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
</template>

