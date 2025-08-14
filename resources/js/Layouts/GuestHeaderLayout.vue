<script setup>
import { ref } from 'vue';
import Button from '@/Components/ui/button/Button.vue';
import NavigationMenu from '../Components/ui/navigation-menu/NavigationMenu.vue';
import NavigationMenuList from '../Components/ui/navigation-menu/NavigationMenuList.vue';
import NavigationMenuItem from '../Components/ui/navigation-menu/NavigationMenuItem.vue';
import NavigationMenuLink from '../Components/ui/navigation-menu/NavigationMenuLink.vue';
import { Link } from '@inertiajs/vue3';
import { UserIcon, Menu, X } from 'lucide-vue-next';

const isMenuOpen = ref(false)

// Smooth scroll functionality
const scrollToSection = (sectionId) => {
    const element = document.getElementById(sectionId);
    if (element) {
        element.scrollIntoView({ behavior: "smooth" });
    }
    // Close mobile menu after navigation
    isMenuOpen.value = false;
};
</script>

<template>
    <header class="container max-w-screen-2xl flex flex-row items-center justify-between py-4 px-4 montserrat relative">
        <img src="/assets/golden-logo-llymar.png" alt="" class="h-8 md:h-12">
        
        <!-- Mobile Menu Button -->
        <button 
            @click="isMenuOpen = !isMenuOpen"
            class="lg:hidden p-2 text-white hover:text-light-gold transition-colors z-50 relative"
        >
            <Menu class="w-8 h-8" />
        </button>
        
        <!-- Desktop Navigation -->
        <nav class="hidden lg:block">
            <ul class="flex flex-row gap-6 items-center">
                <li><a href="#hero" @click.prevent="scrollToSection('hero')" class="hover:text-light-gold transition-colors cursor-pointer">Главная</a></li>
                <li><a href="#portfolio" @click.prevent="scrollToSection('portfolio')" class="hover:text-light-gold transition-colors cursor-pointer">Наши работы</a></li>
                <li><a href="#about" @click.prevent="scrollToSection('about')" class="hover:text-light-gold transition-colors cursor-pointer">О нас</a></li>
                <li><a href="#glass-types" @click.prevent="scrollToSection('glass-types')" class="hover:text-light-gold transition-colors cursor-pointer">Стекло</a></li>
                <li><a href="#contact" @click.prevent="scrollToSection('contact')" class="hover:text-light-gold transition-colors cursor-pointer">Контакты</a></li>
                
                <!-- <li><a href="tel:+7 (989) 804 12-34" class="font-semibold underline hover:text-light-gold transition-colors">+7 (989) 804 12-34</a></li> -->
                <li>
                    <a href="/auth" class="border border-light-gold text-light-gold hover:bg-light-gold hover:text-black transition-colors rounded-full px-3 py-2 inline-block flex items-center gap-2 text-sm">
                        <UserIcon class="w-4 h-4"/>
                        <span class="font-medium">Войти</span>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Mobile Navigation Overlay with Transition -->
        <Transition
            enter-active-class="transition-all duration-300 ease-out"
            leave-active-class="transition-all duration-300 ease-in"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div 
                v-if="isMenuOpen"
                class="lg:hidden fixed inset-0 backdrop-blur-md z-40"
                @click="isMenuOpen = false"
            >
                <X class="w-8 h-8 ml-4 mt-4"/>
            </div>
        </Transition>

        <!-- Mobile Navigation Menu with Slide Animation -->
        <Transition
            enter-active-class="transition-transform duration-300 ease-out"
            leave-active-class="transition-transform duration-300 ease-in"
            enter-from-class="transform translate-x-full"
            enter-to-class="transform translate-x-0"
            leave-from-class="transform translate-x-0"
            leave-to-class="transform translate-x-full"
        >
            <nav 
                v-if="isMenuOpen"
                class="lg:hidden fixed top-0 right-0 h-full w-80 max-w-[85vw] bg-dark-green shadow-2xl z-50 flex flex-col"
                @click.stop
            >
                <!-- Menu content with padding from top to account for header -->
                <div class="py-6 px-6 flex-1">
                    <ul class="flex flex-col gap-2">
                        <li class="menu-item" style="animation-delay: 0.1s">
                            <a href="#hero" @click.prevent="scrollToSection('hero')" class="block py-4 text-lg hover:text-light-gold transition-colors border-b border-gray-700/50 hover:border-light-gold/30 cursor-pointer">
                                Главная
                            </a>
                        </li>
                        <li class="menu-item" style="animation-delay: 0.2s">
                            <a href="#portfolio" @click.prevent="scrollToSection('portfolio')" class="block py-4 text-lg hover:text-light-gold transition-colors border-b border-gray-700/50 hover:border-light-gold/30 cursor-pointer">
                                Наши работы
                            </a>
                        </li>
                        <li class="menu-item" style="animation-delay: 0.3s">
                            <a href="#about" @click.prevent="scrollToSection('about')" class="block py-4 text-lg hover:text-light-gold transition-colors border-b border-gray-700/50 hover:border-light-gold/30 cursor-pointer">
                                О нас
                            </a>
                        </li>
                        <li class="menu-item" style="animation-delay: 0.35s">
                            <a href="#glass-types" @click.prevent="scrollToSection('glass-types')" class="block py-4 text-lg hover:text-light-gold transition-colors border-b border-gray-700/50 hover:border-light-gold/30 cursor-pointer">
                                Стекло
                            </a>
                        </li>
                        <li class="menu-item" style="animation-delay: 0.4s">
                            <a href="#contact" @click.prevent="scrollToSection('contact')" class="block py-4 text-lg hover:text-light-gold transition-colors border-b border-gray-700/50 hover:border-light-gold/30 cursor-pointer">
                                Контакты
                            </a>
                        </li>
                        
                        <li class="pt-6 mt-6 border-t border-light-gold/20 menu-item" style="animation-delay: 0.5s">
                            <a href="tel:+7 (989) 804 12-34" @click="isMenuOpen = false" class="block py-3 text-lg font-semibold underline hover:text-light-gold transition-colors">
                                +7 (989) 804 12-34
                            </a>
                        </li>
                        <li class="mt-6 menu-item" style="animation-delay: 0.6s">
                            <a href="/auth" @click="isMenuOpen = false" class="border border-light-gold text-light-gold hover:bg-light-gold hover:text-black transition-all duration-300 rounded-full px-6 py-4 inline-block flex items-center justify-center gap-3 text-base w-full max-w-xs mx-auto hover:shadow-lg hover:shadow-light-gold/20">
                                <UserIcon class="w-5 h-5"/>
                                <span class="font-medium">Войти</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
        </Transition>
    </header>
</template>

<style scoped>
body {
    font-family: Montserrat, "Open Sans", sans-serif !important;
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

/* Prevent body scroll when menu is open */
.menu-open {
    overflow: hidden;
}
</style>