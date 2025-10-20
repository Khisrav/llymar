<script setup lang="ts">
import { computed, ref } from "vue"
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from "../Components/ui/dropdown-menu"
import { Sheet, SheetContent, SheetTrigger } from "../Components/ui/sheet"
import { CircleUser, Menu, LogOut, Settings, Shield, X } from "lucide-vue-next"
import { Button } from "../Components/ui/button/"
import { Link, usePage } from "@inertiajs/vue3"
import ThemeSwitcher from "../Components/ThemeSwitcher.vue"

const page = usePage()
const { can_access_app_calculator, can_access_app_history, can_access_admin_panel, can_access_app_users, can_access_commission_credits } = page.props as any
const { user } = page.props.auth as any

const mobileMenuOpen = ref(false)

const navigationMenu = computed(() => {
    const menu = [
        { title: 'Главная', to: '/', exact: true },
    ]

    if (can_access_app_history) {
        menu.push({ title: 'История', to: '/app/history', exact: false })
    }

    menu.push({ title: 'КП', to: '/app/commercial-offers', exact: false })

    if (can_access_app_calculator) {
        menu.push({ title: 'Калькулятор', to: '/app/calculator', exact: false })
    }

    if (can_access_app_users) {
        menu.push({ title: 'Пользователи', to: '/app/users', exact: false })
    }

    if (can_access_commission_credits) {
        menu.push({ title: 'Комиссии', to: '/app/commission-credits', exact: false })
    }

    return menu
})

const username = computed(() => {
    let firstName = user.name.split(' ')[0], lastName = user.name.split(' ')[1]
    
    if (!lastName) return firstName
    return firstName + ' ' + lastName[0] + '.'
})

const userInitials = computed(() => {
    const nameParts = user.name.split(' ')
    if (nameParts.length >= 2) {
        return nameParts[0][0] + nameParts[1][0]
    }
    return nameParts[0][0]
})

const isActive = (item: { to: string, exact?: boolean }) => {
    const currentUrl = page.url
    if (item.exact) {
        return currentUrl === item.to
    }
    return currentUrl.startsWith(item.to)
}

const closeMobileMenu = () => {
    mobileMenuOpen.value = false
}
</script>

<script lang="ts">
export default {
    name: 'AuthenticatedHeaderLayout'
}
</script>

