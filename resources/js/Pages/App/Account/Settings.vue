<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import AuthenticatedHeaderLayout from '../../../Layouts/AuthenticatedHeaderLayout.vue';
import Label from '../../../Components/ui/label/Label.vue';
import Input from '../../../Components/ui/input/Input.vue';
import { ref } from 'vue';
import { User } from '../../../lib/types';
import { vMaska } from 'maska/vue';
import Button from '../../../Components/ui/button/Button.vue';
import Separator from '../../../Components/ui/separator/Separator.vue';
import { useForm } from '@inertiajs/vue3';
import { Toaster } from '../../../Components/ui/sonner';
import { toast } from 'vue-sonner';

const user = ref(usePage().props.user as User);

const form = useForm({
	name: user.value.name,
	company: user.value.company,
	email: user.value.email,
	phone: user.value.phone,
	address: user.value.address,
	telegram: user.value.telegram,
	inn: user.value.inn,
	kpp: user.value.kpp,
	bik: user.value.bik,
	bank: user.value.bank,
	legal_address: user.value.legal_address,
	current_account: user.value.current_account,
	correspondent_account: user.value.correspondent_account,
})

const updateUser = () => {
	form.post('/app/account/settings', {
		preserveScroll: true,
		onSuccess: () => {
			toast("Настройки сохранены");
		},
	});
}
</script>

<template>
	<Head title="Настройки аккаунта" />
	<AuthenticatedHeaderLayout />
	
	<Toaster />

	<div class="container p-0 md:p-4">
	    <form @submit.prevent="updateUser" class="p-4 md:p-8 md:mt-8 md:border rounded-2xl bg-background">
			<h2 class="text-3xl font-semibold mb-6">Настройки</h2>
			<h3 class="text-xl font-semibold mb-4 text-muted-foreground">Основная информация</h3>

			<div class="grid md:grid-cols-2 gap-8">
			    <div>
			        <Label class="font-bold">Ф.И.О.</Label>
    			    <p class="text-sm text-muted-foreground mb-4">Это поле необходимо для персонализации уведомлений и документов.</p>
    			    <Input v-model="form.name" type="text" required class="shadow-sm" />
			    </div>

			    <div>
			        <Label class="font-bold">Фактический адрес</Label>
    			    <p class="text-sm text-muted-foreground mb-4">Фактический адрес необходим для доставки товаров и документов.</p>
    			    <Input v-model="form.address" type="text" class="shadow-sm" />
			    </div>
			</div>

		    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-8">
		        <div>
			        <Label class="font-bold">Электронная почта</Label>
    			    <p class="text-sm text-muted-foreground mb-4">Электронная почта используется для отправки важных уведомлений и подтверждений.</p>
    			    <Input v-model="form.email" type="email" required class="shadow-sm" />
			    </div>

			    <div>
			        <Label class="font-bold">Номер Телефона</Label>
    			    <p class="text-sm text-muted-foreground mb-4">Номер телефона необходим для быстрой связи в случае срочных вопросов.</p>
    			    <Input v-model="form.phone" type="tel" v-maska="'+7 (###) ###-##-##'" required class="shadow-sm" />
			    </div>

			    <div>
			        <Label class="font-bold">Ник в Telegram</Label>
    			    <p class="text-sm text-muted-foreground mb-4">Ник в Telegram используется для быстрой связи в случае срочных вопросов.</p>
    			    <Input v-model="form.telegram" type="text" required class="shadow-sm" />
			    </div>

				<div>
					<Label class="font-bold">Организация</Label>
					<p class="text-sm text-muted-foreground mb-4">Название организации используется для правильного оформления документов и счетов.</p>
					<Input v-model="form.company" type="text" class="shadow-sm" />
				</div>
		    </div>

			<Separator class="my-12" />

			<h3 class="text-xl font-semibold mb-4 text-muted-foreground">Реквизиты</h3>

			<div class="grid md:grid-cols-2 gap-8 mb-8">
			    <div class="grid md:grid-cols-2 gap-8">
			        <div>
				        <Label class="font-bold">ИНН</Label>
	    			    <p class="text-sm text-muted-foreground mb-4">ИНН необходим для правильного оформления налоговых документов.</p>
	    			    <Input v-model="form.inn" type="text" required class="shadow-sm" />
				    </div>
	
				    <div>
				        <Label class="font-bold">КПП</Label>
	    			    <p class="text-sm text-muted-foreground mb-4">КПП необходим для правильного оформления налоговых документов.</p>
	    			    <Input v-model="form.kpp" type="text" required class="shadow-sm" />
				    </div>
			    </div>

			    <div>
			        <Label class="font-bold">Расчетный счет</Label>
    			    <p class="text-sm text-muted-foreground mb-4">Расчетный счет используется для перевода средств и оформления платежных документов.</p>
    			    <Input v-model="form.current_account" type="text" required class="shadow-sm" />
			    </div>

			    <div>
			        <Label class="font-bold">Корреспондентский счет</Label>
    			    <p class="text-sm text-muted-foreground mb-4">Необходим для правильного оформления платежных документов.</p>
    			    <Input v-model="form.correspondent_account" type="text" class="shadow-sm" />
			    </div>

			    <div>
			        <Label class="font-bold">БИК</Label>
    			    <p class="text-sm text-muted-foreground mb-4">БИК необходим для правильного оформления платежных документов.</p>
    			    <Input v-model="form.bik" type="text" class="shadow-sm" />
			    </div>

			    <div>
			        <Label class="font-bold">Банк</Label>
    			    <p class="text-sm text-muted-foreground mb-4">Название банка используется для правильного оформления платежных документов.</p>
    			    <Input v-model="form.bank" type="text" class="shadow-sm" />
			    </div>

			    <div>
			        <Label class="font-bold">Юридический адрес</Label>
    			    <p class="text-sm text-muted-foreground mb-4">Юридический адрес необходим для правильного оформления документов и счетов.</p>
    			    <Input v-model="form.legal_address" type="text" class="shadow-sm" />
			    </div>
			</div>

			<div class="text-right">
			    <Button :disabled="form.processing">Сохранить</Button>
			</div>
	    </form>
	</div>
</template>
