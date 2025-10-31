<script setup lang="ts">
import { useForm, Head } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import { vMaska } from "maska/vue";
import { Label } from "../../Components/ui/label/";
import { Input } from "../../Components/ui/input/";
import { Button } from "../../Components/ui/button/";
import { Textarea } from "../../Components/ui/textarea/";
import {
	Select,
	SelectContent,
	SelectGroup,
	SelectItem,
	SelectTrigger,
	SelectValue,
} from "../../Components/ui/select";
import { EyeClosedIcon, EyeIcon, EyeOffIcon } from "lucide-vue-next";
import { toast } from "vue-sonner";
import { Toaster } from "../../Components/ui/sonner";

const props = defineProps<{
	token: string;
	expiresAt: string;
	errors?: any;
}>();

const form = useForm({
	name: "",
	email: "",
	phone: "",
	telegram: "",
	country: "",
	region: "",
	address: "",
	// company: "",
	password: "",
	password_confirmation: "",
	// Requisites
	// inn: "",
	// kpp: "",
	// current_account: "",
	// correspondent_account: "",
	// bik: "",
	// bank: "",
	// legal_address: "",
});

// const showRequisites = ref(false);
const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

const countries = {
	Армения: ["Арагацотн", "Арарат", "Армавир", "Гегаркуник", "Котайк", "Лори", "Ширак", "Сюник", "Тавуш", "Вайоц Дзор", "Ереван"],
	Беларусь: ["Брестская область", "Гомельская область", "Гродненская область", "Минская область", "Могилевская область", "Витебская область", "Минск"],
	Казахстан: ["Акмолинская область", "Актюбинская область", "Алматинская область", "Атырауская область", "Восточно-Казахстанская область", "Жамбылская область", "Карагандинская область", "Костанайская область", "Кызылординская область", "Мангистауская область", "Северо-Казахстанская область", "Павлодарская область", "Туркестанская область", "Западно-Казахстанская область", "Алматы", "Астана", "Шымкент"],
	Киргизия: ["Баткенская область", "Чуйская область", "Джалал-Абадская область", "Нарынская область", "Ошская область", "Таласская область", "Иссык-Кульская область", "Бишкек", "Ош"],
	Россия: ["Республика Адыгея", "Республика Башкортостан", "Республика Бурятия", "Республика Алтай", "Республика Дагестан", "Республика Ингушетия", "Кабардино-Балкарская Республика", "Республика Калмыкия", "Карачаево-Черкесская Республика", "Республика Карелия", "Республика Крым", "Республика Коми", "Республика Марий Эл", "Республика Мордовия", "Республика Саха (Якутия)", "Республика Северная Осетия-Алания", "Республика Татарстан", "Республика Тыва", "Удмуртская Республика", "Республика Хакасия", "Чеченская Республика", "Чувашская Республика", "Алтайский край", "Забайкальский край", "Камчатский край", "Краснодарский край", "Красноярский край", "Пермский край", "Приморский край", "Ставропольский край", "Хабаровский край", "Амурская область", "Архангельская область", "Астраханская область", "Белгородская область", "Брянская область", "Владимирская область", "Волгоградская область", "Вологодская область", "Воронежская область", "Ивановская область", "Иркутская область", "Калининградская область", "Калужская область", "Кемеровская область", "Кировская область", "Костромская область", "Курганская область", "Курская область", "Ленинградская область", "Липецкая область", "Магаданская область", "Московская область", "Мурманская область", "Нижегородская область", "Новгородская область", "Новосибирская область", "Омская область", "Оренбургская область", "Орловская область", "Пензенская область", "Псковская область", "Ростовская область", "Рязанская область", "Самарская область", "Саратовская область", "Сахалинская область", "Свердловская область", "Смоленская область", "Тамбовская область", "Тверская область", "Томская область", "Тульская область", "Тюменская область", "Ульяновская область", "Челябинская область", "Ярославская область", "Москва", "Санкт-Петербург", "Севастополь", "Еврейская автономная область", "Ненецкий автономный округ", "Ханты-Мансийский автономный округ", "Чукотский автономный округ", "Ямало-Ненецкий автономный округ"],
};

const regions = computed(() => {
	return form.country ? countries[form.country as keyof typeof countries] || [] : [];
});

const submit = () => {
	form.post(`/register/${props.token}`, {
		onError: (errors) => {
			toast.error("Проверьте правильность заполнения формы");
		},
	});
};
</script>

