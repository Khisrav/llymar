<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AuthenticatedHeaderLayout from '../../Layouts/AuthenticatedHeaderLayout.vue';
import { Toaster } from 'vue-sonner';


</script>

<template>
    <Head title="Черталка" />
	<AuthenticatedHeaderLayout />

	<Toaster />
	
	<div class="container p-0 md:p-4">
		<div class="p-4 md:p-8 md:mt-8 md:border rounded-2xl bg-background">
			<h2 class="text-3xl font-semibold mb-6">Чертальник</h2>
            
		</div>
	</div>
</template>