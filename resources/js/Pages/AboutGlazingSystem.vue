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
		<title>О системе безрамочного остекления</title>
		<meta name="description" content="О системе безрамочного остекления" />
	</Head>

	<GuestHeaderLayout :openConsultationDialog="openConsultationDialog" />

	<div class="montserrat container text-center max-w-screen-2xl mt-16 mb-8 md:mt-32 md:mb-16 px-2 md:px-4 flex-1 flex flex-col gap-8 justify-center">
		<h1 class="text-4xl md:text-6xl font-light">О системе безрамочного остекления</h1>
		<a target="_blank" href="/assets/llymar-glazing-system.pdf">
			<LandingButton>Скачать PDF</LandingButton>
		</a>
	</div>

	<section class="montserrat container max-w-screen-2xl my-32 px-2 md:px-4">
		<div class="flex flex-row gap-4 mb-8 justify-between items-center">
			<h2 class="text-4xl md:text-5xl font-light">Внимание к деталям</h2>
			<div v-html="SVGdecor" class="flex items-center justify-center"></div>
		</div>
		<div class="flex flex-col gap-4 justify-center items-center gap-4">
			<div class="">
				<img src="/assets/vnimanie-k-detalyam.svg" alt="Внимание к деталям" class="w-full h-auto" />
			</div>
			<div class="grid grid-cols-1 md:grid-cols-2 gap-8 w-full">
				<div v-for="detail in detailsAndEffect" :key="detail.detail" class="flex flex-col gap-2 p-6 border border-gray-200 bg-white rounded-xl">
					<p class="text-dark-green font-medium">{{ detail.detail }}</p>
					<p class="text-gray-500">{{ detail.effect }}</p>
				</div>
			</div>
		</div>
	</section>

	<section class="montserrat container max-w-screen-2xl my-32 px-2 md:px-4">
		<div class="flex flex-row gap-4 mb-8 justify-between items-center">
			<h2 class="text-4xl md:text-5xl font-light">Внимание к деталям</h2>
			<div v-html="SVGdecor" class="flex items-center justify-center"></div>
		</div>

		<div class="grid md:grid-cols-2 lg:grid-cols-3 justify-center gap-8">
			<div class="group p-6 bg-white rounded-2xl transition duration-300 border border-gray-200 hover:border-gray-300">
				<!-- <div class="text-emerald-600 text-3xl mb-4">💨</div> -->
				<h3 class="font-medium mb-2">Прочность и устойчивость</h3>
				<p class="text-gray-500">Выдерживают значительные ветровые, снеговые и другие нагрузки.</p>
			</div>

			<div class="group p-6 bg-white rounded-2xl transition duration-300 border border-gray-200 hover:border-gray-300">
				<!-- <div class="text-emerald-600 text-3xl mb-4">🧹</div> -->
				<h3 class="font-medium mb-2">Легкость в уходе</h3>
				<p class="text-gray-500">Не требуют сложного обслуживания или специальных средств.</p>
			</div>

			<div class="group p-6 bg-white rounded-2xl transition duration-300 border border-gray-200 hover:border-gray-300">
				<!-- <div class="text-emerald-600 text-3xl mb-4">🔧</div> -->
				<h3 class="font-medium mb-2">Премиум фурнитура</h3>
				<p class="text-gray-500">Фурнитура из нержавеющей стали обеспечивает надежность и долговечность.</p>
			</div>

			<div class="group p-6 bg-white rounded-2xl transition duration-300 border border-gray-200 hover:border-gray-300">
				<!-- <div class="text-emerald-600 text-3xl mb-4">🏗️</div> -->
				<h3 class="font-medium mb-2">Алюминиевый профиль</h3>
				<p class="text-gray-500">Долговечный межстворочный алюминиевый профиль для надежных конструкций.</p>
			</div>

			<div class="group p-6 bg-white rounded-2xl transition duration-300 border border-gray-200 hover:border-gray-300">
				<!-- <div class="text-emerald-600 text-3xl mb-4">🪟</div> -->
				<h3 class="font-medium mb-2">Закалённое стекло</h3>
				<p class="text-gray-500">Стекло толщиной 10 мм позволяет изготавливать створки высотой до 3,2 м.</p>
			</div>
		</div>

		<div class="flex justify-center">
			<!-- Certification section -->
    		<div class="mt-8 inline-block bg-gradient-to-r from-dark-green to-green-900 text-white rounded-3xl shadow-lg p-10 relative overflow-hidden">
    			<div class="absolute top-0 left-0 w-full h-full bg-white opacity-5 rounded-3xl"></div>
    			<h3 class="text-2xl md:text-3xl font-bold mb-6 relative">Система сертифицирована и внесена в реестр СТО!</h3>
    			<ul class="space-y-3 text-lg relative">
    				<li class="flex items-center gap-3"><CheckIcon class="w-12 h-12 text-light-gold" /> <span>Наша система и материалы предполагают долгую и безпроблемную эксплуатацию.</span></li>
    				<li class="flex items-center gap-3"><CheckIcon class="w-12 h-12 text-light-gold" /> <span>Мы постоянно совершенствуем систему с расчетом на долговечное использование.</span></li>
    				<li class="flex items-center gap-3"><CheckIcon class="w-12 h-12 text-light-gold" /> <span>Конструкции имеют безупречные характеристики, не требуя специального обслуживания.</span></li>
    			</ul>
    		</div>
		</div>
	</section>
</template>
