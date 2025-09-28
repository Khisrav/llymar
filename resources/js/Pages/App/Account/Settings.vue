<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import AuthenticatedHeaderLayout from '../../../Layouts/AuthenticatedHeaderLayout.vue';
import { Label } from '../../../Components/ui/label';
import { Input } from '../../../Components/ui/input';
import { ref } from 'vue';
import { User } from '../../../lib/types';
import { vMaska } from 'maska/vue';
import { Button } from '../../../Components/ui/button';
import { Separator } from '../../../Components/ui/separator';
import { useForm } from '@inertiajs/vue3';
import { Toaster } from '../../../Components/ui/sonner';
import { toast } from 'vue-sonner';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../../Components/ui/card';
import { 
	UserIcon, 
	BuildingIcon, 
	MailIcon, 
	PhoneIcon, 
	MapPinIcon, 
	MessageSquareIcon,
	CreditCardIcon,
	BanknoteIcon,
	ReceiptRussianRubleIcon,
	SaveIcon
} from 'lucide-vue-next';

const user = ref(usePage().props.user as User);

const form = useForm({
	name: user.value.name,
	company: user.value.company,
	email: user.value.email,
	phone: user.value.phone,
	address: user.value.address,
	telegram: user.value.telegram,
	inn: user.value.inn,
	// kpp: user.value.kpp,
	// bik: user.value.bik,
	// bank: user.value.bank,
	legal_address: user.value.legal_address,
	// current_account: user.value.current_account,
	// correspondent_account: user.value.correspondent_account,
})

const resetForm = () => {
	form.reset();
	form.name = user.value.name;
	form.company = user.value.company;
	form.email = user.value.email;
	form.phone = user.value.phone;
	form.address = user.value.address;
	form.telegram = user.value.telegram;
	form.inn = user.value.inn;
	// form.kpp = user.value.kpp;
	// form.bik = user.value.bik;
	// form.bank = user.value.bank;
	form.legal_address = user.value.legal_address;
	// form.current_account = user.value.current_account;
	// form.correspondent_account = user.value.correspondent_account;
}

const updateUser = () => {
	form.post('/app/account/settings', {
		preserveScroll: true,
		onSuccess: () => {
			toast("Настройки успешно сохранены", {
				description: "Ваши данные были обновлены",
			});
		},
		onError: () => {
			toast("Ошибка при сохранении", {
				description: "Проверьте корректность введенных данных",
				action: {
					label: "Попробовать еще раз",
					onClick: () => updateUser()
				}
			});
		},
	});
}
</script>

