<script setup>
import { ref } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import GuestHeaderLayout from "@/Layouts/GuestHeaderLayout.vue";
import Badge from "../../Components/ui/badge/Badge.vue";
import { EyeIcon, ImageIcon } from "lucide-vue-next";
import Input from "../../Components/ui/input/Input.vue";
import Button from "../../Components/ui/button/Button.vue";
import Card from "../../Components/ui/card/Card.vue";
import CardContent from "../../Components/ui/card/CardContent.vue";
import CardHeader from "../../Components/ui/card/CardHeader.vue";
import CardTitle from "../../Components/ui/card/CardTitle.vue";

defineProps({
	news: Object,
	search: String,
	seo: Object,
});

const searchQuery = ref("");

const searchNews = () => {
	if (searchQuery.value.trim()) {
		router.get(route("news.index"), { search: searchQuery.value.trim() });
	}
};

const formatDate = (dateString) => {
	if (!dateString) return "";
	const date = new Date(dateString);
	return date.toLocaleDateString("ru-RU", {
		year: "numeric",
		month: "long",
		day: "numeric",
	});
};
</script>

<template>
	<Head>
		<title>{{ seo.title }}</title>
		<meta name="description" :content="seo.description" />
		<meta property="og:title" :content="seo.title" />
		<meta property="og:description" :content="seo.description" />
		<meta property="og:type" content="website" />
	</Head>

	<GuestHeaderLayout />
	<div class="min-h-screen bg-gray-50">
		<!-- Hero Section -->
		<div class="bg-white border-b">
			<div class="container mx-auto flex flex-col md:flex-row gap-6 md:gap-8 items-center justify-between px-4 sm:px-6 lg:px-8 py-10 md:py-14">
				<div class="text-center md:text-left">
					<h1 class="text-3xl md:text-4xl font-semibold text-gray-900 montserrat">Статьи компании</h1>
					<p class="mt-2 text-sm md:text-base text-gray-600">Последние обновления, анонсы и полезные материалы</p>
				</div>

				<div class="w-full md:w-auto">
					<!-- Search -->
					<div class="max-w-md md:ml-auto w-full">
						<div class="flex w-full items-center gap-2">
							<Input v-model="searchQuery" @keyup.enter="searchNews" placeholder="Поиск статей..." />
							<Button @click="searchNews" variant="default">Найти</Button>
						</div>
					</div>

					<!-- Search Results Info -->
					<div v-if="search" class="mt-3 text-center md:text-right">
						<p class="text-gray-600">
							Результаты поиска для: <span class="font-semibold">"{{ search }}"</span>
							<Link :href="route('news.index')" class="ml-2 text-blue-600 hover:text-blue-800"> Очистить </Link>
						</p>
					</div>
				</div>
			</div>
		</div>

		<!-- News Grid -->
		<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
			<div v-if="news.data.length > 0" class="grid gap-6 md:gap-8 lg:grid-cols-3 md:grid-cols-2">
				<Card v-for="article in news.data" :key="article.id" class="rounded-2xl border border-gray-200/80 hover:border-gray-300 shadow-sm hover:shadow transition-shadow duration-300 overflow-hidden">
					<Link :href="route('news.show', article.slug)" class="block group">
						<div class="bg-gray-100">
							<img v-if="article.cover_image" :src="'/storage/' + article.cover_image" :alt="article.title" class="w-full h-48 object-cover transition-transform duration-300 group-hover:scale-[1.02]" loading="lazy" />
							<div v-else class="w-full h-48 bg-gray-200 flex items-center justify-center">
								<ImageIcon class="w-6 h-6 text-gray-400" />
							</div>
						</div>
						<CardHeader class="px-4 md:px-5 pt-4 pb-0">
							<div class="flex flex-row justify-between items-center text-xs md:text-sm text-gray-500">
								<time :datetime="article.published_at">{{ formatDate(article.published_at) }}</time>
								<Badge variant="outline" class="space-x-1.5 text-gray-500">
									<EyeIcon class="w-4 h-4" />
									<span>{{ article.views }}</span>
								</Badge>
							</div>
							<CardTitle class="mt-2 text-lg md:text-xl font-semibold text-gray-900 line-clamp-2">{{ article.title }}</CardTitle>
						</CardHeader>
						<CardContent class="px-4 md:px-5 pb-5">
							<p class="text-gray-600 line-clamp-3">{{ article.excerpt }}</p>
							<div class="mt-4">
								<span class="text-blue-600 font-medium group-hover:text-blue-800">Читать далее →</span>
							</div>
						</CardContent>
					</Link>
				</Card>
			</div>

			<!-- Empty State -->
			<div v-else class="text-center py-16">
				<svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
				</svg>
				<h3 class="mt-4 text-lg font-medium text-gray-900">
					{{ search ? "Ничего не найдено" : "Новостей пока нет" }}
				</h3>
				<p class="mt-2 text-gray-500">
					{{ search ? "Попробуйте изменить запрос для поиска" : "Скоро здесь появятся интересные новости" }}
				</p>
				<div class="mt-6">
					<Link :href="route('news.index')">
						<Button variant="default">Вернуться к новостям</Button>
					</Link>
				</div>
			</div>

			<!-- Pagination -->
			<div v-if="news.data.length > 0 && (news.prev_page_url || news.next_page_url)" class="mt-12">
				<nav class="flex justify-center">
					<div class="flex items-center space-x-2">
						<Link v-if="news.prev_page_url" :href="news.prev_page_url" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"> Предыдущая </Link>

						<span class="px-4 py-2 text-sm text-gray-700"> Страница {{ news.current_page }} из {{ news.last_page }} </span>

						<Link v-if="news.next_page_url" :href="news.next_page_url" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"> Следующая </Link>
					</div>
				</nav>
			</div>
		</div>
	</div>
</template>

<style scoped>
.line-clamp-2 {
	overflow: hidden;
	display: -webkit-box;
	-webkit-box-orient: vertical;
	-webkit-line-clamp: 2;
}

.line-clamp-3 {
	overflow: hidden;
	display: -webkit-box;
	-webkit-box-orient: vertical;
	-webkit-line-clamp: 3;
}

.aspect-w-16 {
	position: relative;
	padding-bottom: 56.25%; /* 16:9 aspect ratio */
}

.aspect-w-16 img {
	position: absolute;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	object-fit: cover;
}
</style>
