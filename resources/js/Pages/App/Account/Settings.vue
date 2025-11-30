<script setup lang="ts">
import { Head, usePage, router } from '@inertiajs/vue3';
import AuthenticatedHeaderLayout from '../../../Layouts/AuthenticatedHeaderLayout.vue';
import SettingsLayout from '../../../Layouts/SettingsLayout.vue';
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
	CheckCircleIcon,
	GlobeIcon
} from 'lucide-vue-next';
import {
	Select,
	SelectContent,
	SelectGroup,
	SelectItem,
	SelectTrigger,
	SelectValue,
} from '../../../Components/ui/select';

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

const countries = {
	Армения: ["Арагацотн", "Арарат", "Армавир", "Гегаркуник", "Котайк", "Лори", "Ширак", "Сюник", "Тавуш", "Вайоц Дзор", "Ереван"],
	Беларусь: ["Брестская область", "Гомельская область", "Гродненская область", "Минская область", "Могилевская область", "Витебская область", "Минск"],
	Казахстан: ["Акмолинская область", "Актюбинская область", "Алматинская область", "Атырауская область", "Восточно-Казахстанская область", "Жамбылская область", "Карагандинская область", "Костанайская область", "Кызылординская область", "Мангистауская область", "Северо-Казахстанская область", "Павлодарская область", "Туркестанская область", "Западно-Казахстанская область", "Алматы", "Астана", "Шымкент"],
	Киргизия: ["Баткенская область", "Чуйская область", "Джалал-Абадская область", "Нарынская область", "Ошская область", "Таласская область", "Иссык-Кульская область", "Бишкек", "Ош"],
	Россия: ["Республика Адыгея", "Республика Башкортостан", "Республика Бурятия", "Республика Алтай", "Республика Дагестан", "Республика Ингушетия", "Кабардино-Балкарская Республика", "Республика Калмыкия", "Карачаево-Черкесская Республика", "Республика Карелия", "Республика Крым", "Республика Коми", "Республика Марий Эл", "Республика Мордовия", "Республика Саха (Якутия)", "Республика Северная Осетия-Алания", "Республика Татарстан", "Республика Тыва", "Удмуртская Республика", "Республика Хакасия", "Чеченская Республика", "Чувашская Республика", "Алтайский край", "Забайкальский край", "Камчатский край", "Краснодарский край", "Красноярский край", "Пермский край", "Приморский край", "Ставропольский край", "Хабаровский край", "Амурская область", "Архангельская область", "Астраханская область", "Белгородская область", "Брянская область", "Владимирская область", "Волгоградская область", "Вологодская область", "Воронежская область", "Ивановская область", "Иркутская область", "Калининградская область", "Калужская область", "Кемеровская область", "Кировская область", "Костромская область", "Курганская область", "Курская область", "Ленинградская область", "Липецкая область", "Магаданская область", "Московская область", "Мурманская область", "Нижегородская область", "Новгородская область", "Новосибирская область", "Омская область", "Оренбургская область", "Орловская область", "Пензенская область", "Псковская область", "Ростовская область", "Рязанская область", "Самарская область", "Саратовская область", "Сахалинская область", "Свердловская область", "Смоленская область", "Тамбовская область", "Тверская область", "Томская область", "Тульская область", "Тюменская область", "Ульяновская область", "Челябинская область", "Ярославская область", "Москва", "Санкт-Петербург", "Севастополь", "Еврейская автономная область", "Ненецкий автономный округ", "Ханты-Мансийский автономный округ", "Чукотский автономный округ", "Ямало-Ненецкий автономный округ"],
};

const form = useForm({
	name: user.value.name,
	company: user.value.company,
	email: user.value.email,
	phone: user.value.phone,
	address: user.value.address,
	country: user.value.country,
	region: user.value.region,
	city: user.value.city,
	telegram: user.value.telegram,
	website: user.value.website,
})

const availableRegions = computed(() => {
	return form.country ? countries[form.country as keyof typeof countries] || [] : [];
});

