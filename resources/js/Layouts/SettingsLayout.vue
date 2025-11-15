<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { cn } from '../lib/utils';
import { UserIcon, BuildingIcon } from 'lucide-vue-next';

interface MenuItem {
	title: string;
	href: string;
	icon: any;
	description: string;
	visible?: boolean;
}

const page = usePage();
const currentPath = computed(() => page.url);
const { can_access_companies } = page.props as any;

const allMenuItems: MenuItem[] = [
	{
		title: 'Настройки аккаунта',
		href: '/app/account/settings',
		icon: UserIcon,
		description: 'Личная информация и контакты',
		visible: true
	},
	{
		title: 'Компании',
		href: '/app/companies',
		icon: BuildingIcon,
		description: 'Управление компаниями',
		visible: can_access_companies
	}
];

const menuItems = computed(() => allMenuItems.filter(item => item.visible !== false));

const isActive = (href: string) => {
	return currentPath.value === href || currentPath.value.startsWith(href + '/');
};
</script>

<template>
	<div class="flex flex-col md:flex-row gap-6 md:gap-8 md:mt-6">
		<!-- Sidebar Navigation -->
		<aside class="w-full md:w-64 flex-shrink-0">
			<nav class="space-y-2">
				<Link
					v-for="item in menuItems"
					:key="item.href"
					:href="item.href"
					:class="cn(
						'flex items-start gap-3 rounded-lg px-3 py-3 transition-all duration-200',
						isActive(item.href)
							? 'bg-muted/100 text-primary'
							: 'hover:bg-muted/50 text-muted-foreground hover:text-foreground'
					)"
				>
					<component :is="item.icon" class="h-5 w-5 mt-0.5 flex-shrink-0" />
					<div class="flex-1 min-w-0">
						<div class="font-medium text-sm">{{ item.title }}</div>
						<div 
							:class="cn(
								'text-xs mt-0.5',
								isActive(item.href)
									? 'text-primary'
									: 'text-muted-foreground'
							)"
						>
							{{ item.description }}
						</div>
					</div>
				</Link>
			</nav>
		</aside>

		<!-- Main Content -->
		<main class="flex-1 min-w-0">
			<slot />
		</main>
	</div>
</template>

