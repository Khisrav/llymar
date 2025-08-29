<script setup>
import { computed } from "vue";
import { Head, Link } from "@inertiajs/vue3";
import GuestHeaderLayout from "@/Layouts/GuestHeaderLayout.vue";
import Badge from "../../Components/ui/badge/Badge.vue";
import { EyeIcon } from "lucide-vue-next";
import Button from "../../Components/ui/button/Button.vue";

const props = defineProps({
	news: Object,
	relatedNews: Array,
	seo: Object,
});

const shareUrls = computed(() => {
	const url = encodeURIComponent(props.seo.url);
	const title = encodeURIComponent(props.news.title);
	const description = encodeURIComponent(props.news.excerpt);

	return {
		telegram: `https://t.me/share/url?url=${url}&text=${title}`,
		vk: `https://vk.com/share.php?url=${url}&title=${title}&description=${description}`,
	};
});

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
		<meta property="og:image" :content="seo.image" />
		<meta property="og:url" :content="seo.url" />
		<meta property="og:type" content="article" />
		<meta name="twitter:card" content="summary_large_image" />
		<meta name="twitter:title" :content="seo.title" />
		<meta name="twitter:description" :content="seo.description" />
		<meta name="twitter:image" :content="seo.image" />
	</Head>

	<GuestHeaderLayout />
	<div class="min-h-screen bg-gray-50">
		<!-- Breadcrumbs -->
		<div class="bg-white border-b">
			<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-4">
				<nav class="flex text-xs">
					<Link :href="route('news.index')" class="text-blue-600 hover:text-blue-800"> Новости </Link>
					<span class="mx-2 text-gray-500">/</span>
					<span class="text-gray-700">{{ news.title }}</span>
				</nav>
			</div>
		</div>

		<!-- Article -->
		<article class="container bg-white mx-auto px-4 sm:px-6 lg:px-8 py-12">
			<div class="rounded-lg overflow-hidden">
				<!-- Header -->
				<div class="md:px-8 md:py-8">
					<div class="montserrat flex flex-row items-center justify-between text-xs md:text-sm text-gray-500 mb-4">
						<time :datetime="news.published_at">
							{{ formatDate(news.published_at) }}
						</time>
						<Badge variant="outline" class="space-x-1.5 text-gray-500">
							<EyeIcon class="w-4 h-4" />
							<span>{{ news.views }}</span>
						</Badge>
					</div>

					<h1 class="text-3xl montserrat md:text-4xl font-bold text-gray-900 leading-tight mb-6">
						{{ news.title }}
					</h1>

					<!-- Cover Image -->
					<div class="mb-8">
						<img :src="'/storage/' + news.cover_image" :alt="news.title" class="w-full h-64 md:h-96 object-cover rounded-lg" />
					</div>

					<!-- Content -->
					<div class="prose space-y-4 pb-4 text-justify prose-lg max-w-none prose-blue prose-headings:font-semibold prose-headings:text-gray-900 prose-p:text-gray-700 prose-a:text-blue-600 prose-strong:text-gray-900" v-html="news.content"></div>
				</div>

				<!-- Author Info -->
				<div class="border-t bg-gray-50 px-8 py-6">
					<div class="flex items-center">
						<div class="flex-shrink-0">
							<div class="h-12 w-12 bg-blue-600 rounded-full flex items-center justify-center">
								<span class="text-white font-medium text-lg">
									{{ news.author.name.charAt(0).toUpperCase() }}
								</span>
							</div>
						</div>
						<div class="ml-4">
							<p class="text-sm font-medium text-gray-900">
								{{ news.author.name }}
							</p>
							<p class="text-sm text-gray-500">Автор статьи</p>
						</div>
					</div>
				</div>
			</div>

			<!-- Share Buttons -->
			<div class="mt-8 text-center">
				<p class="text-sm text-gray-600 mb-4">Поделиться статьей:</p>
				<div class="flex justify-center space-x-4">
					<a :href="shareUrls.telegram" target="_blank" rel="noopener noreferrer" class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
						<svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
							<path
								d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"
							/>
						</svg>
						Telegram
					</a>

					<a :href="shareUrls.vk" target="_blank" rel="noopener noreferrer" class="inline-flex items-center px-4 py-2 bg-blue-700 text-white rounded-lg hover:bg-blue-800 transition-colors">
						<svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
							<path
								d="M15.684 0H8.316C1.592 0 0 1.592 0 8.316v7.368C0 22.408 1.592 24 8.316 24h7.368C22.408 24 24 22.408 24 15.684V8.316C24 1.592 22.408 0 15.684 0zm3.692 17.123h-1.744c-.66 0-.862-.525-2.05-1.714-1.033-1.01-1.49-.918-1.744-.918-.356 0-.458.102-.458.594v1.563c0 .424-.135.678-1.253.678-1.846 0-3.896-1.118-5.335-3.202C4.624 10.857 4.03 8.57 4.03 8.096c0-.254.102-.491.594-.491h1.744c.441 0 .61.203.78.678.863 2.49 2.303 4.675 2.896 4.675.22 0 .322-.102.322-.66V9.721c-.068-1.186-.695-1.287-.695-1.71 0-.204.17-.407.44-.407h2.744c.373 0 .508.203.508.643v3.473c0 .372.17.508.271.508.22 0 .407-.136.813-.542 1.254-1.406 2.151-3.574 2.151-3.574.119-.254.322-.491.763-.491h1.744c.525 0 .644.271.525.643-.22 1.017-2.354 4.031-2.354 4.031-.186.305-.254.44 0 .763.186.254.795.78 1.203 1.254.745.847 1.32 1.558 1.473 2.05.17.49-.085.744-.576.744z"
							/>
						</svg>
						VKontakte
					</a>
				</div>
			</div>

			<!-- Related News -->
			<div v-if="relatedNews.length > 0" class="mt-16">
				<h2 class="text-2xl font-bold text-gray-900 mb-8 text-center">Читайте также</h2>
				<div class="grid gap-4 md:grid-cols-3">
					<article v-for="article in relatedNews" :key="article.id" class="bg-white rounded-lg border border-gray-200 overflow-hidden hover:border-gray-400 transition-shadow duration-300">
						<Link :href="route('news.show', article.slug)" class="block">
							<div class="aspect-w-16 aspect-h-9 bg-gray-200">
								<img :src="'/storage/' + article.cover_image" :alt="article.title" class="w-full h-32 object-cover" loading="lazy" />
							</div>
							<div class="p-4">
								<div class="text-xs text-gray-500 mb-2">
									{{ formatDate(article.published_at) }}
								</div>
								<h3 class="text-sm md:text-base font-semibold text-gray-900 mb-2 line-clamp-2">
									{{ article.title }}
								</h3>
								<p class="text-xs md:text-sm text-gray-600 line-clamp-2">
									{{ article.excerpt }}
								</p>
							</div>
						</Link>
					</article>
				</div>
			</div>

			<!-- Back to News -->
			<div class="mt-12 text-center">
				<Link :href="route('news.index')">
					<Button variant="default">
						← Вернуться к новостям
					</Button>
				</Link>
			</div>
		</article>
	</div>
</template>

<style scope>
/* Custom prose styles for rich content */

.prose img {
	@apply rounded-lg shadow-md;
}

.prose table {
	border-width: 1px;
	border-collapse: revert;
	width: 100%;
}
.prose ul {
	list-style: revert;
	margin: revert;
	padding: revert;
}

.prose th,
.prose td {
	border-width: 1px;
	border-collapse: revert;
}

.prose th {
	@apply bg-gray-50 font-semibold;
}

.prose blockquote {
	@apply border-l-4 border-blue-500 pl-4 italic bg-blue-50 py-2;
}

.prose code {
	@apply bg-gray-100 px-1 py-0.5 rounded text-sm;
}

.prose pre {
	@apply bg-gray-900 text-white p-4 rounded-lg overflow-x-auto;
}

.prose pre code {
	@apply bg-transparent p-0;
}

.line-clamp-2 {
	overflow: hidden;
	display: -webkit-box;
	-webkit-box-orient: vertical;
	-webkit-line-clamp: 2;
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
