<script setup>
import { ref } from "vue"
import { UserIcon, Menu, X, HandshakeIcon } from "lucide-vue-next"
import LandingButton from "../Components/LandingButton.vue"
import { Link } from "@inertiajs/vue3"

// Props
const props = defineProps({
	openConsultationDialog: {
		type: Function,
		required: true,
	},
	theme: {
		type: String,
		required: false,
		default: "light", //light or transparent
	}
})

const isMenuOpen = ref(false)

const menuItems = [
	{ label: "Главная", url: "/" }, 
	{ label: "Наши работы", url: "/#portfolio" }, 
	{ label: "О нас", url: "/#about" }, 
	{ label: "Стекло", url: "/#glass-types" }, 
	{ label: "Система", url: "/about-glazing-system" },
	{ label: "Статьи", url: "/articles" },
	{ label: "Контакты", url: "/#contact" },  
]

const scrollToSection = (id) => {
	const el = document.getElementById(id)
	if (el) el.scrollIntoView({ behavior: "smooth" })
	isMenuOpen.value = false
};

const handleMenuClick = (item) => {
	if (item.id) scrollToSection(item.id) 
	isMenuOpen.value = false
};

const toggleMenu = () => {
	isMenuOpen.value = !isMenuOpen.value
}
</script>

<template>
	<header class="container max-w-screen-2xl flex items-center justify-between py-4 px-4 montserrat relative" :class="props.theme === 'transparent' ? 'bg-transparent' : 'bg-gray-50'">
		<img :src="props.theme === 'transparent' ? '/assets/golden-logo-llymar.png' : '/assets/logo.png'" alt="Logo" class="h-8 md:h-12" />

		<!-- Mobile Menu Toggle -->
		<div class="lg:hidden flex flex-row items-center gap-4">
			<Link href="/auth">
				<LandingButton :variant="props.theme === 'transparent' ? 'badge' : 'outline'" size="sm" display="inline" :icon="UserIcon" :class="props.theme === 'transparent' ? 'text-white' : 'text-dark-green'"></LandingButton>
			</Link>
			<button @click="toggleMenu" class="p-2 hover:text-light-gold transition-colors z-50" :class="props.theme === 'transparent' ? 'text-white' : 'text-dark-green'">
				<Menu v-if="!isMenuOpen" class="w-8 h-8" />
				<X v-else class="w-8 h-8" />
			</button>
		</div>

		<!-- Desktop Navigation -->
		<nav class="hidden lg:block">
			<ul class="flex gap-6 items-center">
				<li v-for="item in menuItems" :key="item.label">
					<Link :href="item.url" class="hover:text-light-gold transition-colors cursor-pointer" :class="props.theme === 'transparent' ? 'text-white' : 'text-dark-green'">
						{{ item.label }}
					</Link>
				</li>

				<!-- Action buttons -->
				<li>
					<LandingButton :variant="props.theme === 'transparent' ? 'secondary' : 'outline'" size="sm" iconPosition="left" :icon="HandshakeIcon" @click="props.openConsultationDialog"> Дилерам </LandingButton>
				</li>
				<li>
					<Link href="/auth">
						<LandingButton :variant="props.theme === 'transparent' ? 'badge' : 'dark'" size="sm" iconPosition="left" :icon="UserIcon"> Войти </LandingButton>
					</Link>
				</li>
			</ul>
		</nav>

		<!-- Mobile Navigation Overlay -->
		<Transition name="fade">
			<div v-if="isMenuOpen" class="lg:hidden fixed inset-0 backdrop-blur-md z-40" @click="toggleMenu"></div>
		</Transition>

		<!-- Mobile Navigation Menu -->
		<Transition name="slide">
			<nav v-if="isMenuOpen" class="lg:hidden fixed top-0 right-0 h-full w-80 max-w-[85vw] shadow-2xl z-50 flex flex-col" :class="props.theme === 'transparent' ? 'bg-dark-green' : 'bg-gray-50'">
				<div class="py-6 px-6 flex-1">
					<ul class="flex flex-col gap-6">
						<li v-for="(item, index) in menuItems" :key="item.label" class="menu-item" :style="{ animationDelay: `${0.1 + index * 0.1}s` }">
							<Link :href="item.url" class="block text-lg hover:text-light-gold transition-colors cursor-pointer" :class="props.theme === 'transparent' ? 'text-white' : 'text-dark-green'">
								{{ item.label }}
							</Link>
						</li>

						<li class="border-t border-light-gold/20"></li>

						<li>
							<Link href="tel:+7 (989) 804 12-34" class="block text-lg font-semibold hover:text-light-gold transition-colors" :class="props.theme === 'transparent' ? 'text-white' : 'text-dark-green'"> +7 (989) 804 12-34 </Link>
						</li>
						<li class="">
							<LandingButton variant="outline" size="sm" iconPosition="left" :icon="HandshakeIcon" @click="props.openConsultationDialog" :class="props.theme === 'transparent' ? 'text-white' : 'text-dark-green'" class="block w-full"> Дилерам </LandingButton>
						</li>
						<li class="">
							<Link href="/auth">
								<LandingButton :variant="props.theme === 'transparent' ? 'badge' : 'dark'" size="sm" iconPosition="left" :icon="UserIcon" :class="props.theme === 'transparent' ? 'text-white' : 'text-dark-green'" display="block"> Войти </LandingButton>
							</Link>
						</li>
					</ul>
				</div>
			</nav>
		</Transition>
	</header>
</template>

<style scoped>
/* Animations */
.fade-enter-active,
.fade-leave-active {
	transition: opacity 0.3s;
}
.fade-enter-from,
.fade-leave-to {
	opacity: 0;
}

.slide-enter-active,
.slide-leave-active {
	transition: transform 0.3s ease;
}
.slide-enter-from {
	transform: translateX(100%);
}
.slide-leave-to {
	transform: translateX(100%);
}

.menu-item {
	opacity: 0;
	transform: translateX(20px);
	animation: slideInLeft 0.4s ease-out forwards;
}

@keyframes slideInLeft {
	to {
		opacity: 1;
		transform: translateX(0);
	}
}
</style>
