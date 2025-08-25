<script lang="ts" setup>
import { MailIcon } from 'lucide-vue-next';
import { ref } from 'vue';

const socialLinks = ref([
    {
        title: 'Rutube',
        icon: '<svg viewBox="0 0 192 192" xmlns="http://www.w3.org/2000/svg" xml:space="preserve" fill="none" stroke="#f6d32d"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path fill="#ffffff" d="M128.689 47.57H20.396v116.843h30.141V126.4h57.756l26.352 38.013h33.75l-29.058-38.188c9.025-1.401 15.522-4.73 19.493-9.985 3.97-5.255 5.956-13.664 5.956-24.875v-8.759c0-6.657-.721-11.912-1.985-15.941-1.264-4.029-3.43-7.533-6.498-10.686-3.249-2.978-6.858-5.08-11.19-6.481-4.332-1.226-9.747-1.927-16.424-1.927zm-4.873 53.08H50.537V73.321h73.279c4.15 0 7.038.7 8.482 1.927 1.444 1.226 2.347 3.503 2.347 6.832v9.81c0 3.503-.903 5.78-2.347 7.006s-4.331 1.752-8.482 1.752z" style="display:inline;fill:none;stroke:#FBD7A5;stroke-width:12;stroke-linecap:round;stroke-linejoin:round;stroke-dasharray:none;stroke-opacity:1" transform="translate(1.605 -1.99)"></path><path fill="#F41240" d="M162.324 45.568c5.52 0 9.998-4.477 9.998-10s-4.478-10-9.998-10c-5.524 0-10.002 4.477-10.002 10s4.478 10 10.002 10z" style="display:inline;fill:#FBD7A5;fill-opacity:1;stroke:none;stroke-width:10.6667;stroke-linecap:round;stroke-linejoin:round;stroke-dasharray:none;stroke-opacity:1" transform="translate(1.605 -1.99)"></path></g></svg>',
        url: '#',
    },
    {
        title: 'Rutube',
        icon: '',
        url: '#',
    },
    {
        title: 'Rutube',
        icon: '',
        url: '#',
    },
    {
        title: 'Rutube',
        icon: '',
        url: '#',
    },
    {
        title: 'Rutube',
        icon: '',
        url: '#',
    },
    
])
</script>

<template>
    <!-- Footer -->
	<footer class="bg-black text-white py-16">
		<div class="container max-w-screen-2xl px-4">
			<div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
				<!-- Company Info -->
				<div class="md:col-span-2">
					<div class="flex items-center gap-3 mb-4">
						<img src="/assets/golden-logo-llymar.png" alt="Llymar" class="h-8" />
					</div>
					<p class="text-gray-300 mb-4 max-w-md">Премиальные системы безрамного остекления для жилых и коммерческих объектов. Создаём современные решения.</p>
					<div class="hidden flex gap-4">
						<a href="#" class="text-gray-300 hover:text-light-gold transition-colors">
							<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
								<path
									d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"
								/>
							</svg>
						</a>
						<a href="#" class="text-gray-300 hover:text-light-gold transition-colors">
							<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
								<path
									d="M22.46 6c-.77.35-1.6.58-2.46.69.88-.53 1.56-1.37 1.88-2.38-.83.5-1.75.85-2.72 1.05C18.37 4.5 17.26 4 16 4c-2.35 0-4.27 1.92-4.27 4.29 0 .34.04.67.11.98C8.28 9.09 5.11 7.38 3 4.79c-.37.63-.58 1.37-.58 2.15 0 1.49.75 2.81 1.91 3.56-.71 0-1.37-.2-1.95-.5v.03c0 2.08 1.48 3.82 3.44 4.21a4.22 4.22 0 0 1-1.93.07 4.28 4.28 0 0 0 4 2.98 8.521 8.521 0 0 1-5.33 1.84c-.34 0-.68-.02-1.02-.06C3.44 20.29 5.7 21 8.12 21 16 21 20.33 14.46 20.33 8.79c0-.19 0-.37-.01-.56.84-.6 1.56-1.36 2.14-2.23z"
								/>
							</svg>
						</a>
						<a href="#" class="text-gray-300 hover:text-light-gold transition-colors">
							<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
								<path
									d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.174-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.097.118.112.221.085.343-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.402.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.357-.629-2.746-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24.009 12.017 24.009c6.624 0 11.99-5.367 11.99-11.988C24.007 5.367 18.641.001.012.001z"
								/>
							</svg>
						</a>
					</div>
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
					<div class="text-gray-400 text-sm">© 2025 Llymar. Все права защищены.</div>
					<div class="flex gap-6 text-sm text-gray-400">
						<!-- <a href="https://t.me/kh_tj" class="hover:text-light-gold transition-colors">Разработка сайта</a> -->
					</div>
				</div>
			</div>
		</div>
	</footer>
</template>