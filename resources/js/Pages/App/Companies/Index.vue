<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { vMaska } from 'maska/vue';
import AuthenticatedHeaderLayout from '../../../Layouts/AuthenticatedHeaderLayout.vue';
import SettingsLayout from '../../../Layouts/SettingsLayout.vue';
import {
	Table,
	TableBody,
	TableCell,
	TableHead,
	TableHeader,
	TableRow,
} from '../../../Components/ui/table';
import {
	Card,
	CardContent,
	CardDescription,
	CardHeader,
	CardTitle,
} from '../../../Components/ui/card';
import { Badge } from '../../../Components/ui/badge';
import { toast } from 'vue-sonner';
import { Toaster } from '../../../Components/ui/sonner';
import {
	Dialog,
	DialogContent,
	DialogDescription,
	DialogFooter,
	DialogHeader,
	DialogTitle,
} from '../../../Components/ui/dialog';
import { Label } from '../../../Components/ui/label/';
import { Input } from '../../../Components/ui/input/';
import { Button } from '../../../Components/ui/button/';
import {
	Select,
	SelectContent,
	SelectGroup,
	SelectItem,
	SelectTrigger,
	SelectValue,
} from '../../../Components/ui/select';
import {
	PlusIcon,
	TrashIcon,
	BuildingIcon,
	PhoneIcon,
	MailIcon,
	MapPinIcon,
	UserIcon,
	PercentIcon,
	GlobeIcon,
	EllipsisVerticalIcon,
	ReceiptTextIcon,
	EyeIcon
} from 'lucide-vue-next';
import { DropdownMenu, DropdownMenuTrigger, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator } from '../../../Components/ui/dropdown-menu';
import { Company } from '../../../lib/types';

const props = defineProps<{
	companies: Company[];
}>();

const showCreateDialog = ref(false);

const hasCompanies = computed(() => props.companies.length > 0);

const bossTitle = {
	'director': 'Директор',
	'ceo': 'Генеральный директор',
	'chief': 'Начальник',
	'supervisor': 'Руководитель',
};

const newCompany = ref({
	short_name: '',
	full_name: '',
	boss_name: '',
	boss_title: 'director' as 'director' | 'ceo' | 'chief' | 'supervisor',
	legal_address: '',
	email: '',
	phone: '',
	phone_2: '',
	phone_3: '',
	website: '',
	inn: 0,
	kpp: 0,
	ogrn: 0,
	vat: 0,
	contact_person: '',
});

const isProcessing = ref(false);

const resetNewCompanyForm = () => {
	newCompany.value = {
		short_name: '',
		full_name: '',
		boss_name: '',
		boss_title: 'director',
		legal_address: '',
		email: '',
		phone: '',
		phone_2: '',
		phone_3: '',
		website: '',
		inn: 0,
		kpp: 0,
		ogrn: 0,
		vat: 0,
		contact_person: '',
	};
};

const openCreateDialog = () => {
	resetNewCompanyForm();
	showCreateDialog.value = true;
};

const viewCompany = (company: Company) => {
	router.visit(`/app/companies/${company.id}`);
};

const createCompany = () => {
	isProcessing.value = true;

	router.post('/app/companies', newCompany.value, {
		preserveScroll: true,
		onSuccess: () => {
			showCreateDialog.value = false;
			resetNewCompanyForm();
			toast('Компания успешно создан', {
				description: 'Новая компания добавлена в список',
			});
		},
		onError: (errors) => {
			const firstError = errors && typeof errors === 'object' ? (errors as any)[Object.keys(errors)[0]] : null;
			const errorMessage = firstError as string;
			toast('Ошибка при создании компании', {
				description: errorMessage || 'Проверьте корректность введенных данных',
			});
		},
		onFinish: () => {
			isProcessing.value = false;
		},
	});
};

const deleteCompany = (company: Company) => {
	if (!confirm(`Вы уверены, что хотите удалить "${company.short_name}"?`)) {
		return;
	}

	router.delete(`/app/companies/${company.id}`, {
		preserveScroll: true,
		onSuccess: () => {
			toast('Компания успешно удалена', {
				description: 'Компания удалена из списка',
			});
		},
		onError: () => {
			toast('Ошибка при удалении компании', {
				description: 'Не удалось удалить компанию',
			});
		},
	});
};
</script>

