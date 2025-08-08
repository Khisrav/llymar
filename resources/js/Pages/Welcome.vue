<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import Button from '../Components/ui/button/Button.vue';
import GuestHeaderLayout from '../Layouts/GuestHeaderLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
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
    PlayIcon,
    MailIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    CalendarIcon,
    XIcon
} from 'lucide-vue-next';
import LandingBadge from '../Components/LandingBadge.vue';
import LandingButton from '../Components/LandingButton.vue';

// Portfolio data from API
const portfolio = ref([]);
const isLoadingPortfolio = ref(true);

// Fetch portfolio data from API
const fetchPortfolio = async () => {
    try {
        const response = await fetch('/api/portfolio/latest', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin'
        });

        if (response.ok) {
            const data = await response.json();
            if (data.success && data.data) {
                // Transform API data to match the expected format
                portfolio.value = data.data.map(item => ({
                    id: item.id,
                    image: item.images && item.images.length > 0 ? `/storage/${item.images[0]}` : '/assets/hero.jpg',
                    images: item.images && item.images.length > 0 ? item.images.map(img => `/storage/${img}`) : ['/assets/hero.jpg'],
                    location: item.location || 'Не указано',
                    glass: item.glass || 'Не указано',
                    profile: item.color || 'Не указано',
                    year: item.year || new Date().getFullYear(),
                    area: item.area ? `${item.area} м²` : 'Не указано',
                    type: item.title || 'Проект',
                    description: item.description || '',
                    created_at: item.created_at
                }));
            }
        } else {
            console.error('Failed to fetch portfolio data');
            // Fallback to hardcoded data if API fails
            setFallbackPortfolio();
        }
    } catch (error) {
        console.error('Error fetching portfolio:', error);
        // Fallback to hardcoded data if API fails
        setFallbackPortfolio();
    } finally {
        isLoadingPortfolio.value = false;
    }
};

// Fallback portfolio data in case API fails
const setFallbackPortfolio = () => {
    portfolio.value = [
        {
            id: 1,
            image: '/assets/hero.jpg',
            images: ['/assets/hero.jpg'],
            location: 'г. Краснодар',
            glass: 'Стекло СЕРОЕ 10мм',
            profile: 'Цвет профиля СЕРЫЙ 7024',
            year: '2024',
            area: '45 м²',
            type: 'Панорамное остекление',
            description: 'Современное панорамное остекление с использованием безрамных систем.'
        },
        {
            id: 2,
            image: '/assets/hero.jpg',
            images: ['/assets/hero.jpg'],
            location: 'г. Сочи',
            glass: 'Стекло ПРОЗРАЧНОЕ 12мм',
            profile: 'Цвет профиля БЕЛЫЙ 9010',
            year: '2024',
            area: '32 м²',
            type: 'Балконное остекление',
            description: 'Элегантное балконное остекление с максимальным проникновением света.'
        },
        {
            id: 3,
            image: '/assets/hero.jpg',
            images: ['/assets/hero.jpg'],
            location: 'г. Ростов-на-Дону',
            glass: 'Стекло ТОНИРОВАННОЕ 10мм',
            profile: 'Цвет профиля АНТРАЦИТ 7016',
            year: '2023',
            area: '68 м²',
            type: 'Терраса',
            description: 'Просторная терраса с тонированным остеклением для комфортного отдыха.'
        },
        {
            id: 4,
            image: '/assets/hero.jpg',
            images: ['/assets/hero.jpg'],
            location: 'г. Краснодар',
            glass: 'Стекло СЕРОЕ 10мм',
            profile: 'Цвет профиля СЕРЫЙ 7024',
            year: '2023',
            area: '28 м²',
            type: 'Лоджия',
            description: 'Уютная лоджия с серым остеклением в современном стиле.'
        }
    ];
};

