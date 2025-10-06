<script setup>
import { ref, onMounted, onUnmounted, computed } from "vue";
import Button from "../Components/ui/button/Button.vue";
import GuestHeaderLayout from "../Layouts/GuestHeaderLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import {
	MapPinIcon,
	PaletteIcon,
	RectangleVerticalIcon,
	ArrowRightIcon,
	PhoneIcon,
	CheckCircleIcon,
	StarIcon,
	ShieldCheckIcon,
	ClockIcon,
	AwardIcon,
	ChevronDownIcon,
	MailIcon,
	ChevronUpIcon,
	ChevronLeftIcon,
	ChevronRightIcon,
	FlameIcon,
	ShieldIcon,
	LockIcon,
	GlassWaterIcon,
	CloudIcon,
	SunsetIcon,
	SunIcon,
} from "lucide-vue-next";
import LandingBadge from "../Components/LandingBadge.vue";
import LandingButton from "../Components/LandingButton.vue";
import ConsultationDialog from "../Components/ConsultationDialog.vue";
import GuestFooter from "../Layouts/GuestFooter.vue";

// Define props from Inertia
const props = defineProps({
	canLogin: Boolean,
	landingOptions: {
		type: Object,
		default: () => ({}),
	},
	initialPortfolio: {
		type: Array,
		default: () => [],
	},
});

