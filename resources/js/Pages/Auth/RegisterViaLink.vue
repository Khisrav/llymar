<script setup lang="ts">
import { useForm, Head } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import Label from "../../Components/ui/label/Label.vue";
import Input from "../../Components/ui/input/Input.vue";
import Button from "../../Components/ui/button/Button.vue";
import Textarea from "../../Components/ui/textarea/Textarea.vue";
import {
	Select,
	SelectContent,
	SelectGroup,
	SelectItem,
	SelectTrigger,
	SelectValue,
} from "../../Components/ui/select";
import { useToast } from "../../Components/ui/toast";

const props = defineProps<{
	token: string;
	expiresAt: string;
}>();

const { toast } = useToast();

const form = useForm({
	name: "",
	email: "",
	phone: "",
	telegram: "",
	country: "",
	region: "",
	address: "",
	company: "",
	password: "",
	password_confirmation: "",
	// Requisites
	inn: "",
	kpp: "",
	current_account: "",
	correspondent_account: "",
	bik: "",
	bank: "",
	legal_address: "",
});

const showRequisites = ref(false);

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
		onSuccess: (response: any) => {
			toast({
				title: "Успешно!",
				description: "Регистрация завершена. Перенаправление на страницу входа...",
			});
			setTimeout(() => {
				window.location.href = "/login";
			}, 2000);
		},
		onError: (errors) => {
			toast({
				title: "Ошибка",
				description: "Проверьте правильность заполнения формы",
				variant: "destructive",
			});
		},
	});
};
</script>

<template>
	<Head>
		<title>Регистрация дилера</title>
	</Head>
	<div class="min-h-screen bg-gradient-to-br from-fuchsia-50 to-purple-50 dark:from-gray-900 dark:to-gray-800 py-12 px-4 sm:px-6 lg:px-8">
		<div class="max-w-4xl mx-auto">
			<div class="bg-white dark:bg-gray-800 shadow-2xl rounded-2xl overflow-hidden">
				<!-- Header -->
				<div class="bg-gradient-to-r from-fuchsia-600 to-purple-600 px-8 py-6">
					<h1 class="text-3xl font-bold text-white text-center">Регистрация дилера</h1>
					<p class="text-fuchsia-100 text-center mt-2">Заполните форму для завершения регистрации</p>
				</div>

				<form @submit.prevent="submit" class="p-8 space-y-8">
					<!-- Personal Information -->
					<div class="space-y-4">
						<h2 class="text-xl font-semibold text-gray-900 dark:text-white border-b pb-2">Основная информация</h2>
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
								<Input v-model="form.phone" id="phone" type="tel" required placeholder="+7 (XXX) XXX XX-XX" />
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
						<h2 class="text-xl font-semibold text-gray-900 dark:text-white border-b pb-2">Адресная информация</h2>
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
								<Label for="address">Фактический адрес <span class="text-red-500">*</span></Label>
								<Textarea v-model="form.address" id="address" required placeholder="Введите полный адрес" rows="2" />
								<div v-if="form.errors.address" class="text-xs text-red-500 mt-1">{{ form.errors.address }}</div>
							</div>
						</div>
					</div>

					<!-- Company Information -->
					<div class="space-y-4">
						<h2 class="text-xl font-semibold text-gray-900 dark:text-white border-b pb-2">Информация о компании</h2>
						<div>
							<Label for="company">Контрагент <span class="text-red-500">*</span></Label>
							<Input v-model="form.company" id="company" type="text" required placeholder="Название компании" />
							<div v-if="form.errors.company" class="text-xs text-red-500 mt-1">{{ form.errors.company }}</div>
						</div>
					</div>

					<!-- Requisites (Optional) -->
					<div class="space-y-4">
						<div class="flex items-center justify-between border-b pb-2">
							<h2 class="text-xl font-semibold text-gray-900 dark:text-white">Реквизиты (необязательно)</h2>
							<Button type="button" variant="ghost" size="sm" @click="showRequisites = !showRequisites">
								{{ showRequisites ? "Скрыть" : "Показать" }}
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
								<Textarea v-model="form.legal_address" id="legal_address" placeholder="Полный юридический адрес" rows="2" />
							</div>
						</div>
					</div>

					<!-- Password -->
					<div class="space-y-4">
						<h2 class="text-xl font-semibold text-gray-900 dark:text-white border-b pb-2">Безопасность</h2>
						<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
							<div>
								<Label for="password">Пароль <span class="text-red-500">*</span></Label>
								<Input v-model="form.password" id="password" type="password" required placeholder="Минимум 8 символов" />
								<div v-if="form.errors.password" class="text-xs text-red-500 mt-1">{{ form.errors.password }}</div>
							</div>
							<div>
								<Label for="password_confirmation">Подтверждение пароля <span class="text-red-500">*</span></Label>
								<Input v-model="form.password_confirmation" id="password_confirmation" type="password" required placeholder="Повторите пароль" />
							</div>
						</div>
					</div>

					<!-- Submit Button -->
					<div class="pt-6 border-t">
						<Button type="submit" class="w-full bg-gradient-to-r from-fuchsia-600 to-purple-600 hover:from-fuchsia-700 hover:to-purple-700 text-white py-6 text-lg" :disabled="form.processing">
							<span v-if="form.processing">Регистрация...</span>
							<span v-else>Зарегистрироваться</span>
						</Button>
					</div>
				</form>
			</div>

			<!-- Footer -->
			<p class="text-center text-gray-600 dark:text-gray-400 mt-6 text-sm">
				Ссылка действительна до {{ new Date(expiresAt).toLocaleString("ru-RU") }}
			</p>
		</div>
	</div>
</template>

