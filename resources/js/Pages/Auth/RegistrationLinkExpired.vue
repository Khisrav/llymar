<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import Button from "../../Components/ui/button/Button.vue";
import { ArrowLeftIcon } from "lucide-vue-next";

const props = defineProps<{
	isUsed: boolean;
	expiredAt: string;
}>();
</script>

<template>
	<Head>
		<title>Ссылка недействительна</title>
	</Head>
	<div class="min-h-screen bg-gradient-to-br from-red-50 to-orange-50 dark:from-gray-900 dark:to-gray-800 flex items-center justify-center px-4">
		<div class="max-w-md w-full bg-white dark:bg-gray-800 shadow-2xl rounded-2xl overflow-hidden">
			<div class="bg-gradient-to-r from-red-600 to-orange-600 px-8 py-6">
				<div class="flex justify-center mb-4">
					<svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
					</svg>
				</div>
				<h1 class="text-2xl font-bold text-white text-center">Ссылка недействительна</h1>
			</div>

			<div class="p-8 text-center space-y-4">
				<p class="text-gray-700 dark:text-gray-300 text-lg">
					<template v-if="isUsed">
						Эта ссылка регистрации уже была использована.
					</template>
					<template v-else>
						Срок действия этой ссылки истек {{ new Date(expiredAt).toLocaleString("ru-RU") }}.
					</template>
				</p>

				<p class="text-gray-600 dark:text-gray-400">
					Пожалуйста, свяжитесь с вашим менеджером для получения новой ссылки регистрации.
				</p>

				<div class="pt-4">
					<Button @click="window.location.href = '/'" variant="outline" class="w-full">
						<ArrowLeftIcon />
						Вернуться на главную
					</Button>
				</div>
			</div>
		</div>
	</div>
</template>

