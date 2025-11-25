<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { Head, Link } from "@inertiajs/vue3";
import GuestHeaderLayout from "../../Layouts/GuestHeaderLayout.vue";
import GuestFooter from "../../Layouts/GuestFooter.vue";
import LandingButton from "../../Components/LandingButton.vue";
import LandingBadge from "../../Components/LandingBadge.vue";
import ConsultationDialog from "../../Components/ConsultationDialog.vue";
import {
	MapPinIcon,
	PaletteIcon,
	RectangleVerticalIcon,
	CalendarIcon,
	ChevronLeftIcon,
	ChevronRightIcon,
	PhoneIcon,
	MailIcon,
	ArrowLeftIcon,
	ChevronUpIcon,
} from "lucide-vue-next";

const props = defineProps({
	portfolio: {
		type: Object,
		required: true,
	},
});

// Reactive state
const currentImageIndex = ref(0);
const isConsultationDialogOpen = ref(false);
const showBackToTop = ref(false);
const touchStartX = ref(0);
const touchEndX = ref(0);

// Format date helper
const formatDate = (dateString) => {
	if (!dateString) return "";
	try {
		const date = new Date(dateString);
		return date.toLocaleDateString("ru-RU", {
			year: "numeric",
			month: "long",
			day: "numeric",
		});
	} catch {
		return "";
	}
};

// Carousel functionality
const images = computed(() => {
	if (props.portfolio.images && props.portfolio.images.length > 0) {
		return props.portfolio.images.map((img) => `/storage/${img}`);
	}
	return ["/assets/hero.jpg"];
});

const nextImage = () => {
	currentImageIndex.value = (currentImageIndex.value + 1) % images.value.length;
};

const prevImage = () => {
	currentImageIndex.value = currentImageIndex.value === 0 ? images.value.length - 1 : currentImageIndex.value - 1;
};

const goToImage = (index) => {
	currentImageIndex.value = index;
};

// Touch/swipe handlers
const handleTouchStart = (event) => {
	touchStartX.value = event.touches[0].clientX;
};

const handleTouchEnd = (event) => {
	touchEndX.value = event.changedTouches[0].clientX;
	handleSwipe();
};

const handleSwipe = () => {
	const swipeThreshold = 50;
	const diff = touchStartX.value - touchEndX.value;

	if (Math.abs(diff) > swipeThreshold) {
		if (diff > 0) {
			nextImage();
		} else {
			prevImage();
		}
	}
};

// Keyboard navigation
const handleKeydown = (event) => {
	switch (event.key) {
		case "ArrowLeft":
			event.preventDefault();
			prevImage();
			break;
		case "ArrowRight":
			event.preventDefault();
			nextImage();
			break;
	}
};

// Open consultation dialog
const openConsultationDialog = () => {
	isConsultationDialogOpen.value = true;
};

// Back to top functionality
const scrollToTop = () => {
	window.scrollTo({
		top: 0,
		behavior: "smooth",
	});
};

// Handle scroll events
const handleScroll = () => {
	showBackToTop.value = window.scrollY > 500;
};

// Structured data for SEO
const structuredData = computed(() => ({
	"@context": "https://schema.org",
	"@type": "CreativeWork",
	"@id": `https://llymar.ru/portfolio/${props.portfolio.id}`,
	name: props.portfolio.title || "Проект безрамного остекления",
	description: props.portfolio.description || "Проект безрамного остекления от LLYMAR",
	image: images.value[0],
	creator: {
		"@type": "Organization",
		"@id": "https://llymar.ru/#organization",
		name: "LLYMAR",
	},
	dateCreated: props.portfolio.created_at,
	locationCreated: {
		"@type": "Place",
		name: props.portfolio.location || "Краснодар",
	},
	about: {
		"@type": "Service",
		name: "Безрамное остекление",
		serviceType: "Остекление",
	},
	workExample: {
		"@type": "Product",
		name: props.portfolio.title || "Проект безрамного остекления",
		description: props.portfolio.description,
		image: images.value,
		offers: {
			"@type": "Offer",
			availability: "https://schema.org/InStock",
			priceCurrency: "RUB",
		},
	},
}));

// Lifecycle hooks
onMounted(() => {
	document.addEventListener("keydown", handleKeydown);
	window.addEventListener("scroll", handleScroll);

	// Add structured data
	const structuredDataScript = document.createElement("script");
	structuredDataScript.type = "application/ld+json";
	structuredDataScript.textContent = JSON.stringify(structuredData.value);
	document.head.appendChild(structuredDataScript);
});

onUnmounted(() => {
	document.removeEventListener("keydown", handleKeydown);
	window.removeEventListener("scroll", handleScroll);

	// Remove structured data
	const structuredDataScripts = document.querySelectorAll('script[type="application/ld+json"]');
	structuredDataScripts.forEach((script) => {
		if (script.textContent.includes(`/portfolio/${props.portfolio.id}`)) {
			script.remove();
		}
	});
});
</script>

