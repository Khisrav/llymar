<script setup lang="ts">
import { computed, ref, onMounted, onUnmounted } from "vue"
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from "../Components/ui/dropdown-menu"
import { Sheet, SheetContent, SheetTrigger } from "../Components/ui/sheet"
import { 
    CircleUser, 
    Menu, 
    LogOut, 
    Settings, 
    Shield, 
    X, 
    Calculator, 
    Building2Icon,
    ShoppingCart,
    FileText,
    Users,
    DollarSign,
    LayoutDashboard,
    Send
} from "lucide-vue-next"
import { Button } from "../Components/ui/button/"
import { Link, usePage } from "@inertiajs/vue3"
import ThemeSwitcher from "../Components/ThemeSwitcher.vue"

const page = usePage()
const { 
    can_access_app_calculator, 
    can_access_app_history, 
    can_access_admin_panel, 
    can_access_app_users, 
    can_access_commission_credits,
    can_access_companies,
} = page.props as any
const { user } = page.props.auth as any

const mobileMenuOpen = ref(false)
const sidebarExpanded = ref(false)
const sidebarCollapsed = ref(true) // Delayed state for layout changes
let collapseTimeout: ReturnType<typeof setTimeout> | null = null

// Add body padding for sidebar on mount
onMounted(() => {
    document.body.classList.add('has-sidebar')
})

// Remove body padding on unmount
onUnmounted(() => {
    document.body.classList.remove('has-sidebar')
    if (collapseTimeout) clearTimeout(collapseTimeout)
})

