<script setup lang="ts">
import { ref, computed } from "vue"
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from "../Components/ui/dropdown-menu"
import { Sheet, SheetContent, SheetTrigger } from "../Components/ui/sheet"
import { CircleUser, Menu } from "lucide-vue-next"
import Button from "../Components/ui/button/Button.vue"
import { Link } from "@inertiajs/vue3"
import ThemeSwitcher from "../Components/ThemeSwitcher.vue"
import { usePage } from "@inertiajs/vue3"

const { can_access_app_calculator, can_access_admin_panel } = usePage().props as any
const { user } = usePage().props.auth as any

const navigationMenu = computed(() => {
    const menu = [
        { title: 'Главная', to: '/' },
        { title: 'История', to: '/app/history' },
    ]

    if (can_access_app_calculator) {
        menu.push({ title: 'Калькулятор', to: '/app/calculator' })
    }

    return menu
})

const username = computed(() => {
    let firstName = user.name.split(' ')[0], lastName = user.name.split(' ')[1]
    
    if (!lastName) return firstName
    return firstName + ' ' + lastName[0] + '.'
})
</script>

<template>
    <header class="top-0 flex h-16 items-center gap-4 border-b bg-background px-4 md:px-6 z-20">
        <nav class="hidden flex-col gap-6 text-lg font-medium md:flex md:flex-row md:items-center md:gap-5 md:text-sm lg:gap-6">
            <a href="#" class="flex items-center gap-2 text-lg font-semibold md:text-base">
                <span class="sr-only">LLYMAR</span>
            </a>
            <Link v-for="item in navigationMenu" :key="item.title" :href="item.to" class="text-foreground transition-colors hover:text-foreground">
                {{ item.title }}
            </Link>
        </nav>
        <Sheet>
            <SheetTrigger as-child>
                <Button variant="outline" size="icon" class="shrink-0 md:hidden">
                    <Menu class="h-5 w-5" />
                    <span class="sr-only">Toggle navigation menu</span>
                </Button>
            </SheetTrigger>
            <SheetContent side="left">
                <nav class="grid gap-6 text-lg font-medium">
                    <a href="#" class="flex items-center gap-2 text-lg font-semibold">
                        <span>LLYMAR</span>
                    </a>
                    <Link v-for="item in navigationMenu" :key="item.title" :href="item.to" class="text-foreground transition-colors hover:text-foreground">
                        {{ item.title }}
                    </Link>
                </nav>
            </SheetContent>
        </Sheet>
        <div class="flex w-full items-center gap-4 md:ml-auto md:gap-2 lg:gap-4">
            <div class="ml-auto flex-1 sm:flex-initial"></div>
            <ThemeSwitcher />
            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <Button variant="outline" size="icon" class="rounded-full">
                        <CircleUser class="h-5 w-5" />
                        <span class="sr-only">Toggle user menu</span>
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end">
                    <DropdownMenuLabel>{{ username }}</DropdownMenuLabel>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem v-if="can_access_admin_panel">
                        <Link href="/admin">Админка</Link>
                    </DropdownMenuItem>
                    <DropdownMenuItem>
                        <Link href="/app/account/settings">Настройки</Link>
                    </DropdownMenuItem>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem>
                        <Link href="/logout">Выйти</Link>
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>
        </div>
    </header>
</template>
