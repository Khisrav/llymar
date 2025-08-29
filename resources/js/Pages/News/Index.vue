<script setup>
import { ref } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import GuestHeaderLayout from "@/Layouts/GuestHeaderLayout.vue";
import Badge from "../../Components/ui/badge/Badge.vue";
import { EyeIcon } from "lucide-vue-next";

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
			<div class="container mx-auto fle flex-row gap-4 items-center justify-between px-4 sm:px-6 lg:px-8 py-12">
				<div class="text-center">
					<h1 class="text-4xl font-bold text-gray-900 sm:text-5xl">Новости компании</h1>
					<p class="mt-4 text-xl text-gray-600 max-w-2xl mx-auto">Читайте последние новости о наших продуктах, достижениях и событиях</p>
				</div>

				<div>
					<!-- Search -->
					<div class="mt-8 max-w-md mx-auto">
						<div class="relative">
							<div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
								<svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
								</svg>
							</div>
							<input
								v-model="searchQuery"
								@keyup.enter="searchNews"
								type="text"
								placeholder="Поиск новостей..."
								class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-300 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
							/>
							<div class="absolute inset-y-0 right-0 flex items-center">
								<button @click="searchNews" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-r-md transition-colors duration-200">Найти</button>
							</div>
						</div>
					</div>
	
					<!-- Search Results Info -->
					<div v-if="search" class="mt-4 text-center">
						<p class="text-gray-600">
							Результаты поиска для: <span class="font-semibold">"{{ search }}"</span>
							<Link :href="route('news.index')" class="ml-2 text-blue-600 hover:text-blue-800"> Очистить </Link>
						</p>
					</div>
				</div>
			</div>
		</div>

		<!-- News Grid -->
		<div class="container mx-auto p-4">
			<div v-if="news.data.length > 0" class="grid gap-4 lg:grid-cols-3 md:grid-cols-2">
				<article v-for="article in news.data" :key="article.id" class="bg-white rounded-lg border border-gray-200 overflow-hidden hover:border-gray-400 transition-shadow duration-300">
					<Link :href="route('news.show', article.slug)" class="block">
						<div class="aspect-w-16 aspect-h-9 bg-gray-200">
							<img :src="'/storage/' + article.cover_image" :alt="article.title" class="w-full h-48 object-cover" loading="lazy" />
						</div>
						<div class="p-4">
							<div class="flex flex-row justify-between items-center text-sm text-gray-500 mb-2">
								<time :datetime="article.published_at">
									{{ formatDate(article.published_at) }}
								</time>
								<Badge variant="outline" class="space-x-1.5 text-gray-500">
									<EyeIcon class="w-4 h-4" />
									<span>{{ article.views }}</span>
								</Badge>
							</div>
							<h2 class="text-xl font-semibold text-gray-900 mb-3 line-clamp-2">
								{{ article.title }}
							</h2>
							<p class="text-gray-600 line-clamp-3">
								{{ article.excerpt }}
							</p>
							<div class="mt-4">
								<span class="text-blue-600 font-medium hover:text-blue-800"> Читать далее → </span>
							</div>
						</div>
					</Link>
				</article>
			</div>

			<!-- Empty State -->
			<div v-else class="text-center py-12">
				<svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
				</svg>
				<h3 class="mt-4 text-lg font-medium text-gray-900">
					{{ search ? "Ничего не найдено" : "Новостей пока нет" }}
				</h3>
				<p class="mt-2 text-gray-500">
					{{ search ? "Попробуйте изменить запрос для поиска" : "Скоро здесь появятся интересные новости" }}
				</p>
			</div>

			<!-- Pagination -->
			<div v-if="news.data.length > 0 && (news.prev_page_url || news.next_page_url)" class="mt-12">
				<nav class="flex justify-center">
					<div class="flex space-x-2">
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