const navigationItems = computed(() => {
    const items = []

    if (can_access_app_history) {
        items.push({ 
            title: 'Заказы', 
            to: '/app/history', 
            exact: false,
            icon: ShoppingCart 
        })
    }

    if (can_access_app_calculator) {
        items.push({ 
            title: 'Калькулятор', 
            to: '/app/calculator', 
            exact: false,
            icon: Calculator 
        })
    }

    items.push({ 
        title: 'КП', 
        to: '/app/commercial-offers', 
        exact: false,
        icon: FileText 
    })

    if (can_access_app_users) {
        items.push({ 
            title: 'Пользователи', 
            to: '/app/users', 
            exact: false,
            icon: Users 
        })
    }

    if (can_access_commission_credits) {
        items.push({ 
            title: 'Комиссии', 
            to: '/app/commission-credits', 
            exact: false,
            icon: DollarSign 
        })
    }

    if (can_access_admin_panel) {
        items.push({ 
            title: 'Админка', 
            to: '/ne-tvoe-delo', 
            exact: false,
            icon: LayoutDashboard,
            external: true 
        })
    }

    return items
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

const handleSidebarMouseEnter = () => {
    sidebarExpanded.value = true
    if (collapseTimeout) {
        clearTimeout(collapseTimeout)
        collapseTimeout = null
    }
    sidebarCollapsed.value = false
}

const handleSidebarMouseLeave = () => {
    sidebarExpanded.value = false
    // Delay the layout changes until after animation completes (300ms)
    collapseTimeout = setTimeout(() => {
        sidebarCollapsed.value = true
    }, 300)
}
</script>

<script lang="ts">
export default {
    name: 'AuthenticatedHeaderLayout'
}
</script>

<template>
    <!-- Mobile Header -->
    <header class="md:hidden fixed w-full top-0 flex h-16 items-center gap-4 border-b bg-background/95 backdrop-blur-xl px-4 z-50">
        <!-- Mobile Menu Toggle -->
        <Sheet v-model:open="mobileMenuOpen">
            <SheetTrigger as-child>
                <Button 
                    variant="outline" 
                    size="icon" 
                    class="shrink-0 hover:bg-accent transition-colors"
                    aria-label="Открыть меню навигации"
                >
                    <Menu class="h-5 w-5" />
                </Button>
            </SheetTrigger>
            <SheetContent side="left" class="w-[280px] sm:w-[320px] p-0">
                <!-- Mobile Logo -->
                <div class="p-6 border-b">
                    <Link href="/" @click="closeMobileMenu" class="flex items-center">
                        <img 
                            src="/assets/logo.png" 
                            alt="Логотип компании" 
                            class="h-7 dark:invert"
                        />
                    </Link>
                </div>
                
                <!-- Mobile Navigation -->
                <nav class="flex flex-col p-3 gap-1" aria-label="Мобильная навигация">
                    <template v-for="item in navigationItems" :key="item.title">
                        <a 
                            v-if="item.external"
                            :href="item.to"
                            target="_blank"
                            rel="noopener noreferrer"
                            :class="[
                                'flex items-center gap-3 px-4 py-3 rounded-lg font-medium transition-all duration-200',
                                'text-foreground hover:bg-muted'
                            ]"
                        >
                            <component :is="item.icon" class="h-5 w-5" />
                            {{ item.title }}
                        </a>
                        <Link 
                            v-else
                            :href="item.to"
                            @click="closeMobileMenu"
                            :class="[
                                'flex items-center gap-3 px-4 py-3 rounded-lg font-medium transition-all duration-200',
                                isActive(item)
                                    ? 'bg-muted text-primary'
                                    : 'text-foreground hover:bg-muted'
                            ]"
                        >
                            <component :is="item.icon" class="h-5 w-5" />
                            {{ item.title }}
                        </Link>
                    </template>
                </nav>

                <!-- Mobile Bottom Actions -->
                <div class="absolute bottom-0 left-0 right-0 p-3 border-t bg-background">
                    <div class="flex flex-col gap-2">
                        <Link 
                            href="/app/account/settings"
                            @click="closeMobileMenu"
                            class="flex items-center gap-3 px-4 py-3 rounded-lg font-medium text-foreground hover:bg-muted transition-all"
                        >
                            <Settings class="h-5 w-5" />
                            <span>Настройки</span>
                        </Link>
                        <Link 
                            v-if="can_access_companies"
                            href="/app/companies"
                            @click="closeMobileMenu"
                            class="flex items-center gap-3 px-4 py-3 rounded-lg font-medium text-foreground hover:bg-muted transition-all"
                        >
                            <Building2Icon class="h-5 w-5" />
                            <span>Компании</span>
                        </Link>
                        <a 
                            href="https://t.me/LLymar"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="flex items-center gap-3 px-4 py-3 rounded-lg font-medium text-foreground hover:bg-muted transition-all"
                        >
                            <Send class="h-5 w-5" />
                            <span>Telegram</span>
                        </a>
                        <Link 
                            href="/logout"
                            class="flex items-center gap-3 px-4 py-3 rounded-lg font-medium text-destructive hover:bg-destructive/10 transition-all"
                        >
                            <LogOut class="h-5 w-5" />
                            <span>Выйти</span>
                        </Link>
                    </div>
                </div>
            </SheetContent>
        </Sheet>
        
        <!-- Mobile Logo -->
        <Link href="/" class="flex items-center">
            <img 
                src="/assets/logo.png" 
                alt="Логотип компании" 
                class="h-6 dark:invert"
            />
        </Link>

        <!-- Mobile User Avatar -->
        <div class="ml-auto flex items-center gap-2">
            <ThemeSwitcher />
            <div class="h-9 w-9 rounded-full bg-primary/10 text-primary flex items-center justify-center font-semibold text-sm ring-2 ring-primary/20">
                {{ userInitials }}
            </div>
        </div>
    </header>

    <!-- Mobile Spacer -->
    <div class="h-16 md:hidden"></div>
    
    <!-- Desktop Sidebar -->
    <aside 
        @mouseenter="handleSidebarMouseEnter"
        @mouseleave="handleSidebarMouseLeave"
        :class="[
            'hidden md:flex fixed left-0 top-0 h-full flex-col border-r bg-background/70 shadow-2xl shadow-muted-foreground/20 backdrop-blur z-50 transition-all duration-300 ease-in-out',
            sidebarExpanded ? 'w-64' : 'w-16'
        ]"
        aria-label="Главная навигация"
    >
        <!-- Logo Section -->
        <div class="h-16 flex items-center justify-center border-b px-3 overflow-hidden">
            <Link 
                href="/" 
                class="flex items-center justify-center min-w-0"
                aria-label="Перейти на главную страницу"
            >
                <img 
                    src="/assets/logo.png" 
                    alt="Логотип компании" 
                    :class="[
                        'dark:invert transition-all duration-300 ease-in-out max-h-6 w-auto',
                        // sidebarCollapsed ? 'h-8 w-8 object-cover' : 'h-8 w-auto'
                    ]"
                />
            </Link>
        </div>

        <!-- Navigation Section -->
        <nav class="flex-1 overflow-y-auto overflow-x-hidden py-4 px-2" aria-label="Основная навигация">
            <div class="flex flex-col gap-1">
                <template v-for="item in navigationItems" :key="item.title">
                    <a 
                        v-if="item.external"
                        :href="item.to"
                        target="_blank"
                        rel="noopener noreferrer"
                        :class="[
                            'group relative flex items-center gap-4 px-3 py-2.5 rounded-lg transition-all duration-300 ease-in-out overflow-hidden',
                            'text-muted-foreground hover:bg-muted'
                        ]"
                        :aria-label="item.title"
                    >
                        <!-- Icon -->
                        <component 
                            :is="item.icon" 
                            class="h-5 w-5 ml-[2px] flex-shrink-0"
                        />
                        
                        <!-- Text Label -->
                        <span 
                            :class="[
                                'text-sm font-medium whitespace-nowrap transition-all duration-300 ease-in-out',
                            ]"
                        >
                            {{ item.title }}
                        </span>
                    </a>
                    <Link 
                        v-else
                        :href="item.to"
                        :class="[
                            'group relative flex items-center gap-4 px-3 py-2.5 rounded-lg transition-all duration-300 ease-in-out overflow-hidden',
                            isActive(item) 
                                ? 'bg-muted text-primary' 
                                : 'text-muted-foreground hover:bg-muted'
                        ]"
                        :aria-label="item.title"
                    >
                        <!-- Icon -->
                        <component 
                            :is="item.icon" 
                            class="h-5 w-5 ml-[2px] flex-shrink-0"
                        />
                        
                        <!-- Text Label -->
                        <span 
                            :class="[
                                'text-sm font-medium whitespace-nowrap transition-all duration-300 ease-in-out',
                            ]"
                        >
                            {{ item.title }}
                        </span>
                    </Link>
                </template>
            </div>
        </nav>

        <!-- Bottom Section -->
        <div class="border-t px-2 py-4">
            <div class="flex flex-col gap-1">
                <!-- Settings -->
                <Link 
                    href="/app/account/settings"
                    :class="[
                        'flex items-center gap-4 rounded-lg px-3 py-2.5 text-muted-foreground hover:bg-muted hover:text-muted-foreground transition-all duration-300 ease-in-out overflow-hidden',
                        // sidebarCollapsed && 'justify-center',
                        isActive({ to: '/app/account/settings', exact: false }) && 'bg-muted text-muted-foreground'
                    ]"
                    aria-label="Настройки"
                >
                    <Settings class="h-5 w-5 ml-[2px] flex-shrink-0" />
                    <span 
                        :class="[
                            'text-sm font-medium whitespace-nowrap transition-all duration-300 ease-in-out',
                            // sidebarExpanded ? 'opacity-100 translate-x-0' : 'opacity-0 -translate-x-4 absolute'
                        ]"
                    >
                        Настройки
                    </span>
                </Link>

                <!-- Companies -->
                <Link 
                    v-if="can_access_companies"
                    href="/app/companies"
                    :class="[
                        'flex items-center gap-4 rounded-lg px-3 py-2.5 text-muted-foreground hover:bg-muted hover:text-muted-foreground transition-all duration-300 ease-in-out overflow-hidden',
                        // sidebarCollapsed && 'justify-center',
                        isActive({ to: '/app/companies', exact: false }) && 'bg-muted text-muted-foreground'
                    ]"
                    aria-label="Компании"
                >
                    <Building2Icon class="h-5 w-5 ml-[2px] flex-shrink-0" />
                    <span 
                        :class="[
                            'text-sm font-medium whitespace-nowrap transition-all duration-300 ease-in-out',
                            // sidebarExpanded ? 'opacity-100 translate-x-0' : 'opacity-0 -translate-x-4 absolute'
                        ]"
                    >
                        Компании
                    </span>
                </Link>

                <!-- User Section -->
                <div 
                    :class="[
                        'flex items-center gap-3 rounded-lg px-3 py-2 pl-2 bg-muted/50 transition-all duration-300 ease-in-out overflow-hidden',
                        // sidebarCollapsed && 'justify-center'
                    ]"
                >
                    <div class="h-8 w-8 rounded-full bg-muted text-primary flex items-center justify-center font-semibold text-xs ring-2 ring-accent/10 flex-shrink-0">
                        {{ userInitials }}
                    </div>
                    <div 
                        :class="[
                            'flex-1 min-w-0 transition-all duration-300 ease-in-out',
                            // sidebarExpanded ? 'opacity-100 translate-x-0' : 'opacity-0 -translate-x-4 absolute'
                        ]"
                    >
                        <p class="text-sm font-medium leading-none truncate">{{ username }}</p>
                        <p class="text-xs text-muted-foreground truncate mt-1">{{ user.email }}</p>
                    </div>
                </div>

                <!-- Actions Row: Theme Switcher, Telegram, Logout -->
                <div class="flex items-center gap-1 mt-1 overflow-hidden">
                    <!-- Telegram Link -->
                    <a
                        href="https://t.me/LLymar"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="flex items-center justify-center rounded-lg px-3 py-2.5 text-muted-foreground hover:bg-muted transition-all duration-300 ease-in-out"
                        aria-label="Telegram"
                        title="Связаться через Telegram"
                    >
                        <Send class="h-5 w-5" />
                    </a>

                    <!-- Theme Switcher -->
                    <div class="w-full">
                        <ThemeSwitcher variant="inline" class="w-full" />
                    </div>

                    <!-- Logout -->
                    <Link 
                        href="/logout"
                        class="flex items-center justify-center rounded-lg px-3 py-2.5 text-destructive hover:bg-destructive/10 transition-all duration-300 ease-in-out"
                        aria-label="Выйти"
                        title="Выйти"
                    >
                        <LogOut class="h-5 w-5" />
                    </Link>
                </div>
            </div>
        </div>
    </aside>
</template>

<style>
/* Add left padding for body when sidebar layout is active */
@media (min-width: 768px) {
    body.has-sidebar {
        padding-left: 4rem; /* 64px - collapsed sidebar width */
        transition: padding-left 0.3s ease-in-out;
    }
}
</style>