<template>
	<Head title="Настройки аккаунта" />
	<AuthenticatedHeaderLayout />
	
	<Toaster />

	<div class="container p-4 md:p-6 max-w-6xl mx-auto">
		<div class="mb-6">
			<h1 class="text-3xl font-bold tracking-tight">Настройки</h1>
			<!-- <p class="text-muted-foreground mt-2">Управляйте своими личными данными и реквизитами для ведения бизнеса</p> -->
		</div>

		<form @submit.prevent="updateUser" class="space-y-8">
			<!-- Personal Information Card -->
			<Card>
				<CardHeader>
					<CardTitle class="flex items-center gap-3">
						<UserIcon class="h-5 w-5 text-primary" />
						Личная информация
					</CardTitle>
					<CardDescription>
						Основные данные для персонализации и связи
					</CardDescription>
				</CardHeader>
				<CardContent class="space-y-6">
					<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
						<div class="space-y-2">
							<Label for="name" class="text-sm font-medium flex items-center gap-2">
								<UserIcon class="h-4 w-4" />
								Ф.И.О. <span class="text-destructive">*</span>
							</Label>
							<Input 
								id="name"
								v-model="form.name" 
								type="text" 
								required 
								class="transition-all duration-200 focus:ring-2 focus:ring-primary/20" 
								placeholder="Введите ваше полное имя"
							/>
							<p class="text-xs text-muted-foreground">Используется для персонализации уведомлений и документов</p>
						</div>

						<div class="space-y-2">
							<Label for="address" class="text-sm font-medium flex items-center gap-2">
								<MapPinIcon class="h-4 w-4" />
								Фактический адрес
							</Label>
							<Input 
								id="address"
								v-model="form.address" 
								type="text" 
								class="transition-all duration-200 focus:ring-2 focus:ring-primary/20" 
								placeholder="Введите ваш адрес"
							/>
							<p class="text-xs text-muted-foreground">Необходим для доставки товаров и документов</p>
						</div>
					</div>

					<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
						<div class="space-y-2">
							<Label for="email" class="text-sm font-medium flex items-center gap-2">
								<MailIcon class="h-4 w-4" />
								Электронная почта <span class="text-destructive">*</span>
							</Label>
							<Input 
								id="email"
								v-model="form.email" 
								type="email" 
								required 
								class="transition-all duration-200 focus:ring-2 focus:ring-primary/20"
								placeholder="example@domain.com"
							/>
							<p class="text-xs text-muted-foreground">Для важных уведомлений и подтверждений</p>
						</div>

						<div class="space-y-2">
							<Label for="phone" class="text-sm font-medium flex items-center gap-2">
								<PhoneIcon class="h-4 w-4" />
								Номер телефона <span class="text-destructive">*</span>
							</Label>
							<Input 
								id="phone"
								v-model="form.phone" 
								type="tel" 
								v-maska="'+7 (###) ###-##-##'" 
								required 
								class="transition-all duration-200 focus:ring-2 focus:ring-primary/20"
								placeholder="+7 (___) ___-__-__"
							/>
							<p class="text-xs text-muted-foreground">Для быстрой связи по срочным вопросам</p>
						</div>

						<div class="space-y-2">
							<Label for="telegram" class="text-sm font-medium flex items-center gap-2">
								<MessageSquareIcon class="h-4 w-4" />
								Telegram <span class="text-destructive">*</span>
							</Label>
							<Input 
								id="telegram"
								v-model="form.telegram" 
								type="text" 
								required 
								class="transition-all duration-200 focus:ring-2 focus:ring-primary/20"
								placeholder="@username"
							/>
							<p class="text-xs text-muted-foreground">Для быстрой связи в мессенджере</p>
						</div>
					</div>

					<div class="space-y-2">
						<Label for="company" class="text-sm font-medium flex items-center gap-2">
							<BuildingIcon class="h-4 w-4" />
							Организация
						</Label>
						<Input 
							id="company"
							v-model="form.company" 
							type="text" 
							class="transition-all duration-200 focus:ring-2 focus:ring-primary/20"
							placeholder="Название вашей организации"
						/>
						<p class="text-xs text-muted-foreground">Используется для правильного оформления документов и счетов</p>
					</div>
				</CardContent>
			</Card>

			<!-- Financial Information Card -->
			<Card>
				<CardHeader>
					<CardTitle class="flex items-center gap-3">
						<CreditCardIcon class="h-5 w-5 text-primary" />
						Финансовые реквизиты
					</CardTitle>
					<CardDescription>
						Банковские данные для ведения финансовых операций
					</CardDescription>
				</CardHeader>
				<CardContent class="grid grid-cols-1 md:grid-cols-2 gap-6">
					<div class="grid grid-cols-1 gap-6">
						<div class="space-y-2">
							<Label for="inn" class="text-sm font-medium flex items-center gap-2">
								<ReceiptRussianRubleIcon class="h-4 w-4" />
								ИНН <span class="text-destructive">*</span>
							</Label>
							<Input 
								id="inn"
								v-model="form.inn" 
								type="text" 
								required 
								class="transition-all duration-200 focus:ring-2 focus:ring-primary/20"
								placeholder="Введите ИНН"
							/>
							<p class="text-xs text-muted-foreground">Необходим для налоговых документов</p>
						</div>

						<!-- Hidden fields for now -->
						<!-- <div class="space-y-2">
							<Label for="kpp" class="text-sm font-medium flex items-center gap-2">
								<ReceiptRussianRubleIcon class="h-4 w-4" />
								КПП <span class="text-destructive">*</span>
							</Label>
							<Input 
								id="kpp"
								v-model="form.kpp" 
								type="text" 
								required 
								class="transition-all duration-200 focus:ring-2 focus:ring-primary/20"
								placeholder="Введите КПП"
							/>
							<p class="text-xs text-muted-foreground">Код причины постановки на учет</p>
						</div> -->
					</div>

					<!-- Hidden financial fields for now -->
					<!-- <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
						<div class="space-y-2">
							<Label for="current_account" class="text-sm font-medium flex items-center gap-2">
								<BanknoteIcon class="h-4 w-4" />
								Расчетный счет <span class="text-destructive">*</span>
							</Label>
							<Input 
								id="current_account"
								v-model="form.current_account" 
								type="text" 
								required 
								class="transition-all duration-200 focus:ring-2 focus:ring-primary/20"
								placeholder="20-значный номер счета"
							/>
							<p class="text-xs text-muted-foreground">Основной счет для переводов и платежей</p>
						</div>

						<div class="space-y-2">
							<Label for="correspondent_account" class="text-sm font-medium flex items-center gap-2">
								<BanknoteIcon class="h-4 w-4" />
								Корреспондентский счет
							</Label>
							<Input 
								id="correspondent_account"
								v-model="form.correspondent_account" 
								type="text" 
								class="transition-all duration-200 focus:ring-2 focus:ring-primary/20"
								placeholder="20-значный корр. счет"
							/>
							<p class="text-xs text-muted-foreground">Счет банка в Центральном банке</p>
						</div>
					</div>

					<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
						<div class="space-y-2">
							<Label for="bik" class="text-sm font-medium flex items-center gap-2">
								<CreditCardIcon class="h-4 w-4" />
								БИК
							</Label>
							<Input 
								id="bik"
								v-model="form.bik" 
								type="text" 
								class="transition-all duration-200 focus:ring-2 focus:ring-primary/20"
								placeholder="9-значный БИК"
							/>
							<p class="text-xs text-muted-foreground">Банковский идентификационный код</p>
						</div>

						<div class="space-y-2">
							<Label for="bank" class="text-sm font-medium flex items-center gap-2">
								<BuildingIcon class="h-4 w-4" />
								Банк
							</Label>
							<Input 
								id="bank"
								v-model="form.bank" 
								type="text" 
								class="transition-all duration-200 focus:ring-2 focus:ring-primary/20"
								placeholder="Название банка"
							/>
							<p class="text-xs text-muted-foreground">Полное наименование банковского учреждения</p>
						</div>
					</div> -->

					<div class="space-y-2">
						<Label for="legal_address" class="text-sm font-medium flex items-center gap-2">
							<MapPinIcon class="h-4 w-4" />
							Юридический адрес
						</Label>
						<Input 
							id="legal_address"
							v-model="form.legal_address" 
							type="text" 
							class="transition-all duration-200 focus:ring-2 focus:ring-primary/20"
							placeholder="Юридический адрес организации"
						/>
						<p class="text-xs text-muted-foreground">Официальный адрес регистрации для документов и счетов</p>
					</div>
				</CardContent>
			</Card>

			<!-- Action buttons -->
			<div class="flex flex-col sm:flex-row gap-4 sm:justify-end">
				<Button 
					type="button" 
					variant="outline" 
					class="sm:w-auto"
					@click="resetForm"
				>
					Отменить изменения
				</Button>
				<Button 
					type="submit" 
					:disabled="form.processing"
					class="sm:w-auto"
				>
					<SaveIcon v-if="!form.processing" class="h-4 w-4 mr-2" />
					<div v-else class="h-4 w-4 mr-2 animate-spin rounded-full border-2 border-current border-t-transparent"></div>
					{{ form.processing ? 'Сохранение...' : 'Сохранить настройки' }}
				</Button>
			</div>
		</form>
	</div>
</template>
