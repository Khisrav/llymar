<script setup>
import { ref } from 'vue';
import Button from '@/Components/ui/button/Button.vue';
import NavigationMenu from '../Components/ui/navigation-menu/NavigationMenu.vue';
import NavigationMenuList from '../Components/ui/navigation-menu/NavigationMenuList.vue';
import NavigationMenuItem from '../Components/ui/navigation-menu/NavigationMenuItem.vue';
import NavigationMenuLink from '../Components/ui/navigation-menu/NavigationMenuLink.vue';
import { Link } from '@inertiajs/vue3';

const isMenuOpen = ref(false)
</script>

<template>
    <header class="z-20">
        <nav class="bg-white border-gray-200 px-4 lg:px-6 py-2.5 dark:bg-gray-800">
            <div class="flex flex-wrap justify-between items-center mx-auto max-w-screen-xl">
                <a href="https://flowbite.com" class="flex items-center">
                    <span class="self-center text-xl font-semibold whitespace-nowrap dark:text-white">LLYMAR</span>
                </a>
                <div class="flex items-center lg:order-2">
                    <Link href="/auth"><Button>Войти</Button></Link>
                    <Button @click="isMenuOpen = !isMenuOpen" variant="outline" size="icon" class="ml-2 lg:hidden">
                        <svg v-if="!isMenuOpen" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd"
                                d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"
                                clip-rule="evenodd"></path>
                        </svg>
                        <svg v-else class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"
                            xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd"></path>
                        </svg>
                    </Button>
                </div>
                <div :class="{'hidden': !isMenuOpen, 'flex': isMenuOpen}" class="justify-between items-center w-full lg:flex lg:w-auto lg:order-1" id="mobile-menu-2">
                    <NavigationMenu>
                        <NavigationMenuList>
                            <NavigationMenuItem>
                                <NavigationMenuLink>Link</NavigationMenuLink>
                            </NavigationMenuItem>
                        </NavigationMenuList>
                    </NavigationMenu>
                </div>
            </div>
        </nav>
    </header>
</template>
