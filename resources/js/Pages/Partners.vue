<script setup>
import { ref, reactive, onMounted, onUnmounted } from "vue";
import { Head, Link } from "@inertiajs/vue3";
import GuestHeaderLayout from "@/Layouts/GuestHeaderLayout.vue";
import GuestFooter from "@/Layouts/GuestFooter.vue";
import ConsultationDialog from "@/Components/ConsultationDialog.vue";
import LandingBadge from "@/Components/LandingBadge.vue";
import LandingButton from "@/Components/LandingButton.vue";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { Button } from "@/Components/ui/button";
import {
	PhoneIcon,
	ChevronUpIcon,
	MailIcon,
	Building2Icon,
	HardHatIcon,
	PencilRulerIcon,
	CheckCircleIcon,
} from "lucide-vue-next";
import { vMaska } from "maska/vue";

const PARTNERS_PHONE = "+79528695336";
const PARTNERS_PHONE_FORMATTED = "+7 (952) 869-53-36";
const PAGE_SOURCE = "Страница «Специалистам»";

const partnerTypes = [
	{
		icon: PencilRulerIcon,
		title: "Архитекторов",
		description: "Проектная поддержка, чертежи и спецификации под ваши объекты.",
	},
	{
		icon: Building2Icon,
		title: "Строительные компании",
		description: "Поставка системы, профиля и фурнитуры для безрамного остекления.",
	},
	{
		icon: HardHatIcon,
		title: "Монтажные организации",
		description: "Обучение монтажу, техподдержка и выгодные условия для установщиков.",
	},
];

const cooperationBenefits = [
	"Производитель безрамного и панорамного остекления",
	"Система для безрамного раздвижного остекления",
	"Профиль и фурнитура для безрамного остекления",
	"Гибкие условия и поддержка на всех этапах",
];

const isConsultationDialogOpen = ref(false);
const showBackToTop = ref(false);
const mailButtonPulse = ref(false);

const form = reactive({
	name: "",
	phone: "",
	city: "",
	privacy: false,
});

const isSubmitting = ref(false);
const errors = ref({});
const submitError = ref("");
const submitSuccess = ref(false);

const openConsultationDialog = () => {
	isConsultationDialogOpen.value = true;
};

const scrollToTop = () => {
	window.scrollTo({ top: 0, behavior: "smooth" });
};

const scrollToForm = () => {
	const el = document.getElementById("partners-form");
	if (el) {
		const offset = el.getBoundingClientRect().top + window.pageYOffset - 80;
		window.scrollTo({ top: offset, behavior: "smooth" });
	}
};

const handleScroll = () => {
	showBackToTop.value = window.scrollY > 500;
};

onMounted(() => {
	window.addEventListener("scroll", handleScroll, { passive: true });
});

onUnmounted(() => {
	window.removeEventListener("scroll", handleScroll);
});

const validateForm = () => {
	const newErrors = {};

	if (!form.name.trim()) {
		newErrors.name = "Имя обязательно для заполнения";
	}

	if (!form.phone.trim()) {
		newErrors.phone = "Телефон обязателен для заполнения";
	} else if (!/^[\+]?[0-9\s\(\)\-]{10,}$/.test(form.phone.trim())) {
		newErrors.phone = "Введите корректный номер телефона";
	}

	if (!form.privacy) {
		newErrors.privacy = "Необходимо согласие с политикой конфиденциальности";
	}

	errors.value = newErrors;
	return Object.keys(newErrors).length === 0;
};

const resetFormFields = () => {
	form.name = "";
	form.phone = "";
	form.city = "";
	form.privacy = false;
	errors.value = {};
	submitError.value = "";
};

const submitForm = async () => {
	if (!validateForm()) {
		return;
	}

	isSubmitting.value = true;
	submitError.value = "";
	submitSuccess.value = false;

	try {
		const response = await fetch("/api/consultation-request", {
			method: "POST",
			headers: {
				"Content-Type": "application/json",
				Accept: "application/json",
				"X-Requested-With": "XMLHttpRequest",
				"X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "",
			},
			body: JSON.stringify({
				name: form.name.trim(),
				phone: form.phone.trim(),
				city: form.city.trim() || "не указан",
				source: PAGE_SOURCE,
			}),
		});

		const data = await response.json();

		if (response.ok && data.success) {
			submitSuccess.value = true;
			resetFormFields();
		} else {
			submitError.value = data.message || "Произошла ошибка при отправке заявки. Попробуйте еще раз.";
		}
	} catch {
		submitError.value = "Произошла ошибка при отправке. Проверьте подключение к интернету.";
	} finally {
		isSubmitting.value = false;
	}
};