<template>
	<Head title="Компании" />
	<AuthenticatedHeaderLayout />
	
	<Toaster />

	<div class="container p-4 md:p-6 max-w-7xl mx-auto">
		<!-- <div class="mb-6">
			<h1 class="text-3xl font-bold tracking-tight">Настройки</h1>
			<p class="text-muted-foreground mt-2">Управляйте своими личными данными и компаниями</p>
		</div> -->

		<SettingsLayout>
			<div class="space-y-6">
				<!-- Header -->
				<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
					<div>
						<h2 class="text-2xl font-semibold tracking-tight">Компании</h2>
						<p class="text-sm text-muted-foreground mt-1">
							Управление компаниями-заказчиками
						</p>
					</div>
					<Button @click="openCreateDialog" class="sm:w-auto">
						<PlusIcon class="h-4 w-4 mr-2" />
						Добавить
					</Button>
				</div>

				<!-- Empty State -->
				<Card v-if="!hasCompanies" class="text-center py-12">
					<CardContent class="flex flex-col items-center justify-center">
						<div class="mx-auto w-20 h-20 bg-muted/50 rounded-full flex items-center justify-center mb-4">
							<BuildingIcon class="h-10 w-10 text-muted-foreground" />
						</div>
						<h3 class="text-xl font-semibold mb-2">Компаний пока нет</h3>
						<p class="text-muted-foreground mb-6 max-w-sm">
							Добавьте свою первую компанию-заказчика
						</p>
						<Button @click="openCreateDialog">
							<PlusIcon class="h-4 w-4 mr-2" />
							Добавить
						</Button>
					</CardContent>
				</Card>

				<!-- Companies Content -->
				<div v-else>
					<!-- Mobile Cards View -->
					<div class="md:hidden space-y-4">
						<Card
							v-for="company in companies"
							:key="company.id"
							class="transition-all duration-200 hover:border-border/60 shadow-sm"
						>
							<CardHeader class="pb-3">
								<div class="flex items-start justify-between gap-2">
									<div class="flex-1 min-w-0">
										<CardTitle class="text-lg mb-1">{{ company.short_name }}</CardTitle>
										<CardDescription class="text-sm">{{ company.full_name }}</CardDescription>
									</div>
									<Badge variant="outline" class="flex-shrink-0">
										<PercentIcon class="h-3 w-3 mr-1" />
										НДС {{ company.vat }}%
									</Badge>
								</div>
							</CardHeader>
							<CardContent class="space-y-3">
								<div v-if="company.boss_name" class="flex items-start gap-2 text-sm">
									<UserIcon class="h-4 w-4 text-muted-foreground flex-shrink-0 mt-0.5" />
									<div class="flex-1 min-w-0">
										<div class="font-medium">{{ bossTitle[company.boss_title || 'director'] }}</div>
										<div class="text-muted-foreground">{{ company.boss_name }}</div>
									</div>
								</div>

								<div class="flex items-center gap-2 text-sm">
									<PhoneIcon class="h-4 w-4 text-muted-foreground flex-shrink-0" />
									<a :href="'tel:' + company.phone" class="text-primary hover:underline font-mono">
										{{ company.phone }}
									</a>
								</div>

								<div v-if="company.email" class="flex items-center gap-2 text-sm">
									<MailIcon class="h-4 w-4 text-muted-foreground flex-shrink-0" />
									<a :href="'mailto:' + company.email" class="text-primary hover:underline break-all">
										{{ company.email }}
									</a>
								</div>

								<div v-if="company.legal_address" class="flex items-start gap-2 text-sm">
									<MapPinIcon class="h-4 w-4 text-muted-foreground flex-shrink-0 mt-0.5" />
									<span class="flex-1 break-words">{{ company.legal_address }}</span>
								</div>

								<div v-if="company.website" class="flex items-center gap-2 text-sm">
									<GlobeIcon class="h-4 w-4 text-muted-foreground flex-shrink-0" />
									<a :href="company.website" target="_blank" class="text-primary hover:underline break-all">
										{{ company.website }}
									</a>
								</div>

								<div class="pt-3 border-t flex items-center justify-between text-xs text-muted-foreground">
									<div>
										<div>ИНН: {{ company.inn }}</div>
										<div v-if="company.kpp">КПП: {{ company.kpp }}</div>
									</div>
									<DropdownMenu>
										<DropdownMenuTrigger as-child>
											<Button variant="outline" size="sm" class="h-8 w-8 p-0">
												<EllipsisVerticalIcon class="h-4 w-4" />
											</Button>
										</DropdownMenuTrigger>
										<DropdownMenuContent align="end">
											<DropdownMenuLabel>Действия</DropdownMenuLabel>
											<DropdownMenuSeparator />
											<DropdownMenuItem @click="viewCompany(company)">
												<EyeIcon class="h-4 w-4" />
												<span>Просмотр</span>
											</DropdownMenuItem>
											<!-- <DropdownMenuItem disabled>
												<ReceiptTextIcon class="h-4 w-4" />
												<span>Счета</span>
											</DropdownMenuItem> -->
											<DropdownMenuSeparator />
											<DropdownMenuItem @click="deleteCompany(company)" class="text-destructive focus:text-destructive hover:bg-destructive/10">
												<TrashIcon class="h-4 w-4" />
												<span>Удалить</span>
											</DropdownMenuItem>
										</DropdownMenuContent>
									</DropdownMenu>
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
										<TableHead class="font-semibold">Компания</TableHead>
										<TableHead class="font-semibold">Руководитель</TableHead>
										<TableHead class="font-semibold">Контакты</TableHead>
										<TableHead class="font-semibold">Реквизиты</TableHead>
										<TableHead class="font-semibold text-center">НДС</TableHead>
										<TableHead class="font-semibold text-right">Действия</TableHead>
									</TableRow>
								</TableHeader>
								
								<TableBody>
									<TableRow v-for="company in companies" :key="company.id" class="hover:bg-muted/30">
										<TableCell class="max-w-xs">
											<div class="font-medium">{{ company.short_name }}</div>
											<div class="text-sm text-muted-foreground line-clamp-1">{{ company.full_name }}</div>
										</TableCell>
										<TableCell>
											<div v-if="company.boss_name" class="space-y-0.5">
												<div class="text-sm font-medium">{{ bossTitle[company.boss_title || 'director'] }}</div>
												<div class="text-sm text-muted-foreground">{{ company.boss_name }}</div>
											</div>
											<span v-else class="text-sm text-muted-foreground">—</span>
										</TableCell>
										<TableCell>
											<div class="space-y-1">
												<div class="text-sm font-mono">{{ company.phone }}</div>
												<div v-if="company.email" class="text-sm text-muted-foreground line-clamp-1">
													{{ company.email }}
												</div>
											</div>
										</TableCell>
										<TableCell>
											<div class="text-sm space-y-0.5">
												<div>ИНН: {{ company.inn }}</div>
												<div v-if="company.kpp" class="text-muted-foreground">КПП: {{ company.kpp }}</div>
											</div>
										</TableCell>
										<TableCell class="text-center">
											<Badge variant="outline">{{ company.vat }}%</Badge>
										</TableCell>
										<TableCell class="text-right">
											<DropdownMenu>
												<DropdownMenuTrigger as-child>
													<Button variant="outline" size="icon" class="h-8 w-8">
														<EllipsisVerticalIcon class="h-4 w-4" />
													</Button>
												</DropdownMenuTrigger>
												<DropdownMenuContent align="end">
													<DropdownMenuLabel>Действия</DropdownMenuLabel>
													<DropdownMenuSeparator />
													<DropdownMenuItem @click="viewCompany(company)">
														<EyeIcon class="h-4 w-4" />
														<span>Просмотр</span>
													</DropdownMenuItem>
													<!-- <DropdownMenuItem disabled>
														<ReceiptTextIcon class="h-4 w-4" />
														<span>Счета</span>
													</DropdownMenuItem> -->
													<DropdownMenuSeparator />
													<DropdownMenuItem @click="deleteCompany(company)" class="text-destructive focus:text-destructive hover:bg-destructive/10 focus:bg-destructive/10">
														<TrashIcon class="h-4 w-4" />
														<span>Удалить</span>
													</DropdownMenuItem>
												</DropdownMenuContent>
											</DropdownMenu>
										</TableCell>
									</TableRow>
								</TableBody>
							</Table>
						</Card>
					</div>
				</div>
			</div>
		</SettingsLayout>
	</div>

	<!-- Create Company Dialog -->
	<Dialog v-model:open="showCreateDialog">
		<DialogContent class="max-w-3xl max-h-[90vh] overflow-y-auto">
			<DialogHeader>
				<DialogTitle>Добавить компанию</DialogTitle>
				<!-- <DialogDescription>
					Заполните информацию о новой компании-заказчике
				</DialogDescription> -->
			</DialogHeader>
			
			<form @submit.prevent="createCompany" class="space-y-4">
				<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
					<div class="space-y-2">
						<Label for="create-short-name">
							Короткое название <span class="text-destructive">*</span>
						</Label>
						<Input
							id="create-short-name"
							v-model="newCompany.short_name"
							required
							placeholder="ООО «Компания»"
						/>
					</div>

					<div class="space-y-2">
						<Label for="create-full-name">
							Полное название <span class="text-destructive">*</span>
						</Label>
						<Input
							id="create-full-name"
							v-model="newCompany.full_name"
							required
							placeholder="Общество с ограниченной ответственностью «Компания»"
						/>
					</div>

					<div class="space-y-2">
						<Label for="create-boss-name">Ф.И.О. руководителя</Label>
						<Input
							id="create-boss-name"
							v-model="newCompany.boss_name"
							placeholder="Иванов Иван Иванович"
						/>
					</div>

					<div class="space-y-2">
						<Label for="create-boss-title">Должность руководителя</Label>
						<Select v-model="newCompany.boss_title">
							<SelectTrigger id="create-boss-title">
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

					<div class="space-y-2">
						<Label for="create-phone">
							Телефон <span class="text-destructive">*</span>
						</Label>
						<Input
							id="create-phone"
							v-model="newCompany.phone"
							v-maska="'+7 (###) ### ##-##'"
							required
							placeholder="+7 (___) ___ __-__"
						/>
					</div>

					<div class="space-y-2">
						<Label for="create-phone-2">Телефон 2</Label>
						<Input
							id="create-phone-2"
							v-model="newCompany.phone_2"
							v-maska="'+7 (###) ### ##-##'"
							placeholder="+7 (___) ___ __-__"
						/>
					</div>

					<div class="space-y-2">
						<Label for="create-email">Email</Label>
						<Input
							id="create-email"
							v-model="newCompany.email"
							type="email"
							placeholder="example@company.com"
						/>
					</div>

					<div class="space-y-2">
						<Label for="create-website">Сайт</Label>
						<Input
							id="create-website"
							v-model="newCompany.website"
							type="url"
							placeholder="https://company.com"
						/>
					</div>

					<div class="space-y-2">
						<Label for="create-inn">
							ИНН <span class="text-destructive">*</span>
						</Label>
						<Input
							id="create-inn"
							v-model.number="newCompany.inn"
							type="number"
							required
							placeholder="1234567890"
						/>
					</div>

					<div class="space-y-2">
						<Label for="create-kpp">КПП</Label>
						<Input
							id="create-kpp"
							v-model.number="newCompany.kpp"
							type="number"
							placeholder="123456789"
						/>
					</div>

					<div class="space-y-2">
						<Label for="create-ogrn">ОГРН</Label>
						<Input
							id="create-ogrn"
							v-model.number="newCompany.ogrn"
							type="number"
							placeholder="1234567890123"
						/>
					</div>

					<div class="space-y-2 md:col-span-2">
						<Label for="create-vat">
							НДС (%) <span class="text-destructive">*</span>
						</Label>
						<Select :model-value="String(newCompany.vat)" @update:model-value="(value) => newCompany.vat = Number(value)">
							<SelectTrigger id="create-vat">
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

					<div class="space-y-2 md:col-span-2">
						<Label for="create-legal-address">Юридический адрес</Label>
						<Input
							id="create-legal-address"
							v-model="newCompany.legal_address"
							placeholder="г. Москва, ул. Примерная, д. 1"
						/>
					</div>

					<div class="space-y-2 md:col-span-2">
						<Label for="create-contact-person">Контактное лицо</Label>
						<Input
							id="create-contact-person"
							v-model="newCompany.contact_person"
							placeholder="Петров Петр Петрович"
						/>
					</div>
				</div>

				<DialogFooter>
					<Button
						type="button"
						variant="outline"
						@click="showCreateDialog = false"
						:disabled="isProcessing"
					>
						Отмена
					</Button>
					<Button type="submit" :disabled="isProcessing">
						<div v-if="isProcessing" class="h-4 w-4 mr-2 animate-spin rounded-full border-2 border-current border-t-transparent"></div>
						{{ isProcessing ? 'Создание...' : 'Создать' }}
					</Button>
				</DialogFooter>
			</form>
		</DialogContent>
	</Dialog>
</template>

