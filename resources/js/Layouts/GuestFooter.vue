<script lang="ts" setup>
import { ref } from "vue";

const socialLinks = ref([
	{
		title: "Rutube",
		url: "https://rutube.ru/channel/43806382/",
		icon: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
      <path d="M23.498 6.186a2.965 2.965 0 0 0-2.087-2.098C19.2 3.5 12 3.5 12 3.5s-7.2 0-9.411.588a2.965 2.965 0 0 0-2.087 2.098A30.12 30.12 0 0 0 .5 12a30.12 30.12 0 0 0 .002 5.814 2.965 2.965 0 0 0 2.087 2.098C4.8 20.5 12 20.5 12 20.5s7.2 0 9.411-.588a2.965 2.965 0 0 0 2.087-2.098A30.12 30.12 0 0 0 23.5 12a30.12 30.12 0 0 0-.002-5.814zM9.75 15.02V8.98L15.5 12l-5.75 3.02z"/>
    </svg>`,
	},
	{
		title: "Telegram",
		url: "https://t.me/LLymar",
		icon: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
      <path d="M9.8 18.6c-.42 0-.35-.16-.5-.57L8.2 14.1 18.2 7.4"/>
      <path d="M9.8 18.6c.32 0 .47-.15.65-.33l1.8-1.75-2.25-1.36"/>
      <path d="M9.8 18.6l6.65 4.92c.48.35 1.12.07 1.25-.5l3-14c.15-.72-.53-1.36-1.22-1.09L3.6 9.6c-.71.29-.7 1.22.03 1.49l4.7 1.76 9.9-6.55"/>
    </svg>`,
	},
	{
		title: "Dzen",
		url: "https://dzen.ru/llymar?share_to=whatsapp",
		icon: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" fill="currentColor">
      <circle cx="16" cy="16" r="16" fill="#000"/>
      <path fill="#fff" d="M17.8 17.8v6.2a8.03 8.03 0 0 0 6.2-6.2h-6.2zm-3.6 0H8a8.03 8.03 0 0 0 6.2 6.2v-6.2zm3.6-3.6h6.2a8.03 8.03 0 0 0-6.2-6.2v6.2zm-3.6 0V8a8.03 8.03 0 0 0-6.2 6.2h6.2z"/>
    </svg>`,
	},
]);
</script>

<template>
	<!-- Footer -->
	<footer class="bg-black text-white py-16 montserrat">
		<div class="container max-w-screen-2xl px-4">
			<div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
				<!-- Company Info -->
				<div class="md:col-span-2">
					<div class="flex items-center gap-3 mb-4">
						<img src="/assets/golden-logo-llymar.png" alt="Llymar" class="h-8" />
					</div>
					<p class="text-gray-300 mb-4 max-w-md">Премиальные системы безрамного остекления для жилых и коммерческих объектов. Создаём современные решения.</p>
					
				</div>

				<!-- Services -->
				<div class="hidden">
					<h3 class="text-lg font-semibold mb-4 text-light-gold">Услуги</h3>
					<ul class="space-y-2 text-gray-300">
						<li><a href="#" class="hover:text-light-gold transition-colors">Безрамное остекление</a></li>
						<li><a href="#" class="hover:text-light-gold transition-colors">Панорамное остекление</a></li>
						<li><a href="#" class="hover:text-light-gold transition-colors">Остекление террас</a></li>
						<li><a href="#" class="hover:text-light-gold transition-colors">Фасадное остекление</a></li>
					</ul>
				</div>

				<!-- Contact -->
				<div>
					<h3 class="text-lg font-semibold mb-4 text-light-gold">Контакты</h3>
					<div class="space-y-2 text-gray-300">
						<p>г. Краснодар</p>
						<a href="tel:+79898041234" class="block hover:text-light-gold transition-colors"> +7 (989) 804 12-34 </a>
						<a href="mailto:info@llymar.ru" class="block hover:text-light-gold transition-colors"> info@llymar.ru </a>
					</div>
				</div>

				<div>
					<h3 class="text-lg font-semibold mb-4 text-light-gold">Информация</h3>
					<div class="space-y-2 text-gray-300">
						<a href="#" class="block hover:text-light-gold transition-colors"> Политика конфиденциальности </a>
						<a href="#" class="block hover:text-light-gold transition-colors"> Условия использования </a>
					</div>
				</div>
			</div>

			<!-- Bottom Bar -->
			<div class="border-t border-gray-800 pt-8">
				<div class="flex flex-col md:flex-row justify-between items-center gap-4">
					<div class="flex gap-4">
						<div v-for="(link, index) in socialLinks" :key="index">
							<a :href="link.url" class="text-light-gold hover:text-light-gold transition-colors">{{ link.title }}</a>
						</div>
					</div>
					<div class="text-gray-400 text-sm">© 2025 Llymar. Все права защищены.</div>
				</div>
			</div>
		</div>
	</footer>
</template>