// Transform portfolio data
const portfolio = computed(() => {
	return props.initialPortfolio.map((item) => ({
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
});

// Helper function to get option value with fallback
const getOption = (key, fallback = "") => {
	return props.landingOptions[key] || fallback;
};

// Features data
const features = ref([
	{
		icon: ShieldCheckIcon,
		title: "Гарантия 10 лет",
		description: "Полная гарантия на все виды работ и материалы",
	},
	{
		icon: ClockIcon,
		title: "Быстрый монтаж",
		description: "Установка от 1 до 3 дней в зависимости от сложности",
	},
	{
		icon: AwardIcon,
		title: "Премиум качество",
		description: "Используем только европейские материалы и фурнитуру",
	},
]);

// Stats data
const stats = ref([
	{ number: "500+", label: "Выполненных проектов" },
	{ number: "10", label: "Лет на рынке" },
	{ number: "98%", label: "Довольных клиентов" },
	{ number: "24/7", label: "Техническая поддержка" },
]);

// Services data
const services = ref([
	{
		title: "Безрамное остекление балконов",
		description: "Современное решение для увеличения пространства",
		image: "/assets/hero.jpg",
		price: "от 15 000 ₽/м²",
	},
	{
		title: "Панорамное остекление",
		description: "Максимальный обзор и естественное освещение",
		image: "/assets/hero.jpg",
		price: "от 18 000 ₽/м²",
	},
	{
		title: "Остекление террас",
		description: "Защита от непогоды с сохранением вида",
		image: "/assets/hero.jpg",
		price: "от 20 000 ₽/м²",
	},
]);

const TemperedGlassAdvantages = ref([
	{
		icon: FlameIcon,
		title: "Термостойкость",
		description: "Выдерживает воздействие высоких температур без деформации и потери целостности.",
	},
	{
		icon: ShieldIcon,
		title: "Повышенная прочность",
		description: "В 5 раз прочнее обычного стекла и выдерживает высокие ветровые нагрузки.",
	},
	{
		icon: LockIcon,
		title: "Безопасность",
		description: "При повреждении распадается на мелкие осколки с тупыми гранями, не способными поранить.",
	},
]);

const glassTypes = ref([
	{
		icon: GlassWaterIcon,
		title: "Обычное прозрачное стекло",
		description: "Классический вариант с высокой светопропускаемостью (~90%), обеспечивающий естественное освещение и лёгкость конструкции. Обладает нейтральным оттенком, подходит для любых интерьеров и устойчиво к перепадам температур.",
	},
	{
		icon: CloudIcon,
		title: "Тонированное в массе (серое)",
		description: "Глубокий серый оттенок снижает солнечную нагрузку и создаёт современный вид. Пропускает 50–70% света, уменьшает нагрев, подходит для солнечных сторон и сочетается с металлом и деревом.",
	},
	{
		icon: SunsetIcon,
		title: "Тонированное в массе (бронза)",
		description: "Тёплый золотисто-коричневый оттенок придаёт уют и элегантность. Задерживает УФ-лучи, снижает яркость без потери видимости, пропуская 40–60% света. Идеально смотрится в классических и средиземноморских стилях.",
	},
	{
		icon: SunIcon,
		title: "Просветлённое стекло “Оптивайт”",
		description: "Максимально прозрачно за счёт уменьшенного содержания железа, что убирает зеленоватый оттенок. Отличается естественной цветопередачей и лучшей светопроницаемостью.",
	},
]);

// Reactive state
const currentTestimonial = ref(0);
const isConsultationDialogOpen = ref(false);
const showBackToTop = ref(false);
const mailButtonPulse = ref(false);

// Carousel state
const carouselOffset = ref(0);
const isAutoScrolling = ref(true);
let autoScrollInterval = null;

// Touch/drag support for carousel
const carouselTouchStartX = ref(0);
const carouselTouchEndX = ref(0);
const isDragging = ref(false);

// Carousel functionality
const startAutoScroll = () => {
	if (autoScrollInterval) clearInterval(autoScrollInterval);
	autoScrollInterval = setInterval(() => {
		if (isAutoScrolling.value && portfolio.value.length > 0) {
			carouselOffset.value = (carouselOffset.value + 1) % portfolio.value.length;
		}
	}, 5000); // Auto-scroll every 5 seconds
};

const stopAutoScroll = () => {
	if (autoScrollInterval) {
		clearInterval(autoScrollInterval);
		autoScrollInterval = null;
	}
};

const pauseAutoScroll = () => {
	isAutoScrolling.value = false;
};

const resumeAutoScroll = () => {
	isAutoScrolling.value = true;
};

const nextSlide = () => {
	if (portfolio.value.length > 0) {
		carouselOffset.value = (carouselOffset.value + 1) % portfolio.value.length;
	}
};

const prevSlide = () => {
	if (portfolio.value.length > 0) {
		carouselOffset.value = carouselOffset.value === 0 ? portfolio.value.length - 1 : carouselOffset.value - 1;
	}
};

// Touch/Swipe handlers for carousel
const handleCarouselTouchStart = (event) => {
	carouselTouchStartX.value = event.touches[0].clientX;
	pauseAutoScroll();
};

const handleCarouselTouchMove = (event) => {
	// Optional: Add visual feedback during drag
};

const handleCarouselTouchEnd = (event) => {
	carouselTouchEndX.value = event.changedTouches[0].clientX;
	handleCarouselSwipe();
	resumeAutoScroll();
};

const handleCarouselSwipe = () => {
	const swipeThreshold = 50;
	const diff = carouselTouchStartX.value - carouselTouchEndX.value;

	if (Math.abs(diff) > swipeThreshold) {
		if (diff > 0) {
			nextSlide(); // Swipe left - next slide
		} else {
			prevSlide(); // Swipe right - previous slide
		}
	}
};

// Mouse drag handlers for desktop
const handleCarouselMouseDown = (event) => {
	isDragging.value = true;
	carouselTouchStartX.value = event.clientX;
	pauseAutoScroll();
	event.preventDefault();
};

const handleCarouselMouseMove = (event) => {
	if (!isDragging.value) return;
	// Optional: Add visual feedback during drag
};

const handleCarouselMouseUp = (event) => {
	if (!isDragging.value) return;
	isDragging.value = false;
	carouselTouchEndX.value = event.clientX;
	handleCarouselSwipe();
	resumeAutoScroll();
};

const handleCarouselMouseLeave = () => {
	if (isDragging.value) {
		isDragging.value = false;
		resumeAutoScroll();
	}
};

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

// Smooth scroll functionality
const scrollToSection = (sectionId) => {
	const element = document.getElementById(sectionId);
	if (element) {
		// Calculate offset to account for fixed header
		const headerHeight = 80; // Approximate header height
		const elementPosition = element.getBoundingClientRect().top;
		const offsetPosition = elementPosition + window.pageYOffset - headerHeight;

		window.scrollTo({
			top: offsetPosition,
			behavior: "smooth"
		});
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
		behavior: "smooth"
	});
};

// Handle scroll events for back to top button
const handleScroll = () => {
	showBackToTop.value = window.scrollY > 500;
};

// Pulse animation for mail button
const triggerMailButtonPulse = () => {
	mailButtonPulse.value = true;
	setTimeout(() => {
		mailButtonPulse.value = false;
	}, 1000); // Pulse duration
};

let pulseInterval = null;

// Intersection Observer for animations
onMounted(() => {
	// Start auto-scroll if portfolio data exists
	if (portfolio.value.length > 0) {
		startAutoScroll();
	}

	// Add scroll event listener for back to top button
	window.addEventListener("scroll", handleScroll);

	// Start pulse animation interval when buttons are visible
	const startPulseInterval = () => {
		if (pulseInterval) clearInterval(pulseInterval);
		pulseInterval = setInterval(triggerMailButtonPulse, 5000); // Every 5 seconds
	};

	// Watch for showBackToTop changes to start/stop pulse
	const stopPulseInterval = () => {
		if (pulseInterval) {
			clearInterval(pulseInterval);
			pulseInterval = null;
		}
	};

	// Start pulse when buttons become visible
	const checkPulseStart = () => {
		if (showBackToTop.value && !pulseInterval) {
			startPulseInterval();
		} else if (!showBackToTop.value) {
			stopPulseInterval();
		}
	};

	// Watch for scroll changes to manage pulse
	const originalHandleScroll = handleScroll;
	const enhancedHandleScroll = () => {
		const wasVisible = showBackToTop.value;
		originalHandleScroll();
		if (wasVisible !== showBackToTop.value) {
			checkPulseStart();
		}
	};

	window.removeEventListener("scroll", handleScroll);
	window.addEventListener("scroll", enhancedHandleScroll);

	// Add structured data to document head
	const structuredDataScript = document.createElement('script');
	structuredDataScript.type = 'application/ld+json';
	structuredDataScript.textContent = JSON.stringify(structuredData.value);
	document.head.appendChild(structuredDataScript);

	// Add Organization schema for better brand recognition
	const organizationSchema = {
		"@context": "https://schema.org",
		"@type": "Organization",
		"@id": "https://llymar.ru/#organization",
		name: getOption('site_name', 'LLYMAR'),
		alternateName: `${getOption('site_name', 'LLYMAR')} - Безрамное остекление`,
		url: "https://llymar.ru",
		logo: `https://llymar.ru${getOption('og_image', '/assets/hero.jpg')}`,
		description: getOption('meta_description', "Российский производитель систем безрамного остекления для жилых и коммерческих объектов"),
		foundingDate: getOption('founding_year', '2019'),
		numberOfEmployees: getOption('employees_count', '10-50'),
		sameAs: [
			getOption('social_facebook', ''),
			getOption('social_instagram', ''),
			getOption('social_vk', ''),
			getOption('social_youtube', ''),
			getOption('yandex_maps_link', ''),
		].filter(link => link) // Remove empty links
	};

	const organizationScript = document.createElement('script');
	organizationScript.type = 'application/ld+json';
	organizationScript.textContent = JSON.stringify(organizationSchema);
	document.head.appendChild(organizationScript);

	// Add WebSite schema for site-level information
	const websiteSchema = {
		"@context": "https://schema.org",
		"@type": "WebSite",
		"@id": "https://llymar.ru/#website",
		name: `${getOption('site_name', 'LLYMAR')} - Безрамное остекление`,
		alternateName: getOption('site_name', 'LLYMAR'),
		url: "https://llymar.ru",
		description: getOption('meta_description', "Безрамное раздвижное остекление террас, веранд, беседок, кафе и ресторанов в Краснодаре"),
		publisher: {
			"@id": "https://llymar.ru/#organization"
		},
		potentialAction: {
			"@type": "SearchAction",
			target: {
				"@type": "EntryPoint",
				urlTemplate: "https://llymar.ru/?s={search_term_string}"
			},
			"query-input": "required name=search_term_string"
		},
		inLanguage: "ru-RU"
	};

	const websiteScript = document.createElement('script');
	websiteScript.type = 'application/ld+json';
	websiteScript.textContent = JSON.stringify(websiteSchema);
	document.head.appendChild(websiteScript);

	const observerOptions = {
		threshold: 0.1,
		rootMargin: "0px 0px -50px 0px",
	};

	const observer = new IntersectionObserver((entries) => {
		entries.forEach((entry) => {
			if (entry.isIntersecting) {
				entry.target.classList.add("animate-in");
			}
		});
	}, observerOptions);

	document.querySelectorAll(".animate-on-scroll").forEach((el) => {
		observer.observe(el);
	});
});

// Cleanup on unmount
onUnmounted(() => {
	window.removeEventListener("scroll", handleScroll);
	
	// Clear pulse interval
	if (pulseInterval) {
		clearInterval(pulseInterval);
		pulseInterval = null;
	}
	
	// Stop auto-scroll
	stopAutoScroll();
	
	// Remove structured data scripts
	const structuredDataScripts = document.querySelectorAll('script[type="application/ld+json"]');
	structuredDataScripts.forEach(script => {
		script.remove();
	});
});

// Structured data for SEO - Optimized for Yandex rich snippets
const structuredData = computed(() => ({
	"@context": "https://schema.org",
	"@type": "LocalBusiness",
	"@id": "https://llymar.ru/#business",
	name: `${getOption('site_name', 'LLYMAR')} - Безрамное остекление`,
	alternateName: getOption('site_name', 'LLYMAR'),
	description: getOption('meta_description', "Безрамное раздвижное остекление террас, веранд, беседок, кафе и ресторанов. Наша компания специализируется на производстве и установке безрамного раздвижного остекления (система слайдеры), которая сочетает в себе элегантный минимализм и инженерную точность."),
	url: "https://llymar.ru",
	image: `https://llymar.ru${getOption('og_image', '/assets/hero.jpg')}`,
	logo: `https://llymar.ru${getOption('og_image', '/assets/hero.jpg')}`,
	
	// Enhanced contact information for Yandex rich snippets
	telephone: getOption('phone', '+7 989 804 12-34'),
	email: getOption('email', 'info@llymar.ru'),
	
	// Complete address information
	address: {
		"@type": "PostalAddress",
		streetAddress: getOption('address', 'ул. Уральская, 145/3'),
		addressLocality: getOption('address_city', 'Краснодар'),
		addressRegion: getOption('address_region', 'Краснодарский край'),
		postalCode: getOption('postal_code', '350080'),
		addressCountry: "RU",
	},
	
	// Geographic coordinates for better local search
	geo: {
		"@type": "GeoCoordinates",
		latitude: getOption('geo_latitude', '45.044534'),
		longitude: getOption('geo_longitude', '39.114309'),
	},
	
	// Detailed opening hours that Yandex can display
	openingHoursSpecification: [
		{
			"@type": "OpeningHoursSpecification",
			dayOfWeek: ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"],
			opens: "09:00",
			closes: "20:00"
		},
	],
	
	// Legacy format for backward compatibility
	openingHours: [
		"Mo-Fr 09:00-20:00",
	],
	
	// Business details for rich snippets
	priceRange: "₽₽₽",
	currenciesAccepted: "RUB",
	paymentAccepted: ["Cash", "Credit Card", "Bank Transfer", "Online Payment"],
	
	// Service area
	areaServed: [
		{
			"@type": "City",
			name: "Краснодар",
			"@id": "https://ru.wikipedia.org/wiki/Краснодар"
		},
		{
			"@type": "State", 
			name: "Краснодарский край"
		}
	],
	
	// Contact point for better business info display
	contactPoint: [
		{
			"@type": "ContactPoint",
			telephone: getOption('phone', '+7 989 804 12-34'),
			contactType: "customer service",
			availableLanguage: ["Russian"],
			areaServed: "RU",
			hoursAvailable: {
				"@type": "OpeningHoursSpecification",
				dayOfWeek: ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"],
				opens: "09:00",
				closes: "20:00"
			}
		},
		{
			"@type": "ContactPoint",
			telephone: getOption('phone', '+7 989 804 12-34'),
			contactType: "sales",
			availableLanguage: ["Russian"],
			areaServed: "RU"
		}
	],
	
	// Business categories for better classification
	category: [
		"Строительство и ремонт",
		"Остекление",
		"Алюминиевые конструкции"
	],
	
	// Services offered - enhanced structure
	hasOfferCatalog: {
		"@type": "OfferCatalog",
		name: "Услуги безрамного остекления",
		itemListElement: [
			{
				"@type": "Offer",
				"@id": "https://llymar.ru/#service-terrace",
				name: "Безрамное остекление террасы",
				description: "Современное безрамное остекление террас с использованием качественных материалов",
				price: "от 15000 руб/м²",
				priceCurrency: "RUB",
				availability: "https://schema.org/InStock",
				itemOffered: {
					"@type": "Service",
					name: "Безрамное остекление террасы",
					serviceType: "Остекление",
					areaServed: {
						"@type": "Place", 
						name: "Краснодар"
					}
				}
			},
			{
				"@type": "Offer",
				"@id": "https://llymar.ru/#service-balcony",
				name: "Безрамное остекление беседки",
				description: "Элегантное остекление беседок без видимых рам",
				price: "от 12000 руб/м²",
				priceCurrency: "RUB",
				availability: "https://schema.org/InStock",
				itemOffered: {
					"@type": "Service",
					name: "Безрамное остекление беседки",
					serviceType: "Остекление",
					areaServed: {
						"@type": "Place",
						name: "Краснодар"
					}
				}
			},
			{
				"@type": "Offer",
				"@id": "https://llymar.ru/#service-veranda",
				name: "Безрамное остекление веранды",
				description: "Профессиональное остекление веранд для защиты от погодных условий",
				price: "от 18000 руб/м²",
				priceCurrency: "RUB",
				availability: "https://schema.org/InStock",
				itemOffered: {
					"@type": "Service",
					name: "Безрамное остекление веранды",
					serviceType: "Остекление",
					areaServed: {
						"@type": "Place",
						name: "Краснодар"
					}
				}
			},
			{
				"@type": "Offer",
				"@id": "https://llymar.ru/#service-restaurant",
				name: "Безрамное остекление ресторана",
				description: "Остекление ресторанов и кафе для создания комфортной атмосферы",
				price: "от 20000 руб/м²",
				priceCurrency: "RUB",
				availability: "https://schema.org/InStock",
				itemOffered: {
					"@type": "Service",
					name: "Безрамное остекление ресторана",
					serviceType: "Остекление коммерческих объектов",
					areaServed: {
						"@type": "Place",
						name: "Краснодар"
					}
				}
			},
			{
				"@type": "Offer",
				"@id": "https://llymar.ru/#service-cafe",
				name: "Безрамное остекление кафе",
				description: "Качественное остекление кафе с гарантией качества",
				price: "от 20000 руб/м²",
				priceCurrency: "RUB",
				availability: "https://schema.org/InStock",
				itemOffered: {
					"@type": "Service",
					name: "Безрамное остекление кафе",
					serviceType: "Остекление коммерческих объектов",
					areaServed: {
						"@type": "Place",
						name: "Краснодар"
					}
				}
			}
		]
	},
	
	// Social media and additional links
	sameAs: [
		// Add your social media profiles when available:
		// "https://www.facebook.com/llymar",
		// "https://www.instagram.com/llymar",
		// "https://vk.com/llymar",
		// "https://yandex.ru/maps/org/llymar/..." // Add Yandex Maps link
	],
	
	// Additional business information
	foundingDate: getOption('founding_year', '2019'),
	numberOfEmployees: getOption('employees_count', '10-50'),
	slogan: getOption('site_tagline', 'Ваш комфорт - наша работа!'),
	
	// Legal information (helps with trust)
	// taxID: "ИНН вашей компании", // Add your actual tax ID
	// vatID: "КПП вашей компании", // Add your VAT ID if applicable
	
	// Reviews placeholder (add when you have reviews)
	// aggregateRating: {
	//   "@type": "AggregateRating",
	//   ratingValue: "4.8",
	//   reviewCount: "25",
	//   bestRating: "5",
	//   worstRating: "1"
	// }
}));
</script>

<template>
	<Head>
		<title>{{ getOption('meta_title', 'Безрамное остекление в Краснодаре') }}</title>
		<meta name="description" :content="getOption('meta_description', 'Безрамное раздвижное остекление террас, веранд, беседок, кафе и ресторанов. Европейское качество, установка за 1-3 дня.')" />
		<meta name="keywords" :content="getOption('meta_keywords', 'безрамное остекление, остекление террас, остекление веранд, остекление беседок, остекление кафе, остекление ресторанов')" />

		<!-- Open Graph / Facebook -->
		<meta property="og:type" content="website" />
		<meta property="og:url" content="https://llymar.ru/" />
		<meta property="og:title" :content="`${getOption('site_name', 'LLYMAR')} - ${getOption('meta_title', 'Безрамное остекление в Краснодаре')}`" />
		<meta property="og:description" :content="getOption('meta_description', 'Безрамное раздвижное остекление террас, веранд, беседок, кафе и ресторанов. Европейское качество, установка за 1-3 дня.')" />
		<meta property="og:image" :content="getOption('og_image', '/assets/hero.jpg')" />
		<meta property="og:locale" content="ru_RU" />

		<!-- Twitter -->
		<meta property="twitter:card" content="summary_large_image" />
		<meta property="twitter:url" content="https://llymar.ru/" />
		<meta property="twitter:title" :content="`${getOption('site_name', 'LLYMAR')} - ${getOption('meta_title', 'Безрамное остекление в Краснодаре')}`" />
		<meta property="twitter:description" :content="getOption('meta_description', 'Безрамное раздвижное остекление террас, веранд, беседок, кафе и ресторанов. Европейское качество, установка за 1-3 дня.')" />
		<meta property="twitter:image" :content="getOption('og_image', '/assets/hero.jpg')" />

		<!-- Additional SEO -->
		<meta name="robots" content="index, follow" />
		<meta name="author" content="LLYMAR" />
		<meta name="geo.region" content="RU-KDA" />
		<meta name="geo.placename" content="Краснодар" />
		<meta name="geo.position" content="44.979477;39.095646" />
		<meta name="ICBM" content="44.979477, 39.095646" />
	</Head>

	<!-- Hero Section -->
	<section id="hero" class="bg-[url('/assets/hero.jpg')] bg-cover bg-center text-white relative min-h-screen overflow-hidden">
		<div class="bg-gradient-to-br from-[#23322D]/90 via-[#23322D]/80 to-[#23322D]/70 min-h-screen flex flex-col">
			<GuestHeaderLayout theme="transparent" :openConsultationDialog="openConsultationDialog" />

			<div class="container max-w-screen-2xl px-2 md:px-4 flex-1 flex flex-col justify-center">
				<div class="flex flex-col gap-6 md:gap-8 py-12 md:py-0 animate-on-scroll opacity-0 translate-y-8 transition-all duration-1000">
					<div class="space-y-4">
						<h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl xl:text-7xl uppercase font-light leading-tight" v-html="getOption('hero_title', 'Премиальное <br /><span class=\'text-light-gold\'>безрамное</span> остекление')">
						</h1>

						<p class="text md:text-xl text-gray-300 max-w-2xl leading-relaxed">{{ getOption('hero_subtitle', 'Превратите свое пространство в произведение искусства с нашими безрамными системами остекления.') }}</p>
					</div>

					<div class="flex flex-col sm:flex-row items-center gap-4 md:gap-8 mt-6">
						<LandingButton @click="openConsultationDialog"> Получить консультацию </LandingButton>

						<!-- Phone number with microdata for rich snippets -->
						<LandingButton 
							variant="secondary" 
							:icon="PhoneIcon" 
							:href="`tel:${getOption('phone', '+7 989 804 12-34')}`" 
							iconPosition="left"
							itemProp="telephone"
							itemScope
							itemType="https://schema.org/ContactPoint"
						> 
							<span itemProp="telephone">{{ getOption('phone_formatted', '+7 (989) 804 12-34') }}</span>
						</LandingButton>
					</div>

					<!-- Stats -->
					<!-- <div class="grid grid-cols-2 md:flex flex-col md:flex-row items-center justify-between gap-6 mt-4 md:mt-12 pt-8 border-t border-white/20">
						<div v-for="stat in stats" :key="stat.label" class="text-center animate-on-scroll opacity-0 translate-y-4 transition-all duration-700">
							<div class="text-2xl md:text-3xl font-bold text-light-gold montserrat">{{ stat.number }}</div>
							<div class="text-sm text-gray-300 mt-1">{{ stat.label }}</div>
						</div>
						<div class="hidden md:block animate-bounce cursor-pointer" @click="scrollToSection('portfolio')">
							<div class="flex flex-col items-center gap-2 text-white/70 hover:text-light-gold transition-colors">
								<img src="/assets/scrolldown.svg" alt="scroll" />
							</div>
						</div>
					</div> -->
					<div class="text-right">
						<div class="inline-block text-white/70 hover:text-light-gold transition-colors animate-bounce cursor-pointer mt-4 pt-8 md:mt-12" @click="scrollToSection('portfolio')">
							<img src="/assets/scrolldown.svg" alt="scroll" />
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- Features Section -->
	<section id="features" class="hidden bg-white py-16 md:py-32">
		<div class="container max-w-screen-2xl px-4">
			<div class="grid grid-cols-1 md:grid-cols-3 gap-8">
				<div v-for="(feature, index) in features" :key="feature.title" class="text-center animate-on-scroll opacity-0 translate-y-8 transition-all duration-700" :style="`animation-delay: ${index * 200}ms`">
					<div class="inline-flex items-center justify-center w-16 h-16 bg-dark-green rounded-full mb-6">
						<component :is="feature.icon" class="w-8 h-8 text-light-gold" />
					</div>
					<h3 class="text-xl font-semibold text-dark-green mb-3">{{ feature.title }}</h3>
					<p class="text-gray-600">{{ feature.description }}</p>
				</div>
			</div>
		</div>
	</section>

	<!-- Services Section -->
	<section id="services" class="hidden bg-black py-16 md:py-32">
		<div class="container max-w-screen-2xl px-4">
			<div class="text-center mb-16 animate-on-scroll opacity-0 translate-y-8 transition-all duration-700">
				<LandingBadge variant="gold" size="sm"> Наши услуги </LandingBadge>
				<h2 class="text-3xl md:text-4xl font-light text-white mb-4">Что мы предлагаем</h2>
				<p class="text-gray-300 max-w-2xl mx-auto">Полный спектр услуг по безрамному остеклению с использованием премиальных материалов</p>
			</div>

			<div class="grid grid-cols-1 md:grid-cols-3 gap-8">
				<div
					v-for="(service, index) in services"
					:key="service.title"
					class="group bg-dark-green rounded-2xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-500 animate-on-scroll opacity-0 translate-y-8"
					:style="`animation-delay: ${index * 200}ms`"
				>
					<div class="aspect-video bg-cover bg-center relative overflow-hidden" :style="`background-image: url(${service.image})`">
						<div class="absolute inset-0 bg-black/10 group-hover:bg-black/40 transition-colors"></div>
						<div class="absolute top-4 right-4 bg-black/10 text-white backdrop-blur-sm px-3 py-1 rounded-full text-sm font-semibold">
							{{ service.price }}
						</div>
					</div>
					<div class="p-6">
						<h3 class="text-xl font-semibold text-white mb-3">{{ service.title }}</h3>
						<p class="text-gray-200 mb-4">{{ service.description }}</p>
						<button class="text-white hover:text-light-gold font-medium flex items-center gap-2 group-hover:gap-3 transition-all">
							Подробнее
							<ArrowRightIcon class="w-4 h-4" />
						</button>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- Portfolio Section -->
	<section id="portfolio" class="bg-dark-green text-white py-16 md:py-24 overflow-hidden">
		<div class="container max-w-screen-2xl px-4">
			<div class="flex flex-col md:flex-row md:justify-between md:items-end gap-8 mb-16">
				<div class="animate-on-scroll opacity-0 translate-y-8 transition-all duration-700">
					<LandingBadge size="sm"> Примеры работ </LandingBadge>
					<h4 class="text-3xl md:text-4xl font-light mb-4">Результат говорит сам за себя</h4>
					<p class="text-gray-300">Каждый проект — это уникальное решение, созданное с учетом пожеланий клиента и особенностей объекта</p>
				</div>
				<div class="animate-on-scroll opacity-0 translate-y-8 transition-all duration-700">
					<Link href="/portfolio">
						<LandingButton variant="secondary" size="lg">
							<span class="montserrat font-semibold">Смотреть все работы</span>
						</LandingButton>
					</Link>
				</div>
			</div>

			<!-- Portfolio Carousel -->
			<div 
				v-if="portfolio.length > 0" 
				class="relative -mx-4 px-4 md:mx-0 md:px-0"
				@mouseenter="pauseAutoScroll"
				@mouseleave="handleCarouselMouseLeave"
			>
				<!-- Navigation Arrows -->
				<button 
					@click="prevSlide"
					class="absolute left-0 md:-left-6 top-1/2 -translate-y-1/2 z-10 bg-light-gold hover:bg-light-gold/90 text-dark-green p-2 md:p-3 rounded-full shadow-lg transition-all hover:scale-110"
					aria-label="Previous slide"
				>
					<ChevronLeftIcon class="w-5 h-5 md:w-6 md:h-6" />
				</button>
				<button 
					@click="nextSlide"
					class="absolute right-0 md:-right-6 top-1/2 -translate-y-1/2 z-10 bg-light-gold hover:bg-light-gold/90 text-dark-green p-2 md:p-3 rounded-full shadow-lg transition-all hover:scale-110"
					aria-label="Next slide"
				>
					<ChevronRightIcon class="w-5 h-5 md:w-6 md:h-6" />
				</button>

				<!-- Carousel Container -->
				<div class="overflow-hidden">
					<!-- Mobile: 1 column -->
					<div 
						class="flex md:hidden transition-transform duration-700 ease-in-out gap-4 select-none"
						:class="{ 'cursor-grabbing': isDragging, 'cursor-grab': !isDragging }"
						:style="{
							transform: `translateX(calc(-${carouselOffset * 100}% - ${carouselOffset * 16}px))`,
						}"
						@touchstart="handleCarouselTouchStart"
						@touchmove="handleCarouselTouchMove"
						@touchend="handleCarouselTouchEnd"
						@mousedown="handleCarouselMouseDown"
						@mousemove="handleCarouselMouseMove"
						@mouseup="handleCarouselMouseUp"
					>
						<Link
							v-for="item in portfolio"
							:key="`mobile-${item.id}`"
							:href="`/portfolio/${item.id}`"
							class="group cursor-pointer flex-shrink-0 w-full transition-all duration-300 hover:-translate-y-2"
						>
							<div class="relative overflow-hidden rounded-2xl">
								<div class="aspect-square bg-cover bg-center transition-transform duration-500 group-hover:scale-110" :style="`background-image: url(${item.image})`"></div>
								<div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
									<div class="absolute bottom-4 left-4 right-4">
										<div class="text-light-gold text-sm font-medium">{{ item.type }}</div>
										<div class="text-white text-lg font-semibold">{{ item.area }}</div>
									</div>
								</div>
							</div>

							<div class="flex flex-col gap-3 mt-4 montserrat">
								<h3 class="text-lg font-semibold group-hover:text-light-gold transition-colors">{{ item.type }}</h3>
								<div class="flex flex-row gap-2 items-center text-sm">
									<MapPinIcon class="text-light-gold w-4 h-4" />
									<span>{{ item.location }}</span>
									<span class="text-gray-400">• {{ item.year }}</span>
								</div>
								<div class="flex flex-row gap-2 items-center text-sm">
									<RectangleVerticalIcon class="text-light-gold w-4 h-4" />
									<span>{{ item.glass }}</span>
								</div>
								<div class="flex flex-row gap-2 items-center text-sm">
									<PaletteIcon class="text-light-gold w-4 h-4" />
									<span>{{ item.profile }}</span>
								</div>
							</div>
						</Link>
					</div>

					<!-- Tablet: 2 columns -->
					<div 
						class="hidden md:flex lg:hidden transition-transform duration-700 ease-in-out gap-8 select-none"
						:class="{ 'cursor-grabbing': isDragging, 'cursor-grab': !isDragging }"
						:style="{
							transform: `translateX(calc(-${carouselOffset * 50}% - ${carouselOffset * 16}px))`,
						}"
						@touchstart="handleCarouselTouchStart"
						@touchmove="handleCarouselTouchMove"
						@touchend="handleCarouselTouchEnd"
						@mousedown="handleCarouselMouseDown"
						@mousemove="handleCarouselMouseMove"
						@mouseup="handleCarouselMouseUp"
					>
						<Link
							v-for="item in portfolio"
							:key="`tablet-${item.id}`"
							:href="`/portfolio/${item.id}`"
							class="group cursor-pointer flex-shrink-0 w-[calc(50%-16px)] transition-all duration-300 hover:-translate-y-2"
						>
							<div class="relative overflow-hidden rounded-2xl">
								<div class="aspect-square bg-cover bg-center transition-transform duration-500 group-hover:scale-110" :style="`background-image: url(${item.image})`"></div>
								<div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
									<div class="absolute bottom-4 left-4 right-4">
										<div class="text-light-gold text-sm font-medium">{{ item.type }}</div>
										<div class="text-white text-lg font-semibold">{{ item.area }}</div>
									</div>
								</div>
							</div>

							<div class="flex flex-col gap-3 mt-4 montserrat">
								<h3 class="text-lg font-semibold group-hover:text-light-gold transition-colors">{{ item.type }}</h3>
								<div class="flex flex-row gap-2 items-center text-sm">
									<MapPinIcon class="text-light-gold w-4 h-4" />
									<span>{{ item.location }}</span>
									<span class="text-gray-400">• {{ item.year }}</span>
								</div>
								<div class="flex flex-row gap-2 items-center text-sm">
									<RectangleVerticalIcon class="text-light-gold w-4 h-4" />
									<span>{{ item.glass }}</span>
								</div>
								<div class="flex flex-row gap-2 items-center text-sm">
									<PaletteIcon class="text-light-gold w-4 h-4" />
									<span>{{ item.profile }}</span>
								</div>
							</div>
						</Link>
					</div>

					<!-- Desktop: 4 columns -->
					<div 
						class="hidden lg:flex transition-transform duration-700 ease-in-out gap-8 select-none"
						:class="{ 'cursor-grabbing': isDragging, 'cursor-grab': !isDragging }"
						:style="{
							transform: `translateX(calc(-${carouselOffset * 25}% - ${carouselOffset * 24}px))`,
						}"
						@touchstart="handleCarouselTouchStart"
						@touchmove="handleCarouselTouchMove"
						@touchend="handleCarouselTouchEnd"
						@mousedown="handleCarouselMouseDown"
						@mousemove="handleCarouselMouseMove"
						@mouseup="handleCarouselMouseUp"
					>
						<Link
							v-for="item in portfolio"
							:key="`desktop-${item.id}`"
							:href="`/portfolio/${item.id}`"
							class="group cursor-pointer flex-shrink-0 w-[calc(25%-24px)] transition-all duration-300 hover:-translate-y-2"
						>
							<div class="relative overflow-hidden rounded-2xl">
								<div class="aspect-square bg-cover bg-center transition-transform duration-500 group-hover:scale-110" :style="`background-image: url(${item.image})`"></div>
								<div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
									<div class="absolute bottom-4 left-4 right-4">
										<div class="text-light-gold text-sm font-medium">{{ item.type }}</div>
										<div class="text-white text-lg font-semibold">{{ item.area }}</div>
									</div>
								</div>
							</div>

							<div class="flex flex-col gap-3 mt-4 montserrat">
								<h3 class="text-lg font-semibold group-hover:text-light-gold transition-colors">{{ item.type }}</h3>
								<div class="flex flex-row gap-2 items-center text-sm">
									<MapPinIcon class="text-light-gold w-4 h-4" />
									<span>{{ item.location }}</span>
									<span class="text-gray-400">• {{ item.year }}</span>
								</div>
								<div class="flex flex-row gap-2 items-center text-sm">
									<RectangleVerticalIcon class="text-light-gold w-4 h-4" />
									<span>{{ item.glass }}</span>
								</div>
								<div class="flex flex-row gap-2 items-center text-sm">
									<PaletteIcon class="text-light-gold w-4 h-4" />
									<span>{{ item.profile }}</span>
								</div>
							</div>
						</Link>
					</div>
				</div>

				<!-- Carousel Indicators -->
				<div class="flex justify-center gap-2 mt-8">
					<button
						v-for="(item, index) in portfolio"
						:key="`indicator-${index}`"
						@click="carouselOffset = index"
						class="w-2 h-2 rounded-full transition-all duration-300"
						:class="carouselOffset === index ? 'bg-light-gold w-8' : 'bg-white/30 hover:bg-white/50'"
						:aria-label="`Перейти к слайду ${index + 1}`"
					></button>
				</div>
			</div>

			<!-- Empty State -->
			<div v-else-if="portfolio.length === 0" class="text-center py-16">
				<div class="text-gray-400 text-lg mb-4">Портфолио временно недоступно</div>
				<p class="text-gray-500">Мы работаем над обновлением наших проектов</p>
			</div>

			<!-- CTA -->
			<div id="consultation" class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-16 mt-16 border-t border-white/20 animate-on-scroll opacity-0 translate-y-8 transition-all duration-700">
				<div class="text-center max-w-2xl">
					<h3 class="text-2xl md:text-3xl font-light mb-4">Готовы обсудить ваш проект?</h3>
					<p class="text-gray-300 mb-6">Закажите бесплатный расчет с чертежами, спецификацией и стоимостью. Наш специалист приедет к вам в удобное время.</p>
				</div>

				<div>
					<div class="flex flex-col justify-center sm:flex-row gap-4 w-full">
						<LandingButton @click="openConsultationDialog" size="lg">
							<span class="montserrat font-semibold">Бесплатный расчет</span>
						</LandingButton>
					</div>

					<div class="text-center text-sm text-gray-400 mt-4">
						<p>✓ Выезд замерщика бесплатно</p>
						<p>✓ Расчет готов в течение 24 часов</p>
						<p>✓ Никаких скрытых платежей</p>
					</div>
				</div>
			</div>
		</div>
	</section>

	<section id="about" class="bg-dark-green py-16 md:py-32">
		<div class="container max-w-screen-2xl px-4 text-white">
			<div class="grid grid-cols-1 md:grid-cols-2 gap-8">
				<div>
					<div class="flex flex-col gap-8 mb-6">
						<div class="animate-on-scroll opacity-0 space-y-4 translate-y-8 transition-all duration-700">
							<LandingBadge variant="gold" size="sm"> О нас </LandingBadge>
							<h4 class="text-3xl md:text-4xl font-light mb-4">Ваш комфорт - наша работа!</h4>
						</div>
					</div>

					<div class="text-justify space-y-4">
						<p>Llymar — это надёжный российский производитель систем безрамного остекления для жилых и коммерческих объектов. Уже более 5 лет мы создаём современные и стильные решения, которые делают пространство безопасным, комфортным и визуально просторным.</p>
						<p>Мы реализуем проекты любой сложности и конфигурации, обеспечивая высокий уровень сервиса на каждом этапе - от проектирования до монтажа. Наша компания специализируется на производстве и установке безрамного раздвижного остекления (система слайдеры),  которая сочетает в себе элегантный минимализм и инженерную точность.</p>
						<p>Вы получите не просто панорамный обзор, а бескомпромиссное качество – без рам, но с абсолютной защитой!</p>
						<p>Наша команда работает по всей России, в странах СНГ и Европы. Благодаря широкой партнёрской сети мы предлагаем первоклассное обслуживание в любом регионе.</p>
					</div>
				</div>

				<div class="grid grid-cols-2 gap-1 md:gap-8 text-center">
					<div class="flex flex-col gap-2 items-center justify-center p-4 border-r border-light-gold">
						<span class="text-3xl font-bold block text-light-gold">1000+</span>
						<span>довольных клиентов</span>
					</div>
					<div class="flex flex-col gap-2 items-center justify-center p-4 border-b border-light-gold">
						<span class="text-3xl font-bold block text-light-gold">5+</span>
						<span>лет опыта</span>
					</div>
					<div class="flex flex-col gap-2 items-center justify-center p-4 border-t border-light-gold">
						<span class="text-3xl font-bold block text-light-gold">98%</span>
						<span>заказчиков нас рекомендуют</span>
					</div>
					<div class="flex flex-col gap-2 items-center justify-center p-4 border-l border-light-gold">
						<span class="text-3xl font-bold block text-light-gold">164</span>
						<span>региона могут заказать у нас</span>
					</div>
				</div>
			</div>
		</div>
	</section>

	<section id="glass-types" class="bg-gray-50 pt-16 md:pt-32 montserrat">
		<div class="container max-w-screen-2xl px-4">
			<div class="flex flex-col gap-4 md:gap-8 mb-6">
				<div class="animate-on-scroll opacity-0 space-y-4 translate-y-8 transition-all duration-700">
					<LandingBadge variant="dark" size="sm"> Выбор стекла </LandingBadge>
					<h4 class="text-3xl md:text-4xl font-light mb-4">Преимущества закаленного стекла</h4>
				</div>
			</div>

			<div>
				<p class="text-justify text-base">
					Закаленное стекло (каленое стекло) — современный материал, получаемый из обычных стеклянных листов путем тепловой обработки. В результате применения данного метода хрупкие изделия кардинально меняют физические свойства, становятся прочными и
					надежными. В процессе изготовления стеклянных конструкций мы используем только закаленное стекло толщиной 10 мм.
				</p>

				<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 md:gap-8 mt-12">
					<div v-for="advantage in TemperedGlassAdvantages" :key="advantage.title" class="text-center flex flex-col gap-4 items-center bg-white p-4 md:p-8">
						<div class="flex flex-row gap-2 items-center justify-center bg-light-gold rounded-full p-4">
							<component :is="advantage.icon" class="w-6 h-6 text-dark-green" />
						</div>
						<h5 class="text-lg font-semibold text-dark-green">{{ advantage.title }}</h5>
						<p class="text-base text-gray-600">{{ advantage.description }}</p>
					</div>
				</div>
			</div>

			<div class="mt-16">
				<h2 class="text-4xl md:text-4xl font-light mb-8 text-center">Виды стекла</h2>
				<div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-8">
					<div v-for="glass in glassTypes" :key="glass.title" class="flex flex-col gap-4 bg-white p-4 md:p-8">
						<div class="inline-block">
							<div class="inline-block bg-green-50 rounded-full p-4">
								<component :is="glass.icon" class="w-6 h-6 text-dark-green" />
							</div>
						</div>
						<h5 class="text-lg font-semibold text-dark-green">{{ glass.title }}</h5>
						<p class="text-sm md:text-base text-justify text-gray-600">{{ glass.description }}</p>
					</div>
				</div>
			</div>
		</div>
		<div class="mt-16 md:mt-32">
			<img src="/assets/glass-types.jpg" class="w-full" alt="" />
		</div>
	</section>

	<section id="contact" class="bg-light-gold text-dark-green py-16 md:py-32">
		<div class="container max-w-screen-2xl px-4 text-center">
			<div class="flex flex-col gap-8">
				<div class="animate-on-scroll opacity-0 space-y-4 translate-y-8 transition-all duration-700">
					<LandingBadge variant="dark" size="sm"> Свяжитесь с нами </LandingBadge>
					<h4 class="text-3xl md:text-4xl font-light mb-4">Проконсультируем и обсудим детали</h4>
				</div>
				
				<!-- Enhanced contact section with microdata for Yandex -->
				<div 
					class="flex flex-col md:flex-row items-center justify-center gap-8"
					itemScope 
					itemType="https://schema.org/LocalBusiness"
				>
					<div class="flex flex-col gap-4">
						<LandingButton variant="dark" showArrow @click="openConsultationDialog"> Получить консультацию </LandingButton>
						
						<!-- Business hours for rich snippets -->
						<!-- <div class="text-sm text-dark-green" itemProp="openingHours">
							<div><strong>Часы работы:</strong></div>
							<div>Пн-Пт: 09:00-20:00</div>
							<div>Сб: 09:00-18:00</div>
							<div>Вс: 10:00-16:00</div>
						</div> -->
					</div>
					
					<!-- <span class="hidden md:block">или</span> -->
					
					<!-- <div class="flex flex-col gap-4">
						<div class="flex flex-row gap-4">
							<a 
								href="tel:+7 989 804 12-34"
								class="inline-flex items-center gap-2 bg-dark-green text-light-gold px-4 py-2 rounded-full transition-colors hover:bg-dark-green/90"
								itemProp="telephone"
							>
								<PhoneIcon class="w-5 h-5" />
								<span>+7 (989) 804 12-34</span>
							</a>
							<a 
								href="mailto:info@llymar.ru"
								class="inline-flex items-center gap-2 bg-dark-green text-light-gold px-4 py-2 rounded-full transition-colors hover:bg-dark-green/90"
								itemProp="email"
							>
								<MailIcon class="w-5 h-5" />
								<span>info@llymar.ru</span>
							</a>
						</div>
						
						<div 
							class="text-sm text-dark-green"
							itemProp="address" 
							itemScope 
							itemType="https://schema.org/PostalAddress"
						>
							<div><strong>Адрес:</strong></div>
							<div>
								<span itemProp="addressLocality">Краснодар</span>, 
								<span itemProp="addressRegion">Краснодарский край</span>
							</div>
							<div itemProp="streetAddress">ул. Примерная, 123</div>
						</div>
					</div> -->
				</div>
			</div>
		</div>
	</section>

	<GuestFooter />

	<!-- Consultation Dialog -->
	<ConsultationDialog v-model:isOpen="isConsultationDialogOpen" />

	<!-- Floating Action Buttons -->
	<Transition
		enter-active-class="transition-all duration-300 ease-out"
		leave-active-class="transition-all duration-300 ease-in"
		enter-from-class="opacity-0 translate-y-2 scale-90"
		enter-to-class="opacity-100 translate-y-0 scale-100"
		leave-from-class="opacity-100 translate-y-0 scale-100"
		leave-to-class="opacity-0 translate-y-2 scale-90"
	>
		<div v-if="showBackToTop" class="fixed bottom-6 right-6 z-40 flex flex-col gap-3">
			<!-- Mail Button -->
			<button
				@click="openConsultationDialog"
				class="bg-light-gold hover:bg-light-gold/90 text-dark-green p-3 rounded-full border-2 border-light-gold transition-all duration-300 hover:scale-110"
				:class="{ 'mail-button-pulse': mailButtonPulse }"
				aria-label="Получить консультацию"
			>
				<MailIcon class="w-6 h-6" />
			</button>
			
			<!-- Back to Top Button -->
			<button
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
/* Smooth scrolling for the entire page */
html {
	scroll-behavior: smooth;
}

/* Animation classes */
.animate-on-scroll {
	transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
}

.animate-on-scroll.animate-in {
	opacity: 1 !important;
	transform: translateY(0) !important;
}

/* Custom animations */
@keyframes fadeInUp {
	from {
		opacity: 0;
		transform: translateY(30px);
	}
	to {
		opacity: 1;
		transform: translateY(0);
	}
}

@keyframes slideInLeft {
	from {
		opacity: 0;
		transform: translateX(-30px);
	}
	to {
		opacity: 1;
		transform: translateX(0);
	}
}

/* Improved scrollbar */
::-webkit-scrollbar {
	width: 6px;
}

::-webkit-scrollbar-track {
	background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
	background: var(--light-gold);
	border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
	background: #e6c77a;
}

/* Focus styles for accessibility */
button:focus-visible,
a:focus-visible {
	outline: 2px solid var(--light-gold);
	outline-offset: 2px;
}

/* Smooth transitions */
* {
	transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;
	transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
	transition-duration: 150ms;
}

/* Mail button pulse animation */
.mail-button-pulse {
	animation: mailPulse 1s ease-in-out;
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
</style>
