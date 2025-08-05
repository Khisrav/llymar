<script setup>
import { ref } from 'vue';
import Button from '../Components/ui/button/Button.vue';
import GuestHeaderLayout from '../Layouts/GuestHeaderLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head>
        <title>Безрамные системы остекления</title>
    </Head>
    
    <section class="bg-[url('/assets/hero.jpg')] bg-cover bg-center text-white relative min-h-screen">
        <div class="bg-[#23322D] bg-opacity-75 min-h-screen flex flex-col">
            <GuestHeaderLayout />
            
            <div class="container max-w-screen-2xl px-4 flex-1 flex flex-col justify-center">
                <div class="flex flex-col gap-6 md:gap-8 py-12 md:py-0">
                    <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl xl:text-7xl uppercase font-light leading-tight">
                        Премиальные алюминиевые системы безрамного остекления
                    </h1>
                    <div class="mt-4">
                        <button class="bg-light-gold hover:bg-light-gold/90 transition-colors flex items-center flex-row gap-3 md:gap-4 text-black py-3 md:py-4 px-5 md:px-6 font-medium rounded-full text-sm md:text-base w-full sm:w-auto justify-center sm:justify-start">
                            <span class="montserrat">Получить консультацию</span>
                            <img src="/assets/arrow.svg" alt="">
                        </button>
                    </div>
                </div>
                
                <!-- Scroll indicator - only on larger screens -->
                <div class="hidden lg:block absolute bottom-8 xl:bottom-12 right-8 xl:right-20">
                    <img src="/assets/scrolldown.svg" alt="" class="animate-bounce">
                </div>
            </div>
        </div>
    </section>
    
    <section>
        
    </section>
</template>

<style>

</style>