// Features data
const features = ref([
    {
        icon: ShieldCheckIcon,
        title: 'Гарантия 10 лет',
        description: 'Полная гарантия на все виды работ и материалы'
    },
    {
        icon: ClockIcon,
        title: 'Быстрый монтаж',
        description: 'Установка от 1 до 3 дней в зависимости от сложности'
    },
    {
        icon: AwardIcon,
        title: 'Премиум качество',
        description: 'Используем только европейские материалы и фурнитуру'
    }
]);

// Stats data
const stats = ref([
    { number: '500+', label: 'Выполненных проектов' },
    { number: '10', label: 'Лет на рынке' },
    { number: '98%', label: 'Довольных клиентов' },
    { number: '24/7', label: 'Техническая поддержка' }
]);

// Services data
const services = ref([
    {
        title: 'Безрамное остекление балконов',
        description: 'Современное решение для увеличения пространства',
        image: '/assets/hero.jpg',
        price: 'от 15 000 ₽/м²'
    },
    {
        title: 'Панорамное остекление',
        description: 'Максимальный обзор и естественное освещение',
        image: '/assets/hero.jpg',
        price: 'от 18 000 ₽/м²'
    },
    {
        title: 'Остекление террас',
        description: 'Защита от непогоды с сохранением вида',
        image: '/assets/hero.jpg',
        price: 'от 20 000 ₽/м²'
    }
]);

// Reactive state
const selectedPortfolioItem = ref(null);
const isModalOpen = ref(false);
const currentTestimonial = ref(0);
const currentImageIndex = ref(0);

// Touch/swipe support
const touchStartX = ref(0);
const touchEndX = ref(0);

// Modal functionality
const openModal = (item) => {
    selectedPortfolioItem.value = item;
    currentImageIndex.value = 0;
    isModalOpen.value = true;
    document.body.style.overflow = 'hidden';
};

const closeModal = () => {
    isModalOpen.value = false;
    selectedPortfolioItem.value = null;
    currentImageIndex.value = 0;
    document.body.style.overflow = 'auto';
};

// Carousel functionality
const nextImage = () => {
    if (selectedPortfolioItem.value && selectedPortfolioItem.value.images) {
        currentImageIndex.value = (currentImageIndex.value + 1) % selectedPortfolioItem.value.images.length;
    }
};

const prevImage = () => {
    if (selectedPortfolioItem.value && selectedPortfolioItem.value.images) {
        currentImageIndex.value = currentImageIndex.value === 0 
            ? selectedPortfolioItem.value.images.length - 1 
            : currentImageIndex.value - 1;
    }
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
            nextImage(); // Swipe left - next image
        } else {
            prevImage(); // Swipe right - previous image
        }
    }
};

// Format date helper
const formatDate = (dateString) => {
    if (!dateString) return '';
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString('ru-RU', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    } catch {
        return '';
    }
};

// Smooth scroll functionality
const scrollToSection = (sectionId) => {
    const element = document.getElementById(sectionId);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth' });
    }
};

// Keyboard navigation for carousel
const handleKeydown = (event) => {
    if (!isModalOpen.value) return;
    
    switch (event.key) {
        case 'Escape':
            closeModal();
            break;
        case 'ArrowLeft':
            event.preventDefault();
            prevImage();
            break;
        case 'ArrowRight':
            event.preventDefault();
            nextImage();
            break;
    }
};

// Intersection Observer for animations
onMounted(() => {
    // Fetch portfolio data from API
    fetchPortfolio();

    // Add keyboard event listener
    document.addEventListener('keydown', handleKeydown);

    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
            }
        });
    }, observerOptions);

    document.querySelectorAll('.animate-on-scroll').forEach(el => {
        observer.observe(el);
    });
});

// Cleanup on unmount
onUnmounted(() => {
    document.removeEventListener('keydown', handleKeydown);
});

