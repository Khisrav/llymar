<script setup lang="ts">
import { useForm } from "@inertiajs/vue3";
import Label from "../../Components/ui/label/Label.vue";
import Input from "../../Components/ui/input/Input.vue";
import Button from "../../Components/ui/button/Button.vue";
import { Head, Link } from "@inertiajs/vue3";

const props = defineProps<{
	email: string;
	token: string;
}>();

const form = useForm({
	token: props.token,
	email: props.email,
	password: "",
	password_confirmation: "",
});

const submit = () => {
	form.post('/reset-password', {
		onFinish: () => {
			form.reset('password', 'password_confirmation');
		},
	});
};
</script>

<template>
	<Head>
		<title>Сброс пароля</title>
	</Head>
	<div class="w-full lg:grid lg:min-h-[600px] lg:grid-cols-2 xl:min-h-[800px]">
		<div class="flex items-center justify-center py-12">
			<div class="mx-auto grid w-[350px] gap-6">
				<div class="grid gap-2 text-center">
					<h1 class="text-3xl font-bold">Сброс пароля</h1>
					<p class="text-balance text-muted-foreground">
						Введите новый пароль для вашего аккаунта
					</p>
				</div>

				<form @submit.prevent="submit" class="space-y-4">
					<div class="grid gap-2">
						<Label for="email">Email</Label>
						<Input 
							v-model="form.email" 
							id="email" 
							type="email" 
							required 
							readonly
							class="bg-muted"
						/>
						<div v-if="form.errors.email" class="text-xs text-destructive">
							{{ form.errors.email }}
						</div>
					</div>

					<div class="grid gap-2">
						<Label for="password">Новый пароль</Label>
						<Input 
							v-model="form.password" 
							id="password" 
							type="password" 
							required 
							autofocus
						/>
						<div v-if="form.errors.password" class="text-xs text-destructive">
							{{ form.errors.password }}
						</div>
					</div>

					<div class="grid gap-2">
						<Label for="password_confirmation">Подтвердите пароль</Label>
						<Input 
							v-model="form.password_confirmation" 
							id="password_confirmation" 
							type="password" 
							required
						/>
						<div v-if="form.errors.password_confirmation" class="text-xs text-destructive">
							{{ form.errors.password_confirmation }}
						</div>
					</div>

					<Button type="submit" class="w-full" :disabled="form.processing">
						Сбросить пароль
					</Button>
				</form>

				<div class="text-center text-sm">
					<Link href="/login" class="underline">
						Вернуться к входу
					</Link>
				</div>
			</div>
		</div>
		<div class="hidden bg-muted lg:block">
			<img 
				src="/assets/hero.jpg" 
				alt="Image" 
				class="h-screen w-full object-cover dark:brightness-[0.5] dark:grayscale" 
			/>
		</div>
	</div>
</template>