<template>
	<Head>
		<title>{{ portfolio.title || 'Проект' }} - Портфолио LLYMAR</title>
		<meta name="description" :content="portfolio.description || 'Проект безрамного остекления от компании LLYMAR в Краснодаре'" />
		<meta name="keywords" :content="`безрамное остекление, ${portfolio.location || 'Краснодар'}, ${portfolio.glass || 'стекло'}, ${portfolio.color || 'профиль'}, портфолио, примеры работ`" />

		<!-- Open Graph -->
		<meta property="og:type" content="article" />
		<meta property="og:url" :content="`https://llymar.ru/portfolio/${portfolio.id}`" />
		<meta property="og:title" :content="`${portfolio.title || 'Проект'} - Портфолио LLYMAR`" />
		<meta property="og:description" :content="portfolio.description || 'Проект безрамного остекления от компании LLYMAR'" />
		<meta property="og:image" :content="images[0]" />
		<meta property="og:locale" content="ru_RU" />

		<!-- Twitter -->
		<meta property="twitter:card" content="summary_large_image" />
		<meta property="twitter:url" :content="`https://llymar.ru/portfolio/${portfolio.id}`" />
		<meta property="twitter:title" :content="`${portfolio.title || 'Проект'} - Портфолио LLYMAR`" />
		<meta property="twitter:description" :content="portfolio.description || 'Проект безрамного остекления от компании LLYMAR'" />
		<meta property="twitter:image" :content="images[0]" />

		<!-- Additional SEO -->
		<meta name="robots" content="index, follow" />
		<meta name="author" content="LLYMAR" />
		<link rel="canonical" :href="`https://llymar.ru/portfolio/${portfolio.id}`" />
	</Head>

	<div class="min-h-screen bg-gray-50">
		<GuestHeaderLayout theme="light" :openConsultationDialog="openConsultationDialog" />

		<!-- Breadcrumbs -->
		<div class="bg-white border-b border-gray-200">
			<div class="container max-w-screen-2xl px-4 py-4">
				<nav class="flex items-center gap-2 text-sm montserrat">
					<Link href="/" class="text-gray-500 hover:text-dark-green transition-colors">Главная</Link>
					<span class="text-gray-400">/</span>
					<Link href="/portfolio" class="text-gray-500 hover:text-dark-green transition-colors">Портфолио</Link>
					<span class="text-gray-400">/</span>
					<span class="text-dark-green font-medium">{{ portfolio.title || 'Проект' }}</span>
				</nav>
			</div>
		</div>

		<!-- Main Content -->
		<div class="container max-w-screen-2xl px-4 py-8 md:py-16">
			<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 md:gap-16">
				<!-- Image Gallery -->
				<div class="space-y-4">
					<!-- Main Image -->
					<div class="relative bg-gray-900 rounded-2xl overflow-hidden aspect-[4/3]" @touchstart="handleTouchStart" @touchend="handleTouchEnd">
						<img :src="images[currentImageIndex]" :alt="portfolio.title || 'Проект'" class="w-full h-full object-cover select-none" draggable="false" />

						<!-- Image Counter -->
						<div class="absolute top-4 left-4 bg-black/60 text-white px-3 py-1 rounded-full text-sm montserrat">{{ currentImageIndex + 1 }} / {{ images.length }}</div>

						<!-- Navigation Arrows (only show if more than 1 image) -->
						<template v-if="images.length > 1">
							<button @click="prevImage" class="absolute left-4 top-1/2 -translate-y-1/2 bg-black/50 hover:bg-black/70 text-white rounded-full p-3 transition-all hover:scale-110">
								<ChevronLeftIcon class="w-6 h-6" />
							</button>
							<button @click="nextImage" class="absolute right-4 top-1/2 -translate-y-1/2 bg-black/50 hover:bg-black/70 text-white rounded-full p-3 transition-all hover:scale-110">
								<ChevronRightIcon class="w-6 h-6" />
							</button>
						</template>
					</div>

					<!-- Thumbnail Navigation (only show if more than 1 image) -->
					<div v-if="images.length > 1" class="grid grid-cols-4 gap-2">
						<button
							v-for="(image, index) in images"
							:key="index"
							@click="goToImage(index)"
							class="aspect-square rounded-lg overflow-hidden border-2 transition-all hover:scale-105"
							:class="currentImageIndex === index ? 'border-light-gold' : 'border-gray-300 hover:border-gray-400'"
						>
							<img :src="image" :alt="`Изображение ${index + 1}`" class="w-full h-full object-cover" />
						</button>
					</div>
				</div>

				<!-- Project Details -->
				<div class="space-y-8">
					<!-- Header -->
					<div>
						<LandingBadge variant="dark" size="sm" class="mb-4">Портфолио</LandingBadge>
						<h1 class="text-3xl md:text-4xl lg:text-5xl font-light text-dark-green mb-4">{{ portfolio.title || 'Проект безрамного остекления' }}</h1>
						<div class="flex flex-wrap items-center gap-3 text-gray-600 montserrat">
							<div class="flex items-center gap-2">
								<MapPinIcon class="w-5 h-5 text-light-gold" />
								<span>{{ portfolio.location || 'Не указано' }}</span>
							</div>
							<span class="text-gray-400">•</span>
							<div class="flex items-center gap-2">
								<CalendarIcon class="w-5 h-5 text-light-gold" />
								<span>{{ portfolio.year || new Date().getFullYear() }}</span>
							</div>
						</div>
						<div v-if="portfolio.created_at" class="text-gray-500 text-sm mt-2 montserrat">Опубликовано: {{ formatDate(portfolio.created_at) }}</div>
					</div>

					<!-- Area Info -->
					<div class="bg-light-gold/10 border-2 border-light-gold rounded-2xl p-6 text-center">
						<div class="text-4xl md:text-5xl font-bold text-dark-green montserrat">{{ portfolio.area ? `${portfolio.area} м²` : 'Не указано' }}</div>
						<div class="text-gray-600 mt-2 montserrat">Площадь остекления</div>
					</div>

					<!-- Description -->
					<div v-if="portfolio.description" class="prose prose-lg max-w-none">
						<h2 class="text-2xl font-light text-dark-green mb-4">Описание проекта</h2>
						<p class="text-gray-700 leading-relaxed text-justify">{{ portfolio.description }}</p>
					</div>

					<!-- Technical Details -->
					<div>
						<h2 class="text-2xl font-light text-dark-green mb-4">Технические характеристики</h2>
						<div class="space-y-4">
							<div class="flex items-center gap-4 p-4 bg-white rounded-lg border border-gray-200">
								<RectangleVerticalIcon class="text-light-gold w-6 h-6 flex-shrink-0" />
								<div>
									<div class="text-sm text-gray-500 montserrat">Тип стекла</div>
									<div class="font-medium text-dark-green montserrat">{{ portfolio.glass || 'Не указано' }}</div>
								</div>
							</div>
							<div class="flex items-center gap-4 p-4 bg-white rounded-lg border border-gray-200">
								<PaletteIcon class="text-light-gold w-6 h-6 flex-shrink-0" />
								<div>
									<div class="text-sm text-gray-500 montserrat">Цвет профиля</div>
									<div class="font-medium text-dark-green montserrat">{{ portfolio.color || 'Не указано' }}</div>
								</div>
							</div>
						</div>
					</div>

					<!-- CTA Buttons -->
					<div class="space-y-4 pt-4">
						<div class="flex flex-col sm:flex-row gap-4">
							<LandingButton variant="dark" size="icon" iconPosition="left" :icon="PhoneIcon" @click="openConsultationDialog" class="flex-1"> Заказать похожий проект </LandingButton>
							<LandingButton variant="outline" size="icon" iconPosition="left" :icon="MailIcon" @click="openConsultationDialog" class="flex-1"> Получить консультацию </LandingButton>
						</div>
						<Link href="/portfolio" class="inline-flex items-center justify-center gap-2 text-dark-green hover:text-light-gold transition-colors w-full py-3">
							<ArrowLeftIcon class="w-5 h-5" />
							<span class="montserrat font-medium">Вернуться к портфолио</span>
						</Link>
					</div>
				</div>
			</div>
		</div>

		<!-- Related Projects / CTA Section -->
		<section class="bg-dark-green text-white py-16 md:py-32">
			<div class="container max-w-screen-2xl px-4 text-center">
				<h2 class="text-3xl md:text-4xl font-light mb-6">Хотите реализовать похожий проект?</h2>
				<p class="text-gray-300 mb-8 max-w-2xl mx-auto">Закажите бесплатный расчет с чертежами, спецификацией и стоимостью. Наш специалист приедет к вам в удобное время.</p>
				<div class="flex flex-col sm:flex-row gap-4 justify-center">
					<LandingButton @click="openConsultationDialog" size="lg">Бесплатный расчет</LandingButton>
					<Link href="/portfolio">
						<LandingButton variant="secondary" size="lg">Смотреть все работы</LandingButton>
					</Link>
				</div>
			</div>
		</section>

		<GuestFooter />
	</div>

	<!-- Consultation Dialog -->
	<ConsultationDialog v-model:isOpen="isConsultationDialogOpen" />

	<!-- Back to Top Button -->
	<Transition
		enter-active-class="transition-all duration-300 ease-out"
		leave-active-class="transition-all duration-300 ease-in"
		enter-from-class="opacity-0 translate-y-2 scale-90"
		enter-to-class="opacity-100 translate-y-0 scale-100"
		leave-from-class="opacity-100 translate-y-0 scale-100"
		leave-to-class="opacity-0 translate-y-2 scale-90"
	>
		<div v-if="showBackToTop" class="fixed bottom-6 right-6 z-40">
			<button @click="scrollToTop" class="bg-dark-green hover:bg-dark-green/90 text-light-gold p-3 rounded-full border-2 border-light-gold transition-all duration-300 hover:scale-110" aria-label="Вернуться к началу">
				<ChevronUpIcon class="w-6 h-6" />
			</button>
		</div>
	</Transition>
</template>

<style scoped>
/* Focus styles for accessibility */
button:focus-visible,
a:focus-visible {
	outline: 2px solid var(--light-gold);
	outline-offset: 2px;
}
</style>

