<script setup>
import { ref, onMounted, onUnmounted, computed } from "vue";
import { Head, Link } from "@inertiajs/vue3";
import GuestHeaderLayout from "../../Layouts/GuestHeaderLayout.vue";
import GuestFooter from "../../Layouts/GuestFooter.vue";
import LandingBadge from "../../Components/LandingBadge.vue";
import LandingButton from "../../Components/LandingButton.vue";
import ConsultationDialog from "../../Components/ConsultationDialog.vue";
import { MapPinIcon, PaletteIcon, RectangleVerticalIcon, CalendarIcon, ChevronUpIcon } from "lucide-vue-next";

// Portfolio data from API
const portfolio = ref([]);
const isLoadingPortfolio = ref(true);
const isConsultationDialogOpen = ref(false);
const showBackToTop = ref(false);

// Fetch portfolio data from API
const fetchPortfolio = async () => {
	try {
		const response = await fetch("/api/portfolio", {
			method: "GET",
			headers: {
				Accept: "application/json",
				"Content-Type": "application/json",
			},
			credentials: "same-origin",
		});

		if (response.ok) {
			const data = await response.json();
			if (data.success && data.data) {
				portfolio.value = data.data.map((item) => ({
					id: item.id,
					image: item.images && item.images.length > 0 ? `/storage/${item.images[0]}` : "/assets/hero.jpg",
					images: item.images && item.images.length > 0 ? item.images.map((img) => `/storage/${img}`) : ["/assets/hero.jpg"],
					location: item.location || "Не указано",
					glass: item.glass || "Не указано",
					profile: item.color || "Не указано",
					year: item.year || new Date().getFullYear(),
					area: item.area ? `${item.area} м²` : "Не указано",
					type: item.title || "Проект",
					description: item.description || "",
					created_at: item.created_at,
				}));
			}
		} else {
			console.error("Failed to fetch portfolio data");
		}
	} catch (error) {
		console.error("Error fetching portfolio:", error);
	} finally {
		isLoadingPortfolio.value = false;
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
	"@type": "CollectionPage",
	"@id": "https://llymar.ru/portfolio",
	name: "Портфолио - LLYMAR",
	description: "Примеры наших работ по безрамному остеклению террас, веранд, беседок, кафе и ресторанов",
	url: "https://llymar.ru/portfolio",
	breadcrumb: {
		"@type": "BreadcrumbList",
		itemListElement: [
			{
				"@type": "ListItem",
				position: 1,
				name: "Главная",
				item: "https://llymar.ru",
			},
			{
				"@type": "ListItem",
				position: 2,
				name: "Портфолио",
				item: "https://llymar.ru/portfolio",
			},
		],
	},
	publisher: {
		"@id": "https://llymar.ru/#organization",
	},
	mainEntity: {
		"@type": "ItemList",
		numberOfItems: portfolio.value.length,
		itemListElement: portfolio.value.map((item, index) => ({
			"@type": "ListItem",
			position: index + 1,
			item: {
				"@type": "CreativeWork",
				"@id": `https://llymar.ru/portfolio/${item.id}`,
				name: item.type,
				image: item.image,
				description: item.description,
				url: `https://llymar.ru/portfolio/${item.id}`,
			},
		})),
	},
}));

// Lifecycle hooks
onMounted(() => {
	fetchPortfolio();
	window.addEventListener("scroll", handleScroll);

	// Add structured data
	const structuredDataScript = document.createElement("script");
	structuredDataScript.type = "application/ld+json";
	structuredDataScript.id = "portfolio-structured-data";
	structuredDataScript.textContent = JSON.stringify(structuredData.value);
	document.head.appendChild(structuredDataScript);
});

onUnmounted(() => {
	window.removeEventListener("scroll", handleScroll);

	// Remove structured data
	const script = document.getElementById("portfolio-structured-data");
	if (script) {
		script.remove();
	}
});
</script>