const seoKeywords =
	"безрамное остекление, остекление, производитель безрамного остекления, производитель панорамного остекления, панорамное остекление, безрамное раздвижное остекление, раздвижное остекление, система для безрамного остекления, фурнитура для безрамного остекления, профиль для безрамного остекления";

const metaDescription =
	"Производитель безрамного и панорамного остекления LLYMAR приглашает к сотрудничеству архитекторов, строительные и монтажные организации. Система, профиль и фурнитура для безрамного раздвижного остекления.";
</script>

<template>
	<Head>
		<title>Специалистам — производитель безрамного остекления LLYMAR</title>
		<meta name="description" :content="metaDescription" />
		<meta name="keywords" :content="seoKeywords" />
		<meta property="og:type" content="website" />
		<meta property="og:url" content="https://llymar.ru/partners" />
		<meta property="og:title" content="Специалистам — производитель безрамного остекления LLYMAR" />
		<meta property="og:description" :content="metaDescription" />
		<meta property="og:image" content="/assets/hero.jpg" />
		<meta property="og:locale" content="ru_RU" />
		<meta property="twitter:card" content="summary_large_image" />
		<meta property="twitter:url" content="https://llymar.ru/partners" />
		<meta property="twitter:title" content="Специалистам — производитель безрамного остекления LLYMAR" />
		<meta property="twitter:description" :content="metaDescription" />
		<meta property="twitter:image" content="/assets/hero.jpg" />
		<meta name="robots" content="index, follow" />
		<meta name="author" content="LLYMAR" />
		<link rel="canonical" href="https://llymar.ru/partners" />
	</Head>

	<!-- Hero (Welcome-style) -->
	<section id="hero" class="text-white relative min-h-screen overflow-hidden">
		<div class="absolute inset-0 bg-[url('/assets/hero.jpg')] bg-cover bg-center"></div>
		<div class="absolute inset-0 bg-gradient-to-br from-[#23322D]/90 via-[#23322D]/80 to-[#23322D]/70"></div>

		<div class="relative z-20 min-h-screen flex flex-col">
			<GuestHeaderLayout theme="transparent" :openConsultationDialog="openConsultationDialog" />

			<div class="container max-w-screen-2xl px-2 md:px-4 flex-1 flex flex-col justify-center">
				<div class="flex flex-col gap-6 md:gap-8 py-12 md:py-0">
					<div class="space-y-4 max-w-4xl">
						<LandingBadge variant="gold" size="sm">Специалистам</LandingBadge>
						<h1 class="text-3xl sm:text-4xl md:text-5xl uppercase font-light leading-tight">
							Производитель системы
							<span class="text-light-gold">безрамного остекления</span>
							LLYMAR предлагает гибкие условия сотрудничества
						</h1>
					</div>

					<div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4 md:gap-8 mt-2">
						<LandingButton @click="scrollToForm"> Оставить заявку </LandingButton>
						<LandingButton
							variant="secondary"
							:icon="PhoneIcon"
							:href="`tel:${PARTNERS_PHONE}`"
							iconPosition="left"
						>
							{{ PARTNERS_PHONE_FORMATTED }}
						</LandingButton>
					</div>

					<div class="text-right hidden md:block">
						<button
							type="button"
							class="inline-block text-white/70 hover:text-light-gold transition-colors animate-bounce cursor-pointer mt-8 md:mt-12"
							@click="scrollToForm"
							aria-label="Перейти к форме"
						>
							<img src="/assets/scrolldown.svg" alt="" />
						</button>
					</div>
				</div>
			</div>
		</div>
	</section>

	<div class="min-h-screen bg-gray-50 montserrat">
		<!-- Partner types -->
		<section class="py-16 md:py-24">
			<div class="container max-w-screen-2xl px-4">
				<div class="max-w-3xl mx-auto text-center space-y-4 mb-12 md:mb-16">
					<LandingBadge variant="dark" size="sm">Сотрудничество</LandingBadge>
					<h2 class="text-3xl md:text-4xl font-light text-dark-green">Мы приглашаем к сотрудничеству надёжных специалистов</h2>
					<p class="text-gray-600 text-lg">
						Звоните по номеру
						<a :href="`tel:${PARTNERS_PHONE}`" class="text-dark-green font-medium hover:text-light-gold transition-colors whitespace-nowrap">
							{{ PARTNERS_PHONE_FORMATTED }}
						</a>
						или заполните форму ниже — мы свяжемся с вами.
					</p>
				</div>

				<div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8">
					<div
						v-for="type in partnerTypes"
						:key="type.title"
						class="text-center flex flex-col gap-4 items-center bg-white p-6 md:p-8 rounded-2xl shadow-sm border border-gray-100 hover:shadow-lg hover:border-light-gold/30 transition-all duration-300"
					>
						<div class="flex items-center justify-center bg-light-gold rounded-full p-4">
							<component :is="type.icon" class="w-7 h-7 text-dark-green" />
						</div>
						<h3 class="text-lg font-semibold text-dark-green">{{ type.title }}</h3>
						<p class="text-base text-gray-600">{{ type.description }}</p>
					</div>
				</div>
			</div>
		</section>

		<!-- What we offer -->
		<section class="bg-dark-green text-white py-16 md:py-24">
			<div class="container max-w-screen-2xl px-4">
				<div class="grid grid-cols-1 lg:grid-cols-2 gap-10 md:gap-16 items-center">
					<div class="space-y-6">
						<LandingBadge variant="gold" size="sm">Преимущества</LandingBadge>
						<h2 class="text-3xl md:text-4xl font-light">Почему выбирают LLYMAR как производителя</h2>
						<p class="text-gray-300 text-justify leading-relaxed">
							LLYMAR — производитель безрамного остекления и панорамного остекления. Мы разрабатываем и поставляем
							систему для безрамного остекления, профиль для безрамного остекления и фурнитуру для безрамного
							остекления. Безрамное раздвижное остекление и раздвижное остекление — наша специализация.
						</p>
					</div>
					<ul class="space-y-4">
						<li v-for="benefit in cooperationBenefits" :key="benefit" class="flex items-start gap-3">
							<CheckCircleIcon class="w-6 h-6 text-light-gold flex-shrink-0 mt-0.5" />
							<span class="text-gray-200">{{ benefit }}</span>
						</li>
					</ul>
				</div>
			</div>
		</section>

		<!-- Form -->
		<section id="partners-form" class="py-16 md:py-32">
			<div class="container max-w-screen-2xl px-4">
				<div class="max-w-xl mx-auto">
					<div class="text-center space-y-4 mb-10">
						<LandingBadge variant="dark" size="sm">Заявка</LandingBadge>
						<h2 class="text-3xl md:text-4xl font-light text-dark-green">Свяжитесь с нами</h2>
						<p class="text-gray-600">Заполните форму — менеджер перезвонит и обсудит условия сотрудничества</p>
					</div>

					<div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 md:p-10">
						<form class="space-y-5" @submit.prevent="submitForm" novalidate>
							<div class="grid gap-2">
								<Label for="partners-name">Имя *</Label>
								<Input
									id="partners-name"
									v-model="form.name"
									type="text"
									placeholder="Ваше имя"
									autocomplete="name"
									:disabled="isSubmitting"
									class="h-11"
								/>
								<p v-if="errors.name" class="text-xs text-red-500">{{ errors.name }}</p>
							</div>

							<div class="grid gap-2">
								<Label for="partners-phone">Телефон *</Label>
								<Input
									id="partners-phone"
									v-model="form.phone"
									type="tel"
									placeholder="+7 (999) 123-45-67"
									v-maska="'+7 (###) ###-##-##'"
									autocomplete="tel"
									:disabled="isSubmitting"
									class="h-11"
								/>
								<p v-if="errors.phone" class="text-xs text-red-500">{{ errors.phone }}</p>
							</div>

							<div class="grid gap-2">
								<Label for="partners-city">Город</Label>
								<Input
									id="partners-city"
									v-model="form.city"
									type="text"
									placeholder="Ваш город"
									autocomplete="address-level2"
									:disabled="isSubmitting"
									class="h-11"
								/>
							</div>

							<div class="flex items-start space-x-2 pt-1">
								<input
									id="partners-privacy"
									v-model="form.privacy"
									type="checkbox"
									:disabled="isSubmitting"
									class="h-4 w-4 mt-1 text-dark-green focus:ring-light-gold border-gray-300 rounded"
								/>
								<Label for="partners-privacy" class="text-sm text-gray-600 font-normal leading-snug">
									Я согласен с
									<a href="#" class="text-dark-green hover:underline">политикой конфиденциальности</a> *
								</Label>
							</div>
							<p v-if="errors.privacy" class="text-xs text-red-500">{{ errors.privacy }}</p>

							<p v-if="submitError" class="text-sm text-red-500 bg-red-50 p-3 rounded-md">{{ submitError }}</p>
							<p v-if="submitSuccess" class="text-sm text-green-700 bg-green-50 p-3 rounded-md">
								Спасибо! Заявка отправлена. Мы свяжемся с вами в ближайшее время.
							</p>

							<Button
								type="submit"
								class="w-full h-12 bg-dark-green hover:bg-dark-green/90 text-light-gold rounded-full text-base font-medium"
								:disabled="isSubmitting"
							>
								<span v-if="isSubmitting" class="flex items-center justify-center gap-2">
									<span class="w-4 h-4 border-2 border-light-gold border-t-transparent rounded-full animate-spin"></span>
									Отправка...
								</span>
								<span v-else>Отправить заявку</span>
							</Button>
						</form>
					</div>
				</div>
			</div>
		</section>

		<!-- CTA -->
		<!-- <section class="bg-light-gold text-dark-green py-16 md:py-24">
			<div class="container max-w-screen-2xl px-4 text-center">
				<div class="max-w-3xl mx-auto space-y-6">
					<h2 class="text-3xl md:text-4xl font-light">Нужна консультация по системе?</h2>
					<p class="text-lg">Наш специалист ответит на вопросы по монтажу, комплектации и условиям поставки.</p>
					<div class="flex flex-col sm:flex-row gap-4 justify-center pt-2">
						<LandingButton variant="dark" size="lg" showArrow @click="openConsultationDialog">
							Получить консультацию
						</LandingButton>
						<Link href="/">
							<LandingButton variant="outline" size="lg">На главную</LandingButton>
						</Link>
					</div>
				</div>
			</div>
		</section> -->

		<GuestFooter />
	</div>

	<ConsultationDialog v-model:isOpen="isConsultationDialogOpen" />

	<!-- Floating buttons (Welcome-style) -->
	<Transition
		enter-active-class="transition-all duration-300 ease-out"
		leave-active-class="transition-all duration-300 ease-in"
		enter-from-class="opacity-0 translate-y-2 scale-90"
		enter-to-class="opacity-100 translate-y-0 scale-100"
		leave-from-class="opacity-100 translate-y-0 scale-100"
		leave-to-class="opacity-0 translate-y-2 scale-90"
	>
		<div v-if="showBackToTop" class="fixed bottom-6 right-6 z-40 flex flex-col gap-3">
			<button
				type="button"
				@click="openConsultationDialog"
				class="bg-light-gold hover:bg-light-gold/90 text-dark-green p-3 rounded-full border-2 border-light-gold transition-all duration-300 hover:scale-110"
				:class="{ 'mail-button-pulse': mailButtonPulse }"
				aria-label="Получить консультацию"
			>
				<MailIcon class="w-6 h-6" />
			</button>
			<button
				type="button"
				@click="scrollToTop"
				class="bg-dark-green hover:bg-dark-green/90 text-light-gold p-3 rounded-full border-2 border-light-gold transition-all duration-300 hover:scale-110"
				aria-label="Вернуться к началу"
			>
				<ChevronUpIcon class="w-6 h-6" />
			</button>
		</div>
	</Transition>
</template>

<style scoped>
button:focus-visible,
a:focus-visible {
	outline: 2px solid var(--light-gold);
	outline-offset: 2px;
}

@keyframes mailPulse {
	0% {
		transform: scale(1);
		box-shadow: 0 0 0 0 rgba(231, 200, 134, 0.7);
	}
	50% {
		transform: scale(1.1);
		box-shadow: 0 0 0 10px rgba(231, 200, 134, 0.3);
	}
	100% {
		transform: scale(1);
		box-shadow: 0 0 0 20px rgba(231, 200, 134, 0);
	}
}

.mail-button-pulse {
	animation: mailPulse 1s ease-in-out;
}
</style>
