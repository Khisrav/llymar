<script setup lang="ts">
import { Head, usePage, router } from '@inertiajs/vue3';
import AuthenticatedHeaderLayout from '../../../Layouts/AuthenticatedHeaderLayout.vue';
import { Label } from '../../../Components/ui/label';
import { Input } from '../../../Components/ui/input';
import { ref, computed } from 'vue';
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
	SaveIcon,
	UploadIcon,
	TrashIcon,
	AlertCircleIcon,
	CheckCircleIcon
} from 'lucide-vue-next';

const user = ref(usePage().props.user as User);

// Logo upload state
const logoInput = ref<HTMLInputElement | null>(null);
const selectedFile = ref<File | null>(null);
const logoPreview = ref<string | null>(null);
const logoUploading = ref(false);
const logoError = ref<string | null>(null);
const logoSuccess = ref(false);

// Logo validation constants
const LOGO_MAX_SIZE = 2 * 1024 * 1024; // 2MB
const LOGO_ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/webp'];
const LOGO_ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

const form = useForm({
	name: user.value.name,
	company: user.value.company,
	email: user.value.email,
	phone: user.value.phone,
	address: user.value.address,
	telegram: user.value.telegram,
})

const resetForm = () => {
	form.reset();
	form.name = user.value.name;
	form.company = user.value.company;
	form.email = user.value.email;
	form.phone = user.value.phone;
	form.address = user.value.address;
	form.telegram = user.value.telegram;
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

// Logo upload handlers
const handleLogoChange = (event: Event) => {
	const input = event.target as HTMLInputElement;
	const file = input.files?.[0];
	
	if (!file) {
		logoError.value = null;
		logoPreview.value = null;
		selectedFile.value = null;
		return;
	}

	// Reset previous states
	logoError.value = null;
	logoSuccess.value = false;

	// Validate file size
	if (file.size > LOGO_MAX_SIZE) {
		logoError.value = `Размер логотипа не должен превышать 2 МБ. Текущий размер: ${(file.size / 1024 / 1024).toFixed(2)} МБ`;
		input.value = '';
		selectedFile.value = null;
		return;
	}

	// Validate file type
	if (!LOGO_ALLOWED_TYPES.includes(file.type)) {
		logoError.value = 'Логотип должен быть в формате JPG, PNG или WebP.';
		input.value = '';
		selectedFile.value = null;
		return;
	}

	// Store the selected file
	selectedFile.value = file;

	// Create preview
	const reader = new FileReader();
	reader.onload = (e) => {
		logoPreview.value = e.target?.result as string;
	};
	reader.onerror = () => {
		logoError.value = 'Невозможно обработать изображение. Убедитесь, что файл является корректным изображением.';
		input.value = '';
		logoPreview.value = null;
		selectedFile.value = null;
	};
	reader.readAsDataURL(file);
};

const uploadLogo = () => {
	const file = selectedFile.value;

	if (!file) {
		toast('Пожалуйста, выберите логотип', {
			description: 'Никакой файл не был выбран',
		});
		return;
	}

	logoUploading.value = true;
	logoError.value = null;

	router.post('/app/account/logo/upload', {
		logo: file,
	}, {
		forceFormData: true,
		preserveScroll: true,
		onSuccess: (page) => {
			// Update user data with the new logo path
			if (page.props.user) {
				user.value = page.props.user as User;
			}
			
			// Clear selection state
			logoSuccess.value = true;
			logoPreview.value = null;
			selectedFile.value = null;
			
			// Clear the file input
			if (logoInput.value) {
				const inputElement = logoInput.value as any;
				if (inputElement.$el && inputElement.$el.value) {
					inputElement.$el.value = '';
				} else if (inputElement.value !== undefined) {
					inputElement.value = '';
				}
			}

			toast('Логотип успешно загружен', {
				description: 'Ваш логотип будет использован в коммерческих предложениях',
			});
			
			logoUploading.value = false;
		},
		onError: (errors) => {
			console.error('Logo upload errors:', errors);
			logoError.value = errors.logo || Object.values(errors)[0] || 'Ошибка при загрузке логотипа';
			toast('Ошибка при загрузке логотипа', {
				description: logoError.value as string,
			});
			logoUploading.value = false;
		},
	});

	logoUploading.value = false;
};

const deleteLogo = () => {
	if (!confirm('Вы уверены, что хотите удалить логотип? Будет использоваться логотип по умолчанию.')) {
		return;
	}

	logoUploading.value = true;
	logoError.value = null;

	router.delete('/app/account/logo/delete', {
		preserveScroll: true,
		onSuccess: (page) => {
			// Update user data
			if (page.props.user) {
				user.value = page.props.user as User;
			}
			
			toast('Логотип успешно удален', {
				description: 'Будет использоваться логотип LLYMAR по умолчанию',
			});
			
			logoUploading.value = false;
		},
		onError: (errors) => {
			logoError.value = 'Ошибка при удалении логотипа';
			toast('Ошибка при удалении логотипа', {
				description: logoError.value,
			});
			logoUploading.value = false;
		},
	});
};

const currentLogoUrl = computed(() => {
	if (user.value.logo) {
		return `/storage/${user.value.logo}`;
	}
	return null;
});

const hasLogo = computed(() => {
	return !!user.value.logo || logoSuccess.value;
});

const hasFileSelected = computed(() => {
	return !!selectedFile.value;
});

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
								v-maska="'+7 (###) ### ##-##'" 
								required 
								class="transition-all duration-200 focus:ring-2 focus:ring-primary/20"
								placeholder="+7 (___) ___ __-__"
							/>
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
								class="transition-all duration-200 focus:ring-2 focus:ring-primary/20"
								placeholder="@username"
							/>
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
					</div>
				</CardContent>
			</Card>

			<!-- Logo Card -->
			<Card>
				<CardHeader>
					<CardTitle class="flex items-center gap-3">
						<UploadIcon class="h-5 w-5 text-primary" />
						Логотип компании
					</CardTitle>
					<CardDescription>
						Загрузите свой логотип для использования в коммерческих предложениях
					</CardDescription>
				</CardHeader>
				<CardContent class="space-y-6">
					<!-- Current Logo Display -->
					<div v-if="currentLogoUrl" class="flex flex-col gap-2">
						<Label class="text-sm font-medium">Текущий логотип</Label>
						<div>
							<div class="relative inline-block border border-blue-300 rounded-lg p-3 bg-blue-50">
								<img :src="currentLogoUrl" alt="Current logo" class="max-h-32 max-w-xs object-contain">
							</div>
						</div>
					</div>

					<!-- Logo Preview -->
					<div v-if="logoPreview" class="flex flex-col gap-2">
						<Label class="text-sm font-medium">Предпросмотр логотипа</Label>
						<div>
							<div class="relative inline-block border border-blue-300 rounded-lg p-3 bg-blue-50">
								<img :src="logoPreview" alt="Logo preview" class="max-h-32 max-w-xs object-contain">
							</div>
						</div>
					</div>

					<!-- Error Message -->
					<div v-if="logoError" class="flex items-start gap-3 p-3 bg-red-50 border border-red-200 rounded-lg">
						<AlertCircleIcon class="h-5 w-5 text-red-500 flex-shrink-0 mt-0.5" />
						<p class="text-sm text-red-700">{{ logoError }}</p>
					</div>


					<!-- File Input and Upload Section -->
					<div class="space-y-4">
						<div class="space-y-2">
							<Input
								ref="logoInput"
								type="file"
								accept="image/jpeg,image/png,image/webp"
								@change="handleLogoChange"
								class="cursor-pointer"
								:disabled="logoUploading"
							/>
						<p class="text-xs text-muted-foreground">
							Поддерживаемые форматы: JPG, PNG, WebP (максимум 2 МБ)
						</p>
						</div>

					<!-- Upload Buttons -->
					<div class="flex flex-col sm:flex-row gap-2 sm:justify-start">
						<Button
							type="button"
							@click="uploadLogo"
							:disabled="!hasFileSelected || logoUploading"
							class="sm:w-auto"
						>
							<UploadIcon v-if="!logoUploading" class="h-4 w-4 mr-2" />
							<div v-else class="h-4 w-4 mr-2 animate-spin rounded-full border-2 border-current border-t-transparent"></div>
							{{ logoUploading ? 'Загрузка...' : 'Загрузить логотип' }}
						</Button>

							<Button
								v-if="currentLogoUrl"
								type="button"
								variant="outline"
								@click="deleteLogo"
								:disabled="logoUploading"
								class="sm:w-auto"
							>
								<TrashIcon class="h-4 w-4 mr-2" />
								Удалить логотип
							</Button>
						</div>
					</div>

					<div class="space-y-2 p-3 bg-blue-50 border border-blue-200 rounded-lg">
						<p class="text-xs font-medium text-blue-900 flex items-center gap-2">
							<CheckCircleIcon class="h-4 w-4" />
							Информация
						</p>
						<ul class="text-xs text-blue-800 space-y-1 ml-6 list-disc">
							<li>Логотип не является обязательным полем</li>
							<li>Если логотип не загружен, будет использоваться логотип LLYMAR по умолчанию</li>
							<li>Логотип будет отображаться в коммерческих предложениях (PDF)</li>
							<li>Новый логотип не будет применен для уже созданных коммерческих предложений</li>
						</ul>
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