<template>
	<Head>
		<title>Портфолио - Примеры наших работ | LLYMAR</title>
		<meta
			name="description"
			content="Портфолио компании LLYMAR: примеры наших работ по безрамному остеклению террас, веранд, беседок, кафе и ресторанов в Краснодаре. Фото выполненных проектов с описанием."
		/>
		<meta name="keywords" content="портфолио LLYMAR, примеры работ, безрамное остекление, фото проектов, реализованные проекты, остекление Краснодар" />

		<!-- Open Graph -->
		<meta property="og:type" content="website" />
		<meta property="og:url" content="https://llymar.ru/portfolio" />
		<meta property="og:title" content="Портфолио - Примеры наших работ | LLYMAR" />
		<meta property="og:description" content="Портфолио компании LLYMAR: примеры наших работ по безрамному остеклению. Фото выполненных проектов с описанием." />
		<meta property="og:image" content="/assets/hero.jpg" />
		<meta property="og:locale" content="ru_RU" />

		<!-- Twitter -->
		<meta property="twitter:card" content="summary_large_image" />
		<meta property="twitter:url" content="https://llymar.ru/portfolio" />
		<meta property="twitter:title" content="Портфолио - Примеры наших работ | LLYMAR" />
		<meta property="twitter:description" content="Портфолио компании LLYMAR: примеры наших работ по безрамному остеклению. Фото выполненных проектов с описанием." />
		<meta property="twitter:image" content="/assets/hero.jpg" />

		<!-- Additional SEO -->
		<meta name="robots" content="index, follow" />
		<meta name="author" content="LLYMAR" />
		<link rel="canonical" href="https://llymar.ru/portfolio" />
	</Head>

	<div class="min-h-screen bg-gray-50">
		<GuestHeaderLayout theme="light" :openConsultationDialog="openConsultationDialog" />

		<!-- Hero Section -->
		<section class="bg-dark-green text-white py-16 md:py-24">
			<div class="container max-w-screen-2xl px-4">
				<div class="max-w-4xl mx-auto text-center space-y-6">
					<LandingBadge size="sm">Наши работы</LandingBadge>
					<h1 class="text-3xl md:text-4xl lg:text-5xl font-light">Портфолио реализованных проектов</h1>
					<p class="text-gray-300 text-lg max-w-2xl mx-auto">Каждый проект — это уникальное решение, созданное с учетом пожеланий клиента и особенностей объекта. Посмотрите примеры наших работ.</p>
				</div>
			</div>
		</section>

		<!-- Breadcrumbs -->
		<div class="bg-white border-b border-gray-200">
			<div class="container max-w-screen-2xl px-4 py-4">
				<nav class="flex items-center gap-2 text-sm montserrat">
					<Link href="/" class="text-gray-500 hover:text-dark-green transition-colors">Главная</Link>
					<span class="text-gray-400">/</span>
					<span class="text-dark-green font-medium">Портфолио</span>
				</nav>
			</div>
		</div>

		<!-- Portfolio Grid -->
		<section class="py-16 md:py-24">
			<div class="container max-w-screen-2xl px-4">
				<!-- Loading State -->
				<div v-if="isLoadingPortfolio" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
					<div v-for="n in 8" :key="n" class="animate-pulse">
						<div class="aspect-square bg-gray-300 rounded-2xl mb-4"></div>
						<div class="space-y-2">
							<div class="h-4 bg-gray-300 rounded w-3/4"></div>
							<div class="h-3 bg-gray-300 rounded w-1/2"></div>
							<div class="h-3 bg-gray-300 rounded w-2/3"></div>
						</div>
					</div>
				</div>

				<!-- Portfolio Items -->
				<div v-else-if="portfolio.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
					<Link
						v-for="item in portfolio"
						:key="item.id"
						:href="`/portfolio/${item.id}`"
						class="group cursor-pointer transition-all duration-300 hover:-translate-y-2"
					>
						<div class="relative overflow-hidden rounded-2xl bg-white shadow-lg hover:shadow-2xl transition-all duration-300">
							<div class="aspect-square bg-cover bg-center transition-transform duration-500 group-hover:scale-110" :style="`background-image: url(${item.image})`"></div>
							<div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
								<div class="absolute bottom-4 left-4 right-4">
									<div class="text-light-gold text-sm font-medium montserrat">{{ item.type }}</div>
									<div class="text-white text-lg font-semibold montserrat">{{ item.area }}</div>
								</div>
							</div>
						</div>

						<div class="flex flex-col gap-3 mt-4 montserrat">
							<h3 class="text-lg font-semibold text-dark-green group-hover:text-light-gold transition-colors">{{ item.type }}</h3>
							<div class="flex flex-row gap-2 items-center text-sm text-gray-600">
								<MapPinIcon class="text-light-gold w-4 h-4" />
								<span>{{ item.location }}</span>
								<span class="text-gray-400">• {{ item.year }}</span>
							</div>
							<div class="flex flex-row gap-2 items-center text-sm text-gray-600">
								<RectangleVerticalIcon class="text-light-gold w-4 h-4" />
								<span>{{ item.glass }}</span>
							</div>
							<div class="flex flex-row gap-2 items-center text-sm text-gray-600">
								<PaletteIcon class="text-light-gold w-4 h-4" />
								<span>{{ item.profile }}</span>
							</div>
						</div>
					</Link>
				</div>

				<!-- Empty State -->
				<div v-else class="text-center py-16">
					<div class="text-gray-600 text-xl mb-4">Портфолио временно недоступно</div>
					<p class="text-gray-500">Мы работаем над обновлением наших проектов</p>
				</div>
			</div>
		</section>

		<!-- CTA Section -->
		<section class="bg-light-gold text-dark-green py-16 md:py-32">
			<div class="container max-w-screen-2xl px-4 text-center">
				<div class="max-w-3xl mx-auto space-y-6">
					<h2 class="text-3xl md:text-4xl font-light">Готовы обсудить ваш проект?</h2>
					<p class="text-lg">Закажите бесплатный расчет с чертежами, спецификацией и стоимостью. Наш специалист приедет к вам в удобное время.</p>
					<div class="flex flex-col sm:flex-row gap-4 justify-center pt-4">
						<LandingButton variant="dark" size="lg" @click="openConsultationDialog">Бесплатный расчет</LandingButton>
						<Link href="/">
							<LandingButton variant="outline" size="lg">Вернуться на главную</LandingButton>
						</Link>
					</div>
					<div class="text-sm text-dark-green/70 mt-6 space-y-1">
						<p>✓ Выезд замерщика бесплатно</p>
						<p>✓ Расчет готов в течение 24 часов</p>
						<p>✓ Никаких скрытых платежей</p>
					</div>
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

