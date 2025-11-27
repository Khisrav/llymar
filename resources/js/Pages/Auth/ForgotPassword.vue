<script setup lang="ts">
import { useForm } from "@inertiajs/vue3";
import Label from "../../Components/ui/label/Label.vue";
import Input from "../../Components/ui/input/Input.vue";
import Button from "../../Components/ui/button/Button.vue";
import { Head, Link } from "@inertiajs/vue3";

defineProps<{
	status?: string;
}>();

const form = useForm({
	email: "",
});

const submit = () => {
	form.post('/forgot-password');
};
</script>

<template>
	<Head>
		<title>Восстановление пароля</title>
	</Head>
	<div class="w-full lg:grid lg:min-h-[600px] lg:grid-cols-2 xl:min-h-[800px]">
		<div class="flex items-center justify-center py-12">
			<div class="mx-auto grid w-[350px] gap-6">
				<div class="grid gap-2 text-center">
					<h1 class="text-3xl font-bold">Забыли пароль?</h1>
					<p class="text-balance text-muted-foreground">
						Введите ваш email и мы отправим вам ссылку для восстановления пароля
					</p>
				</div>

				<div v-if="status" class="text-sm text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20 p-3 rounded-md">
					{{ status }}
				</div>

				<form @submit.prevent="submit" class="space-y-4">
					<div class="grid gap-2">
						<Label for="email">Email</Label>
						<Input 
							v-model="form.email" 
							id="email" 
							type="email" 
							placeholder="m@example.com" 
							required 
							autofocus
						/>
						<div v-if="form.errors.email" class="text-xs text-destructive">
							{{ form.errors.email }}
						</div>
					</div>

					<Button type="submit" class="w-full" :disabled="form.processing">
						Отправить ссылку для восстановления
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