// Structured data for SEO
const structuredData = computed(() => ({
    "@context": "https://schema.org",
    "@type": "LocalBusiness",
    "name": "Llymar - Безрамные системы остекления",
    "description": "Премиальное безрамное остекление балконов, террас и панорамных окон в Краснодаре. Гарантия 10 лет, быстрый монтаж, европейское качество.",
    "url": "https://llymar.ru",
    "telephone": "+7 (989) 804 12-34",
    "address": {
        "@type": "PostalAddress",
        "addressLocality": "Краснодар",
        "addressCountry": "RU"
    },
    "geo": {
        "@type": "GeoCoordinates",
        "latitude": "45.0355",
        "longitude": "38.9753"
    },
    "openingHours": "Mo-Su 08:00-20:00",
    "priceRange": "₽₽₽",
    "aggregateRating": {
        "@type": "AggregateRating",
        "ratingValue": "4.9",
        "reviewCount": "127"
    },
    "service": [
        {
            "@type": "Service",
            "name": "Безрамное остекление балконов",
            "description": "Современное безрамное остекление балконов и лоджий"
        },
        {
            "@type": "Service", 
            "name": "Панорамное остекление",
            "description": "Панорамное остекление с максимальным обзором"
        }
    ]
}));
</script>

<template>
    <Head>
        <title>Безрамное остекление в Краснодаре | Llymar - Премиум качество от 15 000 ₽/м²</title>
        <meta name="description" content="⭐ Безрамное остекление балконов, террас и панорамных окон в Краснодаре. Гарантия 10 лет, установка за 1-3 дня. 500+ реализованных проектов. Звоните: +7 (989) 804 12-34" />
        <meta name="keywords" content="безрамное остекление, остекление балконов Краснодар, панорамное остекление, остекление террас, безрамные окна, стеклянные системы" />
        
        <!-- Open Graph / Facebook -->
        <meta property="og:type" content="website" />
        <meta property="og:url" content="https://llymar.ru/" />
        <meta property="og:title" content="Безрамное остекление в Краснодаре | Llymar - Премиум качество" />
        <meta property="og:description" content="Безрамное остекление балконов, террас и панорамных окон. Гарантия 10 лет, установка за 1-3 дня. 500+ проектов. Звоните: +7 (989) 804 12-34" />
        <meta property="og:image" content="/assets/hero.jpg" />
        <meta property="og:locale" content="ru_RU" />

        <!-- Twitter -->
        <meta property="twitter:card" content="summary_large_image" />
        <meta property="twitter:url" content="https://llymar.ru/" />
        <meta property="twitter:title" content="Безрамное остекление в Краснодаре | Llymar" />
        <meta property="twitter:description" content="Безрамное остекление балконов, террас и панорамных окон. Гарантия 10 лет, 500+ проектов." />
        <meta property="twitter:image" content="/assets/hero.jpg" />

        <!-- Additional SEO -->
        <meta name="robots" content="index, follow" />
        <meta name="author" content="Llymar" />
        <meta name="geo.region" content="RU-KDA" />
        <meta name="geo.placename" content="Краснодар" />
        <meta name="geo.position" content="45.0355;38.9753" />
        <meta name="ICBM" content="45.0355, 38.9753" />
        
        <!-- Canonical URL -->
        <link rel="canonical" href="https://llymar.ru/" />
        
    </Head>
    
    <!-- Hero Section -->
    <section class="bg-[url('/assets/hero.jpg')] bg-cover bg-center text-white relative min-h-screen overflow-hidden">
        <div class="bg-gradient-to-br from-[#23322D]/90 via-[#23322D]/80 to-[#23322D]/70 min-h-screen flex flex-col">
            <GuestHeaderLayout />
            
            <div class="container max-w-screen-2xl px-2 md:px-4 flex-1 flex flex-col justify-center">
                <div class="flex flex-col gap-6 md:gap-8 py-12 md:py-0 animate-on-scroll opacity-0 translate-y-8 transition-all duration-1000">
                    <div class="space-y-4">
                        <LandingBadge variant="gold" :icon="StarIcon">
                            Премиум качество • Гарантия 10 лет
                        </LandingBadge>    
                        
                        <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl xl:text-7xl uppercase font-light leading-tight">
                            Премиальное <br/> 
                            <span class="text-light-gold">безрамное</span> остекление
                        </h1>
                        
                        <p class="text md:text-xl text-gray-300 max-w-2xl leading-relaxed">
                            Превратите свое пространство в произведение искусства с нашими безрамными системами остекления. 
                            Максимальный обзор, минимальные рамы.
                        </p>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row items-center gap-4 md:gap-8 mt-6">
                        <LandingButton>
                            Получить консультацию
                        </LandingButton>
                        
                        <LandingButton 
                            variant="secondary" 
                            :icon="PhoneIcon" 
                            href="tel:+7 (989) 804 12-34"
                            iconPosition="left"
                        >
                            +7 (989) 804 12-34
                        </LandingButton>
                    </div>
                    
                    <!-- Stats -->
                    <div class="grid grid-cols-2 md:flex flex-col md:flex-row items-center justify-between gap-6 mt-4 md:mt-12 pt-8 border-t border-white/20">
                        <div v-for="stat in stats" :key="stat.label" class="text-center animate-on-scroll opacity-0 translate-y-4 transition-all duration-700">
                            <div class="text-2xl md:text-3xl font-bold text-light-gold montserrat">{{ stat.number }}</div>
                            <div class="text-sm text-gray-300 mt-1">{{ stat.label }}</div>
                        </div>
                        <!-- Scroll indicator -->
                        <div class="hidden md:block animate-bounce cursor-pointer" @click="scrollToSection('portfolio')">
                            <div class="flex flex-col items-center gap-2 text-white/70 hover:text-light-gold transition-colors">
                                <img src="/assets/scrolldown.svg" alt="scroll">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Features Section -->
    <section class="hidden bg-white py-16 md:py-24">
        <div class="container max-w-screen-2xl px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div v-for="(feature, index) in features" :key="feature.title" 
                     class="text-center animate-on-scroll opacity-0 translate-y-8 transition-all duration-700"
                     :style="`animation-delay: ${index * 200}ms`">
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
    <section class="hidden bg-gray-50 py-16 md:py-24">
        <div class="container max-w-screen-2xl px-4">
            <div class="text-center mb-16 animate-on-scroll opacity-0 translate-y-8 transition-all duration-700">
                <LandingBadge variant="gold" size="sm" icon="Star">
                    Наши услуги
                </LandingBadge>
                <h2 class="text-3xl md:text-4xl font-light text-dark-green mb-4">
                    Что мы предлагаем
                </h2>
                <p class="text-gray-600 max-w-2xl mx-auto">
                    Полный спектр услуг по безрамному остеклению с использованием премиальных материалов
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div v-for="(service, index) in services" :key="service.title" 
                     class="group bg-white rounded-2xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-500 animate-on-scroll opacity-0 translate-y-8"
                     :style="`animation-delay: ${index * 200}ms`">
                    <div class="aspect-video bg-cover bg-center relative overflow-hidden" 
                         :style="`background-image: url(${service.image})`">
                        <div class="absolute inset-0 bg-dark-green/60 group-hover:bg-dark-green/40 transition-colors"></div>
                        <div class="absolute top-4 right-4 bg-light-gold text-dark-green px-3 py-1 rounded-full text-sm font-semibold">
                            {{ service.price }}
                        </div>
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-dark-green mb-3">{{ service.title }}</h3>
                        <p class="text-gray-600 mb-4">{{ service.description }}</p>
                        <button class="text-dark-green hover:text-light-gold font-medium flex items-center gap-2 group-hover:gap-3 transition-all">
                            Подробнее
                            <ArrowRightIcon class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Portfolio Section -->
    <section id="portfolio" class="bg-dark-green text-white py-16 md:py-24">
        <div class="container max-w-screen-2xl px-4">
            <div class="flex flex-col gap-8 mb-16">
                <div class="animate-on-scroll opacity-0 translate-y-8 transition-all duration-700">
                    <LandingBadge>
                        Примеры работ
                    </LandingBadge>
                    <h4 class="text-3xl md:text-4xl font-light mb-4">
                        Результат говорит сам за себя
                    </h4>
                    <p class="text-gray-300">
                        Каждый проект — это уникальное решение, созданное с учетом пожеланий клиента и особенностей объекта
                    </p>
                </div>
            </div>
            
            <!-- Loading State -->
            <div v-if="isLoadingPortfolio" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div v-for="n in 4" :key="n" class="animate-pulse">
                    <div class="aspect-square bg-gray-700 rounded-2xl mb-4"></div>
                    <div class="space-y-2">
                        <div class="h-4 bg-gray-700 rounded w-3/4"></div>
                        <div class="h-3 bg-gray-700 rounded w-1/2"></div>
                        <div class="h-3 bg-gray-700 rounded w-2/3"></div>
                    </div>
                </div>
            </div>

            <!-- Portfolio Items -->
            <div v-else-if="portfolio.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div v-for="(item, index) in portfolio" :key="item.id" 
                     class="group cursor-pointer translate-y-8 transition-all duration-700"
                     :style="`animation-delay: ${index * 150}ms`"
                     @click="openModal(item)">
                    <div class="relative overflow-hidden rounded-2xl">
                        <div class="aspect-square bg-cover bg-center transition-transform duration-500 group-hover:scale-110" 
                             :style="`background-image: url(${item.image})`">
                        </div>
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            <div class="absolute bottom-4 left-4 right-4">
                                <div class="text-light-gold text-sm font-medium">{{ item.type }}</div>
                                <div class="text-white text-lg font-semibold">{{ item.area }}</div>
                            </div>
                        </div>
                        <div class="absolute top-4 right-4 bg-white/20 backdrop-blur-sm rounded-full p-2 opacity-0 group-hover:opacity-100 transition-all duration-300 hover:bg-light-gold/20">
                            <PlayIcon class="w-5 h-5 text-white" />
                        </div>
                    </div>
                    
                    <div class="flex flex-col gap-3 mt-4 montserrat">
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
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-16">
                <div class="text-gray-400 text-lg mb-4">Портфолио временно недоступно</div>
                <p class="text-gray-500">Мы работаем над обновлением наших проектов</p>
            </div>
            
            <!-- CTA -->
            <div id="consultation" class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-16 mt-16 border-t border-white/20 animate-on-scroll opacity-0 translate-y-8 transition-all duration-700">
                <div class="text-center max-w-2xl">
                    <h3 class="text-2xl md:text-3xl font-light mb-4">
                        Готовы обсудить ваш проект?
                    </h3>
                    <p class="text-gray-300 mb-6">
                        Закажите бесплатный расчет с чертежами, спецификацией и стоимостью. 
                        Наш специалист приедет к вам в удобное время.
                    </p>
                </div>
                
                <div>
                    <div class="flex flex-col justify-center sm:flex-row gap-4 w-full">
                        <div>
                            <button class="group bg-light-gold hover:bg-light-gold/90 transition-all duration-300 flex items-center justify-center gap-3 text-black py-4 px-6 font-semibold rounded-full text-base flex-1 hover:shadow-lg hover:shadow-light-gold/25 hover:scale-105">
                                <span class="montserrat">Бесплатный расчет</span>
                                <img src="/assets/arrow.svg" alt="arrow" class="group-hover:translate-x-1 transition-transform">
                            </button>
                        </div>
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
    
    <section class="bg-light-gold text-dark-green py-16 md:py-24">
        <div class="container max-w-screen-2xl px-4 text-center">
            <div class="flex flex-col gap-8 mb-16">
                <div class="animate-on-scroll opacity-0 space-y-4 translate-y-8 transition-all duration-700">
                    <LandingBadge variant="dark">
                        Свяжитесь с нами
                    </LandingBadge>
                    <h4 class="text-3xl md:text-4xl font-light mb-4">Проконсультируем и обсудим детали</h4>
                </div>
                <div class="flex flex-col md:flex-row items-center justify-center gap-4">
                    <LandingButton variant="dark" showArrow>
                        Получить консультацию
                    </LandingButton>
                    <span>или</span>
                    <div class="flex flex-row gap-4">
                        <LandingButton variant="dark" size="icon" :icon="PhoneIcon"></LandingButton>
                        <LandingButton variant="dark" size="icon" :icon="MailIcon"></LandingButton>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Portfolio Modal with Carousel -->
    <Transition
        enter-active-class="transition-opacity duration-300"
        leave-active-class="transition-opacity duration-300"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-0 sm:p-4">
            <div class="absolute inset-0 bg-black/90 backdrop-blur-sm" @click="closeModal"></div>
            
            <div class="relative overflow-y-scroll bg-white sm:rounded-2xl max-w-6xl w-full overflow-hidden flex flex-col modal-content">
                <!-- Close Button -->
                <button 
                    @click="closeModal"
                    class="absolute top-3 right-3 sm:top-4 sm:right-4 z-20 bg-black/60 hover:bg-black/80 text-white rounded-full p-2 transition-all hover:scale-110"
                >
                    <XIcon class="w-5 h-5 sm:w-6 sm:h-6" />
                </button>
                
                <div v-if="selectedPortfolioItem" class="flex flex-col h-full">
                    <!-- Image Carousel Section -->
                    <div class="relative flex-shrink-0 h-64 sm:h-80 md:h-96 bg-gray-900">
                        <!-- Main Image -->
                        <div 
                            class="relative w-full h-full overflow-hidden"
                            @touchstart="handleTouchStart"
                            @touchend="handleTouchEnd"
                        >
                            <img 
                                :src="selectedPortfolioItem.images[currentImageIndex]" 
                                :alt="selectedPortfolioItem.type"
                                class="w-full h-full object-cover carousel-image select-none"
                                draggable="false"
                            />
                            
                            <!-- Image Counter -->
                            <div class="absolute top-4 left-4 bg-black/60 text-white px-3 py-1 rounded-full text-sm">
                                {{ currentImageIndex + 1 }} / {{ selectedPortfolioItem.images.length }}
                            </div>
                        </div>
                        
                        <!-- Navigation Arrows (only show if more than 1 image) -->
                        <template v-if="selectedPortfolioItem.images.length > 1">
                            <button 
                                @click="prevImage"
                                class="absolute left-2 sm:left-4 top-1/2 -translate-y-1/2 bg-black/50 hover:bg-black/70 text-white rounded-full p-2 sm:p-3 transition-all hover:scale-110"
                            >
                                <ChevronLeftIcon class="w-5 h-5 sm:w-6 sm:h-6" />
                            </button>
                            <button 
                                @click="nextImage"
                                class="absolute right-2 sm:right-4 top-1/2 -translate-y-1/2 bg-black/50 hover:bg-black/70 text-white rounded-full p-2 sm:p-3 transition-all hover:scale-110"
                            >
                                <ChevronRightIcon class="w-5 h-5 sm:w-6 sm:h-6" />
                            </button>
                        </template>
                        
                        <!-- Thumbnail Navigation (only show if more than 1 image) -->
                        <div v-if="selectedPortfolioItem.images.length > 1" class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2 max-w-full overflow-x-auto px-4">
                            <button
                                v-for="(image, index) in selectedPortfolioItem.images"
                                :key="index"
                                @click="goToImage(index)"
                                class="flex-shrink-0 w-12 h-8 sm:w-16 sm:h-10 rounded border-2 overflow-hidden carousel-thumbnail"
                                :class="currentImageIndex === index ? 'border-light-gold' : 'border-white/50 hover:border-white'"
                            >
                                <img :src="image" :alt="`Thumbnail ${index + 1}`" class="w-full h-full object-cover" />
                            </button>
                        </div>
                    </div>
                    
                    <!-- Content Section -->
                    <div class="flex-1 overflow-y-auto p-4 sm:p-6 modal-scroll">
                        <!-- Header -->
                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4 mb-6">
                            <div class="flex-1">
                                <h3 class="text-xl sm:text-2xl lg:text-3xl font-semibold text-dark-green mb-2">
                                    {{ selectedPortfolioItem.type }}
                                </h3>
                                <div class="flex flex-wrap items-center gap-2 text-sm sm:text-base text-gray-600">
                                    <MapPinIcon class="w-4 h-4 text-light-gold" />
                                    <span>{{ selectedPortfolioItem.location }}</span>
                                    <span class="text-gray-400">•</span>
                                    <CalendarIcon class="w-4 h-4 text-light-gold" />
                                    <span>{{ selectedPortfolioItem.year }}</span>
                                    <template v-if="selectedPortfolioItem.created_at">
                                        <span class="text-gray-400">•</span>
                                        <span class="text-gray-500">{{ formatDate(selectedPortfolioItem.created_at) }}</span>
                                    </template>
                                </div>
                            </div>
                            <div class="text-center sm:text-right bg-gray-50 p-3 rounded-lg">
                                <div class="text-2xl sm:text-3xl font-bold text-dark-green">{{ selectedPortfolioItem.area }}</div>
                                <div class="text-xs sm:text-sm text-gray-500">площадь остекления</div>
                            </div>
                        </div>
                        
                        <!-- Description -->
                        <div v-if="selectedPortfolioItem.description" class="mb-6">
                            <h4 class="text-lg font-semibold text-dark-green mb-3">Описание проекта</h4>
                            <p class="text-gray-700 leading-relaxed">{{ selectedPortfolioItem.description }}</p>
                        </div>
                        
                        <!-- Technical Details -->
                        <div class="mb-6">
                            <h4 class="text-lg font-semibold text-dark-green mb-4">Технические характеристики</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                                    <RectangleVerticalIcon class="text-light-gold w-5 h-5 flex-shrink-0" />
                                    <div>
                                        <div class="text-sm text-gray-500">Тип стекла</div>
                                        <div class="font-medium">{{ selectedPortfolioItem.glass }}</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                                    <PaletteIcon class="text-light-gold w-5 h-5 flex-shrink-0" />
                                    <div>
                                        <div class="text-sm text-gray-500">Цвет профиля</div>
                                        <div class="font-medium">{{ selectedPortfolioItem.profile }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="flex flex-col sm:flex-row gap-3 sm:gap-4">
                            <button class="bg-dark-green hover:bg-dark-green/90 text-white px-6 py-3 rounded-full font-medium transition-all hover:shadow-lg flex-1 sm:flex-none">
                                Заказать похожий проект
                            </button>
                            <button class="border border-dark-green text-dark-green hover:bg-dark-green hover:text-white px-6 py-3 rounded-full font-medium transition-all flex-1 sm:flex-none">
                                Получить консультацию
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
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

/* Carousel specific styles */
.carousel-image {
    transition: transform 0.3s ease-in-out, opacity 0.3s ease-in-out;
}

.carousel-thumbnail {
    transition: all 0.2s ease-in-out;
}

.carousel-thumbnail:hover {
    transform: scale(1.05);
}

/* Modal responsive improvements */
@media (max-width: 640px) {
    .modal-content {
        max-height: calc(100vh);
    }
}

/* Smooth transitions */
* {
    transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}

/* Custom scrollbar for modal content */
.modal-scroll::-webkit-scrollbar {
    width: 4px;
}

.modal-scroll::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 2px;
}

.modal-scroll::-webkit-scrollbar-thumb {
    background: var(--light-gold);
    border-radius: 2px;
}

.modal-scroll::-webkit-scrollbar-thumb:hover {
    background: #e6c77a;
}
</style>