<template>
	<Head>
		<title>Регистрация дилера</title>
	</Head>

	<Toaster />

	<div class="min-h-screen bg-gradient-to-br from-[#FBD7A5]/10 to-[#FBD7A5]/40 sm:py-12 px-0 sm:px-6 lg:px-8">
		<div class="max-w-4xl mx-auto">
			<!-- Error Alert -->
			<div v-if="errors?.error" class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded">
				<div class="flex">
					<div class="flex-shrink-0">
						<svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
							<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
						</svg>
					</div>
					<div class="ml-3">
						<p class="text-sm text-red-700">{{ errors.error }}</p>
					</div>
				</div>
			</div>

			<div class="bg-white shadow-2xl sm:rounded-2xl overflow-hidden">
				<!-- Header -->
				<div class="bg-[#23322D] px-8 py-6">
					<h1 class="text-xl  sm:text-3xl font-bold text-[#E7C886] text-center">Регистрация</h1>
				<!-- <p class="text-gray-300 text-center mt-2">Заполните форму для завершения регистрации</p> -->
			</div>

			<form @submit.prevent="submit" class="p-8 space-y-8">
				<!-- Personal Information -->
				<div class="space-y-4">
					<!-- <h2 class="sm:text-xl font-semibold text-[#23322D] border-b border-[#E7C886] pb-2">Основная информация</h2> -->
						<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
							<div class="md:col-span-2">
								<Label for="name">ФИО <span class="text-red-500">*</span></Label>
								<Input v-model="form.name" id="name" type="text" required placeholder="Введите полное имя" />
								<div v-if="form.errors.name" class="text-xs text-red-500 mt-1">{{ form.errors.name }}</div>
							</div>

							<div>
								<Label for="email">Email <span class="text-red-500">*</span></Label>
								<Input v-model="form.email" id="email" type="email" required placeholder="example@domain.com" />
								<div v-if="form.errors.email" class="text-xs text-red-500 mt-1">{{ form.errors.email }}</div>
							</div>

							<div>
								<Label for="phone">Телефон <span class="text-red-500">*</span></Label>
								<Input v-model="form.phone" id="phone" type="tel" v-maska="'+7 (###) ### ##-##'" required placeholder="+7 (__) ___ __-__" />
								<div v-if="form.errors.phone" class="text-xs text-red-500 mt-1">{{ form.errors.phone }}</div>
							</div>

							<div class="md:col-span-2">
								<Label for="telegram">Telegram</Label>
								<Input v-model="form.telegram" id="telegram" type="text" placeholder="@username" />
								<div v-if="form.errors.telegram" class="text-xs text-red-500 mt-1">{{ form.errors.telegram }}</div>
							</div>
						</div>
					</div>

					<!-- Address Information -->
					<div class="space-y-4">
						<!-- <h2 class="sm:text-xl font-semibold text-[#23322D] border-b border-[#E7C886] pb-2">Адресная информация</h2> -->
						<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
							<div>
								<Label for="country">Страна <span class="text-red-500">*</span></Label>
								<Select v-model="form.country" required>
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
								<div v-if="form.errors.country" class="text-xs text-red-500 mt-1">{{ form.errors.country }}</div>
							</div>

							<div>
								<Label for="region">Регион <span class="text-red-500">*</span></Label>
								<Select v-model="form.region" :disabled="!form.country" required>
									<SelectTrigger>
										<SelectValue placeholder="Выберите регион" />
									</SelectTrigger>
									<SelectContent>
										<SelectGroup>
											<SelectItem v-for="region in regions" :key="region" :value="region">
												{{ region }}
											</SelectItem>
										</SelectGroup>
									</SelectContent>
								</Select>
								<div v-if="form.errors.region" class="text-xs text-red-500 mt-1">{{ form.errors.region }}</div>
							</div>

							<div class="md:col-span-2">
								<Label for="address">Адрес <span class="text-red-500">*</span></Label>
								<Input v-model="form.address" id="address" type="text" required placeholder="Введите полный адрес..." /> 
								<!-- <Textarea v-model="form.address" id="address" required placeholder="Введите полный адрес" :rows="2" /> -->
								<div v-if="form.errors.address" class="text-xs text-red-500 mt-1">{{ form.errors.address }}</div>
							</div>
						</div>
					</div>

					<!-- Company Information -->
					<!-- <div class="space-y-4">
						<h2 class="sm:text-xl font-semibold text-[#23322D] border-b border-[#E7C886] pb-2">Информация о компании</h2>
						<div>
							<Label for="company">Контрагент <span class="text-red-500">*</span></Label>
							<Input v-model="form.company" id="company" type="text" required placeholder="Название компании" />
							<div v-if="form.errors.company" class="text-xs text-red-500 mt-1">{{ form.errors.company }}</div>
						</div>
					</div> -->

					<!-- Requisites (Optional) -->
					<!-- <div class="space-y-4">
						<div class="flex items-center justify-between border-b border-[#E7C886] pb-2">
							<h2 class="sm:text-xl font-semibold text-[#23322D]">Реквизиты (необязательно)</h2>
							<Button type="button" variant="outline" size="sm" @click="showRequisites = !showRequisites" class="text-[#23322D] hover:text-[#E7C886]">
								<EyeClosedIcon v-if="!showRequisites" />
								<EyeIcon v-else />
							</Button>
						</div>
						<div v-if="showRequisites" class="grid grid-cols-1 md:grid-cols-4 gap-4">
							<div>
								<Label for="inn">ИНН</Label>
								<Input v-model="form.inn" id="inn" type="text" maxlength="12" placeholder="0000000000" />
							</div>
							<div>
								<Label for="kpp">КПП</Label>
								<Input v-model="form.kpp" id="kpp" type="text" maxlength="9" placeholder="000000000" />
							</div>
							<div class="md:col-span-2">
								<Label for="current_account">Расчетный счет</Label>
								<Input v-model="form.current_account" id="current_account" type="text" maxlength="20" placeholder="00000000000000000000" />
							</div>
							<div class="md:col-span-2">
								<Label for="correspondent_account">Корреспондентский счет</Label>
								<Input v-model="form.correspondent_account" id="correspondent_account" type="text" maxlength="20" placeholder="00000000000000000000" />
							</div>
							<div>
								<Label for="bik">БИК</Label>
								<Input v-model="form.bik" id="bik" type="text" maxlength="9" placeholder="000000000" />
							</div>
							<div class="md:col-span-3">
								<Label for="bank">Банк</Label>
								<Input v-model="form.bank" id="bank" type="text" placeholder="Название банка" />
							</div>
							<div class="md:col-span-4">
								<Label for="legal_address">Юридический адрес</Label>
								<Textarea v-model="form.legal_address" id="legal_address" placeholder="Полный юридический адрес" :rows="2" />
							</div>
						</div>
					</div> -->

					<!-- Password -->
					<div class="space-y-4">
						<!-- <h2 class="sm:text-xl font-semibold text-[#23322D] border-b border-[#E7C886] pb-2">Безопасность</h2> -->
						<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
							<div class="space-y-2">
								<Label for="password">Пароль (минимум 8 символов) <span class="text-red-500">*</span></Label>
								<div class="relative">
									<Input 
										v-model="form.password" 
										id="password" 
										:type="showPassword ? 'text' : 'password'" 
										required 
										minlength="8"
										placeholder="Минимум 8 символов"
										class="pr-10"
									/>
									<Button 
										type="button" 
										variant="ghost" 
										size="icon" 
										class="absolute right-0 top-0 h-full hover:bg-transparent" 
										@click="showPassword = !showPassword"
									>
										<EyeIcon v-if="showPassword" class="h-4 w-4" />
										<EyeOffIcon v-else class="h-4 w-4" />
									</Button>
								</div>
								<div v-if="form.errors.password" class="text-xs text-red-500 mt-1">{{ form.errors.password }}</div>
							</div>
							<div class="space-y-2">
								<Label for="password_confirmation">Подтверждение пароля <span class="text-red-500">*</span></Label>
								<div class="relative">
									<Input 
										v-model="form.password_confirmation" 
										id="password_confirmation" 
										:type="showPasswordConfirmation ? 'text' : 'password'" 
										required 
										minlength="8"
										placeholder="Повторите пароль"
										class="pr-10"
									/>
									<Button 
										type="button" 
										variant="ghost" 
										size="icon" 
										class="absolute right-0 top-0 h-full hover:bg-transparent" 
										@click="showPasswordConfirmation = !showPasswordConfirmation"
									>
										<EyeIcon v-if="showPasswordConfirmation" class="h-4 w-4" />
										<EyeOffIcon v-else class="h-4 w-4" />
									</Button>
								</div>
							</div>
						</div>
					</div>

					<!-- Submit Button -->
					<div class="pt-6 border-t border-[#E7C886]">
						<Button 
							type="submit" 
							class="w-full"
							:disabled="form.processing"
						>
							<span v-if="form.processing">Регистрация...</span>
							<span v-else>Зарегистрироваться</span>
						</Button>
					</div>
				</form>
			</div>

			<!-- Footer -->
			<!-- <p class="text-center text-[#E7C886] mt-6 text-sm bg-[#23322D]/50 backdrop-blur-sm py-3 px-6 rounded-lg">
				Ссылка действительна до {{ new Date(expiresAt).toLocaleString("ru-RU") }}
			</p> -->
		</div>
	</div>
</template>

