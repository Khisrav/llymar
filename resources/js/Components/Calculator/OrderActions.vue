<script setup lang="ts">
import { EllipsisVertical } from 'lucide-vue-next';
import Button from '../ui/button/Button.vue';
</script>

<template>
    <div class="h-6 md:h-10"></div>
	<div class="z-20 fixed bottom-0 md:bottom-2 left-1/2 w-full max-w-96 transform -translate-x-1/2 bg-white dark:bg-slate-900 p-2 px-4 md:p-4 border md:rounded-2xl shadow-sm">
		<div class="flex items-center justify-between">
			<span class="font-bold text-xl text-primary">123 456 ₽</span>

			<div class="flex gap-2 md:gap-4 items-center">
			    <Button variant="outline" size="icon">
			        <EllipsisVertical/>
			    </Button>
			    <Button>
			        <span class="text-base">Заказать</span>
			    </Button>
			</div>
		</div>
	</div>
</template>