<template>
    <!-- Spacer for fixed header -->
    <div class="h-16"></div>
    
    <!-- Main Header -->
    <header class="fixed w-full top-0 flex h-16 items-center gap-4 border-b bg-background/80 backdrop-blur-xl px-4 md:px-6 z-50 transition-all duration-300">
        <!-- Logo -->
        <Link 
            href="/" 
            class="flex items-center h-8 mr-2 transition-transform hover:scale-105 active:scale-95 duration-200"
            aria-label="Перейти на главную страницу"
        >
            <img 
                src="/assets/logo.png" 
                alt="Логотип компании" 
                class="h-6 min-w-24 dark:invert transition-all duration-200"
            />
        </Link>
        
        <!-- Desktop Navigation -->
        <nav 
            class="hidden flex-col gap-6 text-lg font-medium md:flex md:flex-row md:items-center md:gap-1 md:text-sm lg:gap-2"
            aria-label="Основная навигация"
        >
            <Link 
                v-for="item in navigationMenu" 
                :key="item.title" 
                :href="item.to" 
                :class="[
                    'relative px-3 py-2 rounded-md font-medium transition-all duration-200',
                    isActive(item) 
                        ? 'text-foreground' 
                        : 'text-muted-foreground hover:bg-primary/10 hover:text-primary'
                ]"
            >
                {{ item.title }}
                <!-- Active indicator -->
                <span 
                    v-if="isActive(item)"
                    class="absolute bottom-0 left-1/2 -translate-x-1/2 w-1 h-1 rounded-full bg-primary animate-in fade-in zoom-in duration-200"
                ></span>
            </Link>
            
            <a 
                v-if="can_access_admin_panel" 
                href="/ne-tvoe-delo" 
                target="_blank"
                rel="noopener noreferrer"
                class="relative px-3 py-2 rounded-md font-medium text-muted-foreground hover:bg-primary/10 hover:text-primary transition-all duration-200 flex items-center gap-1.5"
            >Админка</a>
        </nav>
        
        <!-- Mobile Menu -->
        <Sheet v-model:open="mobileMenuOpen">
            <SheetTrigger as-child>
                <Button 
                    variant="outline" 
                    size="icon" 
                    class="shrink-0 md:hidden hover:bg-accent transition-colors"
                    aria-label="Открыть меню навигации"
                >
                    <Menu class="h-5 w-5" />
                </Button>
            </SheetTrigger>
            <SheetContent side="left" class="w-[280px] sm:w-[320px]">
                <!-- Mobile Menu Header -->
                <div class="flex items-center justify-between mb-8">
                    <Link href="/" @click="closeMobileMenu" class="flex items-center gap-2">
                        <img 
                            src="/assets/logo.png" 
                            alt="Логотип компании" 
                            class="h-6 sm:h-8 dark:invert"
                        />
                    </Link>
                </div>
                
                <!-- Mobile Navigation -->
                <nav class="flex flex-col gap-2" aria-label="Мобильная навигация">
                    <Link 
                        v-for="item in navigationMenu" 
                        :key="item.title" 
                        :href="item.to"
                        @click="closeMobileMenu"
                        :class="[
                            'px-4 py-3 rounded-lg font-medium transition-all duration-200',
                            isActive(item)
                                ? 'bg-primary text-primary-foreground shadow-sm'
                                : 'text-foreground hover:bg-primary/10 active:scale-[0.98]'
                        ]"
                    >
                        {{ item.title }}
                    </Link>
                    
                    <a 
                        v-if="can_access_admin_panel" 
                        href="/ne-tvoe-delo" 
                        target="_blank"
                        rel="noopener noreferrer"
                        class="px-4 py-3 rounded-lg font-medium text-foreground hover:bg-primary/10 transition-all duration-200 flex items-center gap-2 active:scale-[0.98]"
                    >Админка</a>
                </nav>
            </SheetContent>
        </Sheet>
        
        <!-- Right Section -->
        <div class="flex w-full items-center gap-2 md:ml-auto md:gap-3">
            <div class="ml-auto flex-1 sm:flex-initial"></div>
            
            <!-- Theme Switcher -->
            <ThemeSwitcher />
            
            <!-- User Menu -->
            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <Button 
                        variant="ghost" 
                        size="icon" 
                        class="relative rounded-full h-9 w-9 transition-all duration-200 hover:bg-primary/10"
                        aria-label="Открыть меню пользователя"
                    >
                        <!-- User Avatar with Initials -->
                        <div class="h-8 w-8 rounded-full bg-neutral-100 dark:bg-neutral-900 text-primary flex items-center justify-center font-semibold text-sm ring-2 ring-primary/20 transition-all duration-200 hover:ring-primary/40">
                            {{ userInitials }}
                        </div>
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" class="w-56">
                    <DropdownMenuLabel class="font-normal">
                        <div class="flex flex-col space-y-1">
                            <p class="text-sm font-medium leading-none">{{ username }}</p>
                            <p class="text-xs leading-none text-muted-foreground">{{ user.email }}</p>
                        </div>
                    </DropdownMenuLabel>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem as-child class="cursor-pointer">
                        <Link href="/app/account/settings" class="flex items-center gap-2 hover:bg-primary/10">
                            <Settings class="h-4 w-4" />
                            <span>Настройки</span>
                        </Link>
                    </DropdownMenuItem>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem as-child class="cursor-pointer text-destructive focus:text-destructive">
                        <Link href="/logout" class="flex items-center gap-2 hover:bg-destructive/10">
                            <LogOut class="h-4 w-4" />
                            <span>Выйти</span>
                        </Link>
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>
        </div>
    </header>
</template>