const resetForm = () => {
	form.reset();
	form.name = user.value.name;
	form.company = user.value.company;
	form.email = user.value.email;
	form.phone = user.value.phone;
	form.address = user.value.address;
	form.country = user.value.country;
	form.region = user.value.region;
	form.city = user.value.city;
	form.telegram = user.value.telegram;
	form.website = user.value.website;
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
			const firstError = errors.logo || (errors && typeof errors === 'object' ? (errors as any)[Object.keys(errors)[0]] : null);
			logoError.value = firstError || 'Ошибка при загрузке логотипа';
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

	<div class="container p-4 md:p-6 max-w-7xl mx-auto">
		<!-- <div class="mb-6">
			<h1 class="text-3xl font-bold tracking-tight">Настройки</h1>
			<p class="text-muted-foreground mt-2">Управляйте своими личными данными и компаниями</p>
		</div> -->

		<SettingsLayout>
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
					<div class="space-y-2">
						<Label for="name" class="text-sm font-medium flex items-center gap-2">
							<UserIcon class="h-4 w-4" />
							Ф.И.О.
						</Label>
						<Input 
							id="name"
							v-model="form.name" 
							type="text" 
							class="transition-all duration-200 focus:ring-2 focus:ring-primary/20" 
							placeholder="Введите ваше полное имя"
						/>
					</div>

					<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
						<div class="space-y-2">
							<Label class="text-sm font-medium flex items-center gap-2">
								<GlobeIcon class="h-4 w-4" />
								Страна
							</Label>
							<Select v-model="form.country">
								<SelectTrigger>
									<SelectValue placeholder="Выберите страну" />
								</SelectTrigger>
								<SelectContent>
									<SelectGroup>
										<SelectItem value="Армения">Армения</SelectItem>
										<SelectItem value="Беларусь">Беларусь</SelectItem>
										<SelectItem value="Казахстан">Казахстан</SelectItem>
										<SelectItem value="Киргизия">Киргизия</SelectItem>
										<SelectItem value="Россия">Россия</SelectItem>
									</SelectGroup>
								</SelectContent>
							</Select>
						</div>

						<div class="space-y-2">
							<Label class="text-sm font-medium flex items-center gap-2">
								<MapPinIcon class="h-4 w-4" />
								Регион
							</Label>
							<Select v-model="form.region" :disabled="!form.country">
								<SelectTrigger>
									<SelectValue placeholder="Выберите регион" />
								</SelectTrigger>
								<SelectContent>
									<SelectGroup>
										<SelectItem v-for="region in availableRegions" :key="region" :value="region">
											{{ region }}
										</SelectItem>
									</SelectGroup>
								</SelectContent>
							</Select>
						</div>

						<div class="space-y-2">
							<Label for="city" class="text-sm font-medium flex items-center gap-2">
								<MapPinIcon class="h-4 w-4" />
								Город
							</Label>
							<Input 
								id="city"
								v-model="form.city" 
								type="text" 
								class="transition-all duration-200 focus:ring-2 focus:ring-primary/20" 
								placeholder="Название города"
							/>
						</div>
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
								Номер телефона
							</Label>
							<Input 
								id="phone"
								v-model="form.phone" 
								type="tel" 
								v-maska="'+7 (###) ### ##-##'" 
								class="transition-all duration-200 focus:ring-2 focus:ring-primary/20"
								placeholder="+7 (___) ___ __-__"
							/>
						</div>

						<div class="space-y-2">
							<Label for="telegram" class="text-sm font-medium flex items-center gap-2">
								<MessageSquareIcon class="h-4 w-4" />
								Telegram
							</Label>
							<Input 
								id="telegram"
								v-model="form.telegram" 
								type="text" 
								class="transition-all duration-200 focus:ring-2 focus:ring-primary/20"
								placeholder="@username"
							/>
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

						<div class="space-y-2">
							<Label for="website" class="text-sm font-medium flex items-center gap-2">
								<GlobeIcon class="h-4 w-4" />
								Веб-сайт
							</Label>
							<Input 
								id="website"
								v-model="form.website" 
								type="url" 
								class="transition-all duration-200 focus:ring-2 focus:ring-primary/20"
								placeholder="https://example.com"
							/>
						</div>
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

					<div class="space-y-2 p-3 bg-blue-50 border border-blue-200 rounded-lg dark:bg-blue-950 dark:border-blue-800">
						<p class="text-xs font-medium text-blue-900 dark:text-blue-100 flex items-center gap-2">
							<CheckCircleIcon class="h-4 w-4" />
							Информация
						</p>
						<ul class="text-xs text-blue-800 dark:text-blue-200 space-y-1 ml-6 list-disc">
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
		</SettingsLayout>
	</div>
</template>
