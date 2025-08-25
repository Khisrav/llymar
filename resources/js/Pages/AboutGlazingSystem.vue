<script setup>
import GuestHeaderLayout from '../Layouts/GuestHeaderLayout.vue';

const SVGdecor = '<svg width="86" height="16" viewBox="0 0 86 16" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0_705_180)"><path d="M6.0088 6H86V0H0L6.0088 6Z" fill="#23322D"/><path d="M16.0244 16H86V10H10.0146L16.0244 16Z" fill="#23322D"/></g><defs><clipPath id="clip0_705_180"><rect width="86" height="16" fill="white" transform="matrix(-1 0 0 1 86 0)"/></clipPath></defs></svg>'
</script>

<template>
    <GuestHeaderLayout :openConsultationDialog="openConsultationDialog" />

    <div class="container max-w-screen-2xl mt-16 mb-8 md:mt-32 md:mb-16 px-2 md:px-4 flex-1 flex flex-col justify-center">
        <h1 class="text-center text-4xl md:text-6xl font-light">О системе безрамочного остекления</h1>
    </div>
    
    <section class="container max-w-screen-2xl my-32 px-2 md:px-4">
        <div class="flex flex-row gap-4 justify-between items-center">
            <h2 class="text-4xl md:text-5xl font-light">Внимание к деталям</h2>
            <div v-html="SVGdecor" class="flex items-center justify-center"></div>
        </div>
    </section>
</template>