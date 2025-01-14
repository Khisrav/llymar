<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import AuthenticatedHeaderLayout from '../../../Layouts/AuthenticatedHeaderLayout.vue';
import Label from '../../../Components/ui/label/Label.vue';
import Input from '../../../Components/ui/input/Input.vue';
import { ref } from 'vue';
import { User } from '../../../lib/types';
import { vMaska } from 'maska/vue';
import Button from '../../../Components/ui/button/Button.vue';

const user = ref(usePage().props.user as User);
</script>

<template>
	<Head title="История заказов" />
	<AuthenticatedHeaderLayout />
	
	<div class="container p-4 md:p-8">
	    <div class="md:p-8 md:mt-8 md:border rounded-2xl">
			<h2 class="text-3xl font-semibold mb-6">Настройки</h2>
			
			<div class="grid md:grid-cols-2 gap-8 mb-8">
			    <div>
			        <Label class="font-bold">Ф.И.О.</Label>
    			    <p class="text-sm text-muted-foreground mb-4">Введите свое полное имя (фамилия, имя и отчество при наличии)</p>
    			    <Input v-model="user.name" type="text" required class="shadow-sm" />
			    </div>
			    
			    <div>
			        <Label class="font-bold">Электронная почта</Label>
    			    <p class="text-sm text-muted-foreground mb-4">Введите электронную почту, на нее будут приходить уведомления</p>
    			    <Input v-model="user.email" type="email" required class="shadow-sm" />
			    </div>
			    
			    <div>
			        <Label class="font-bold">Номер Телефона</Label>
    			    <p class="text-sm text-muted-foreground mb-4">Введите электронную почту, на нее будут приходить уведомления</p>
    			    <Input v-model="user.phone" type="tel" v-maska="'+7 (###) ###-##-##'" required class="shadow-sm" />
			    </div>
			    
			    <div>
			        <Label class="font-bold">Организация</Label>
    			    <p class="text-sm text-muted-foreground mb-4">Введите электронную почту, на нее будут приходить уведомления</p>
    			    <Input v-model="user.company" type="text" class="shadow-sm" />
			    </div>
			</div>
			
			<div class="text-right">
			    <Button>Сохранить</Button>
			</div>
	    </div>
	</div>
</template>