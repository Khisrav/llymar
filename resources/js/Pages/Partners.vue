<script setup>
import { Head } from "@inertiajs/vue3";
import GuestHeaderLayout from "@/Layouts/GuestHeaderLayout.vue";

const openConsultationDialog = () => {};
</script>

<template>
	<Head>
		<title>Партнёрам</title>
	</Head>

	<GuestHeaderLayout :openConsultationDialog="openConsultationDialog" />
</template>
