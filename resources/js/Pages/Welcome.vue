<script setup>
import { ref } from 'vue';
import Button from '../Components/ui/button/Button.vue';
import GuestHeaderLayout from '../Layouts/GuestHeaderLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <GuestHeaderLayout />
    <Head title="Welcome" />
    <section class="bg-white dark:bg-gray-900">
    <div class="grid max-w-screen-xl px-4 py-8 mx-auto lg:gap-8 xl:gap-0 lg:py-16 lg:grid-cols-12 min-h-[calc(100vh-56px)]">
        <div class="mr-auto place-self-center lg:col-span-7 mb-8">
            <h1 class="mb-4 text-4xl font-extrabold tracking-tight leading-none md:text-5xl xl:text-6xl dark:text-white">Система безрамного остекления</h1>
            <p class="font-light text-gray-500 md:text-lg lg:text-xl dark:text-gray-400">Сайт на стадии разработки</p>
            <!-- phone number -->
            <p class="text-gray-500 dark:text-gray-400 underline my-4 font-medium text-xl"><Link href="tel:+7 (989) 804-12-34">+7 (989) 804-12-34</Link></p>            
            
            <Link href="/register" class="mb-6 lg:mt-8">
                <Button variant>
                    Регистрация партнеров
                    <svg class="w-5 h-5 ml-2 -mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                </Button>
            </Link>
        </div>
        <div class="lg:mt-0 lg:col-span-5 place-self-center">
            <img src="/assets/hero.jpg" alt="mockup" class="rounded-xl">
        </div>                
    </div>
</section>
</template>