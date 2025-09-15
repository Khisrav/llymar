<script setup>
import { Head, Link } from "@inertiajs/vue3";
import GuestHeaderLayout from "../Layouts/GuestHeaderLayout.vue";
import LandingButton from "../Components/LandingButton.vue";
import { ref } from "vue";
import { CheckIcon } from "lucide-vue-next";

const SVGdecor =
	'<svg width="86" height="16" viewBox="0 0 86 16" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0_705_180)"><path d="M6.0088 6H86V0H0L6.0088 6Z" fill="#23322D"/><path d="M16.0244 16H86V10H10.0146L16.0244 16Z" fill="#23322D"/></g><defs><clipPath id="clip0_705_180"><rect width="86" height="16" fill="white" transform="matrix(-1 0 0 1 86 0)"/></clipPath></defs></svg>';

const detailsAndEffect = ref([
	{
		detail: "Дренажные отверстия",
		effect: "Для отвода воды в нижней направляющей. Защита во время дождя.",
	},
	{
		detail: "Резиновые демпфера",
		effect: "Мягкое открытие и закрытие створок.",
	},
	{
		detail: "Усиленные ролики",
		effect: "Износостойкий морозоустойчивый материал для долгой работы.",
	},
	{
		detail: "Нержавеющие элементы",
		effect: "Крепеж и заглушки сохраняют первозданный вид и надежность.",
	},
]);
</script>

