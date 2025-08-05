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
</script>

<template>
    <header class="container max-w-screen-2xl flex flex-row items-center justify-between py-4 px-4 montserrat relative">
        <img src="/assets/golden-logo-llymar.png" alt="" class="h-8 md:h-12" :class="{'z-50': isMenuOpen}">
        
        <!-- Mobile Menu Button -->
        <button 
            @click="isMenuOpen = !isMenuOpen"
            class="lg:hidden p-2 text-white hover:text-light-gold transition-colors z-50 relative"
        >
            <Menu v-if="!isMenuOpen" class="w-6 h-6" />
            <X v-else class="w-6 h-6" />
        </button>
        
        <!-- Desktop Navigation -->
        <nav class="hidden lg:block">
            <ul class="flex flex-row gap-6 items-center">
                <li><a href="" class="hover:text-light-gold transition-colors">Главная</a></li>
                <li><a href="" class="hover:text-light-gold transition-colors">Наши работы</a></li>
                <li><a href="" class="hover:text-light-gold transition-colors">О нас</a></li>
                
                <li><a href="tel:+7 (123) 456 67-89" class="font-semibold underline hover:text-light-gold transition-colors">+7 (123) 456 67-89</a></li>
                <li>
                    <a href="/auth" class="border border-light-gold text-light-gold hover:bg-light-gold hover:text-black transition-colors rounded-full px-3 py-2 inline-block flex items-center gap-2 text-sm">
                        <UserIcon class="w-4 h-4"/>
                        <span class="font-medium">Войти</span>
                    </a>
                </li>
            </ul>
        </nav>

        

        <!-- Mobile Navigation Overlay -->
        <nav 
            v-show="isMenuOpen" 
            class="lg:hidden fixed inset-0 bg-opacity-75 backdrop-blur-md z-40 flex flex-col"
        >
            <!-- Menu content with padding from top to account for header -->
            <div class="pt-20 px-6 flex-1">
                <ul class="flex flex-col gap-6">
                    <li><a href="" @click="isMenuOpen = false" class="block py-3 text-lg hover:text-light-gold transition-colors">Главная</a></li>
                    <li><a href="" @click="isMenuOpen = false" class="block py-3 text-lg hover:text-light-gold transition-colors">Наши работы</a></li>
                    <li><a href="" @click="isMenuOpen = false" class="block py-3 text-lg hover:text-light-gold transition-colors">О нас</a></li>
                    
                    <li class="pt-6 mt-6 border-t border-light-gold/20">
                        <a href="tel:+7 (123) 456 67-89" @click="isMenuOpen = false" class="block py-3 text-lg font-semibold underline hover:text-light-gold transition-colors">+7 (123) 456 67-89</a>
                    </li>
                    <li class="mt-6">
                        <a href="/auth" @click="isMenuOpen = false" class="border border-light-gold text-light-gold hover:bg-light-gold hover:text-black transition-colors rounded-full px-6 py-4 inline-block flex items-center justify-center gap-3 text-base w-full max-w-xs mx-auto">
                            <UserIcon class="w-5 h-5"/>
                            <span class="font-medium">Войти</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </header>
</template>

<style>
body {
    font-family: Montserrat, "Open Sans", sans-serif !important;
}
</style>