<template>
	<Head>
		<title>О системе безрамного остекления</title>
		<meta name="description" content="О системе безрамочного остекления" />
	</Head>

	<GuestHeaderLayout :openConsultationDialog="openConsultationDialog" />

	<!-- Hero Section -->
	<div class="montserrat container text-center max-w-screen-2xl mt-8 mb-6 sm:mt-16 sm:mb-8 md:mt-32 md:mb-16 px-4 sm:px-6 lg:px-8 flex-1 flex flex-col gap-6 sm:gap-8 justify-center">
		<h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl xl:text-6xl font-light leading-tight">О системе безрамочного остекления</h1>
		<div class="flex justify-center">
		<a target="_blank" href="/assets/llymar-glazing-system.pdf">
			<LandingButton>Скачать PDF</LandingButton>
		</a>
		</div>
	</div>

	<!-- Details Section -->
	<section class="montserrat container max-w-screen-2xl my-16 sm:my-24 md:my-32 px-4 sm:px-6 lg:px-8">
		<div class="flex flex-col sm:flex-row gap-4 mb-8 sm:justify-between sm:items-center">
			<h2 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-light">Внимание к деталям</h2>
			<div v-html="SVGdecor" class="hidden sm:flex items-center justify-center"></div>
		</div>
		<div class="flex flex-col gap-6 sm:gap-8 justify-center items-center">
			<div class="w-full flex justify-center">
				<img src="/assets/vnimanie-k-detalyam.svg" alt="Внимание к деталям" class="w-full max-w-4xl h-auto" />
			</div>
			<div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6 md:gap-8 w-full">
				<div v-for="detail in detailsAndEffect" :key="detail.detail" class="flex flex-col gap-2 p-4 sm:p-6 border border-gray-200 bg-white rounded-xl shadow-sm">
					<p class="text-dark-green font-medium text-sm sm:text-base">{{ detail.detail }}</p>
					<p class="text-gray-500 text-sm sm:text-base">{{ detail.effect }}</p>
				</div>
			</div>
		</div>
	</section>

	<!-- Features Section -->
	<section class="montserrat container max-w-screen-2xl my-16 sm:my-24 md:my-32 px-4 sm:px-6 lg:px-8">
		<div class="flex flex-col sm:flex-row gap-4 mb-8 sm:justify-between sm:items-center">
			<h2 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-light">Преимущества системы</h2>
			<div v-html="SVGdecor" class="hidden sm:flex items-center justify-center"></div>
		</div>

		<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 md:gap-8">
			<div class="group p-4 sm:p-6 bg-white rounded-2xl transition duration-300 border border-gray-200 hover:border-gray-300 shadow-sm">
				<h3 class="font-medium mb-2 text-sm sm:text-base">Прочность и устойчивость</h3>
				<p class="text-gray-500 text-sm sm:text-base">Выдерживают значительные ветровые, снеговые и другие нагрузки.</p>
			</div>

			<div class="group p-4 sm:p-6 bg-white rounded-2xl transition duration-300 border border-gray-200 hover:border-gray-300 shadow-sm">
				<h3 class="font-medium mb-2 text-sm sm:text-base">Легкость в уходе</h3>
				<p class="text-gray-500 text-sm sm:text-base">Не требуют сложного обслуживания или специальных средств.</p>
			</div>

			<div class="group p-4 sm:p-6 bg-white rounded-2xl transition duration-300 border border-gray-200 hover:border-gray-300 shadow-sm">
				<h3 class="font-medium mb-2 text-sm sm:text-base">Премиум фурнитура</h3>
				<p class="text-gray-500 text-sm sm:text-base">Фурнитура из нержавеющей стали обеспечивает надежность и долговечность.</p>
			</div>

			<div class="group p-4 sm:p-6 bg-white rounded-2xl transition duration-300 border border-gray-200 hover:border-gray-300 shadow-sm">
				<h3 class="font-medium mb-2 text-sm sm:text-base">Алюминиевый профиль</h3>
				<p class="text-gray-500 text-sm sm:text-base">Долговечный межстворочный алюминиевый профиль для надежных конструкций.</p>
			</div>

			<div class="group p-4 sm:p-6 bg-white rounded-2xl transition duration-300 border border-gray-200 hover:border-gray-300 shadow-sm col-span-1 sm:col-span-2 lg:col-span-1">
				<h3 class="font-medium mb-2 text-sm sm:text-base">Закалённое стекло</h3>
				<p class="text-gray-500 text-sm sm:text-base">Стекло толщиной 10 мм позволяет изготавливать створки высотой до 3,2 м.</p>
			</div>
		</div>

		<!-- Certification Section -->
		<div class="flex justify-center mt-12 sm:mt-16">
			<div class="w-full max-w-4xl bg-gradient-to-r from-dark-green to-green-900 text-white rounded-2xl sm:rounded-3xl shadow-lg p-6 sm:p-8 md:p-10 relative overflow-hidden">
				<div class="absolute top-0 left-0 w-full h-full bg-white opacity-5 rounded-2xl sm:rounded-3xl"></div>
				<h3 class="text-xl sm:text-2xl md:text-3xl font-bold mb-4 sm:mb-6 relative text-center sm:text-left">Система сертифицирована и внесена в реестр СТО!</h3>
				<ul class="space-y-3 sm:space-y-4 text-sm sm:text-base md:text-lg relative">
					<li class="flex items-start sm:items-center gap-3">
						<CheckIcon class="w-6 h-6 sm:w-8 sm:h-8 md:w-12 md:h-12 text-light-gold flex-shrink-0 mt-1 sm:mt-0" />
						<span>Наша система и материалы предполагают долгую и безпроблемную эксплуатацию.</span>
					</li>
					<li class="flex items-start sm:items-center gap-3">
						<CheckIcon class="w-6 h-6 sm:w-8 sm:h-8 md:w-12 md:h-12 text-light-gold flex-shrink-0 mt-1 sm:mt-0" />
						<span>Мы постоянно совершенствуем систему с расчетом на долговечное использование.</span>
					</li>
					<li class="flex items-start sm:items-center gap-3">
						<CheckIcon class="w-6 h-6 sm:w-8 sm:h-8 md:w-12 md:h-12 text-light-gold flex-shrink-0 mt-1 sm:mt-0" />
						<span>Конструкции имеют безупречные характеристики, не требуя специального обслуживания.</span>
					</li>
    			</ul>
    		</div>
		</div>
	</section>
</template>
