<script setup lang="ts">
import { ref, computed } from "vue";
import { Head, router } from "@inertiajs/vue3";
import AuthenticatedHeaderLayout from "../../../Layouts/AuthenticatedHeaderLayout.vue";
import {
	Table,
	TableBody,
	TableCell,
	TableHead,
	TableHeader,
	TableRow,
} from "../../../Components/ui/table";
import {
	Card,
	CardContent,
	CardDescription,
	CardHeader,
	CardTitle,
} from "../../../Components/ui/card";
import { Badge } from "../../../Components/ui/badge";
import { toast } from "vue-sonner";
import { Toaster } from "../../../Components/ui/sonner";
import axios from "axios";
import {
	Dialog,
	DialogContent,
	DialogDescription,
	DialogFooter,
	DialogHeader,
	DialogTitle,
} from "../../../Components/ui/dialog";
import { Label } from "../../../Components/ui/label/";
import { Input } from "../../../Components/ui/input/";
import { Button } from "../../../Components/ui/button/";
import { PencilIcon, TrashIcon, LinkIcon, CopyIcon, ClockIcon, UserIcon, CheckCircleIcon, XCircleIcon } from "lucide-vue-next";
import { RegistrationLink } from "../../../lib/types";

const props = defineProps<{
	registrationLinks: Array<RegistrationLink>;
	canManageLinks: boolean;
}>();

const showCreateLinkDialog = ref(false);
const showEditLinkDialog = ref(false);
const showLinkUrlDialog = ref(false);
const editingLink = ref<RegistrationLink | null>(null);
const selectedLinkUrl = ref("");

const hasLinks = computed(() => props.registrationLinks.length > 0);

const newLink = ref({
	reward_fee: 0,
});

const createLink = () => {
	router.post("/app/registration-links", newLink.value, {
		onSuccess: () => {
			toast.success("Ссылка регистрации успешно создана");
			showCreateLinkDialog.value = false;
			resetNewLink();
		},
		onError: (errors) => {
			toast.error("Не удалось создать ссылку регистрации");
		},
	});
};

const updateLink = () => {
	if (!editingLink.value) return;

	router.put(`/app/registration-links/${editingLink.value.id}`, {
		reward_fee: editingLink.value.reward_fee,
	}, {
		onSuccess: () => {
			toast.success("Ссылка регистрации успешно обновлена");
			showEditLinkDialog.value = false;
			editingLink.value = null;
		},
		onError: (errors) => {
			toast.error("Не удалось обновить ссылку регистрации");
		},
	});
};

const deleteLink = (linkId: number) => {
	if (!confirm("Вы уверены, что хотите удалить эту ссылку регистрации?")) {
		return;
	}

	axios.delete(`/app/registration-links/${linkId}`).then(() => {
		// Remove link from the list
		const index = props.registrationLinks.findIndex(link => link.id === linkId);
		if (index > -1) {
			props.registrationLinks.splice(index, 1);
		}
		toast.success("Ссылка регистрации успешно удалена");
	}).catch((error) => {
		console.error(error);
		toast.error("Произошла ошибка при удалении ссылки регистрации");
	});
};

const editLink = (link: RegistrationLink) => {
	editingLink.value = { ...link };
	showEditLinkDialog.value = true;
};

const copyLinkUrl = (url: string) => {
	navigator.clipboard.writeText(url).then(() => {
		toast.success("Ссылка скопирована в буфер обмена");
	}).catch(() => {
		toast.error("Не удалось скопировать ссылку");
	});
};

const showLinkUrl = (url: string) => {
	selectedLinkUrl.value = url;
	showLinkUrlDialog.value = true;
};

const resetNewLink = () => {
	newLink.value = {
		reward_fee: 0,
	};
};

const formatDate = (dateString: string) => {
	const date = new Date(dateString);
	return date.toLocaleString('ru-RU', {
		year: 'numeric',
		month: '2-digit',
		day: '2-digit',
		hour: '2-digit',
		minute: '2-digit',
	});
};

const getDefaultExpirationDate = () => {
	const date = new Date();
	date.setHours(date.getHours() + 24);
	return date.toISOString().slice(0, 16);
};

const getStatusBadgeVariant = (status: string) => {
	switch (status) {
		case 'active':
			return 'default';
		case 'used':
			return 'outline';
		case 'expired':
			return 'destructive';
		default:
			return 'outline';
	}
};

const getStatusLabel = (status: string) => {
	switch (status) {
		case 'active':
			return 'Активна';
		case 'used':
			return 'Использована';
		case 'expired':
			return 'Истекла';
		default:
			return status;
	}
};
</script>

<template>
	<Head>
		<title>Ссылки регистрации</title>
	</Head>
	<AuthenticatedHeaderLayout />
	
	<Toaster />

	<div class="container mx-auto p-0 md:p-6 lg:p-8">
		<div class="bg-background md:border md:rounded-2xl md:shadow-sm overflow-hidden">
			<!-- Header Section -->
			<div class="p-4 md:p-8">
				<div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
					<div class="flex items-center gap-3">
						<div class="p-2 bg-primary/10 rounded-lg">
							<LinkIcon class="h-6 w-6 text-primary" />
						</div>
						<div>
							<h1 class="text-2xl font-bold tracking-tight">Ссылки регистрации</h1>
							<p class="text-sm text-muted-foreground mt-1">
								{{ hasLinks ? `${registrationLinks.length} ссылок` : 'Управляйте ссылками регистрации' }}
							</p>
						</div>
					</div>

					<Button @click="showCreateLinkDialog = true" variant="outline">
						<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
						</svg>
						Создать ссылку
					</Button>
				</div>
			</div>

			<!-- Empty State -->
			<div v-if="!hasLinks" class="p-8 md:p-16">
				<div class="text-center max-w-md mx-auto">
					<div class="mb-6">
						<div class="mx-auto w-20 h-20 bg-muted/50 rounded-full flex items-center justify-center mb-4">
							<LinkIcon class="h-10 w-10 text-muted-foreground" />
						</div>
					</div>
					<h3 class="text-xl font-semibold mb-2">Ссылок регистрации пока нет</h3>
					<p class="text-muted-foreground mb-6">Создайте первую ссылку для регистрации дилеров</p>
					<Button @click="showCreateLinkDialog = true" size="lg" class="gap-2">
						<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
						</svg>
						Создать ссылку
					</Button>
				</div>
			</div>

			<!-- Links Content -->
			<div v-else class="p-4 md:p-6 lg:p-8" style="padding-top: 0px !important;">
				<!-- Mobile Cards View -->
				<div class="md:hidden space-y-4">
					<div 
						v-for="(link, index) in registrationLinks" 
						:key="link.id" 
						class="border rounded-xl p-4 bg-card transition-all duration-200 hover:border-border/60"
						:style="{ animationDelay: `${index * 50}ms` }"
					>
						<div class="flex items-center justify-between gap-2 mb-3">
							<Badge :variant="getStatusBadgeVariant(link.status)">
								{{ getStatusLabel(link.status) }}
							</Badge>
							<span class="text-xs text-muted-foreground">ID: {{ link.id }}</span>
						</div>

						<div class="space-y-3 mb-4">
							<div class="flex items-center gap-2 text-sm">
								<UserIcon class="h-4 w-4 text-muted-foreground" />
								<div>
									<div class="font-medium">{{ link.creator.name }}</div>
									<div class="text-xs text-muted-foreground">{{ link.creator.email }}</div>
								</div>
							</div>

							<div class="flex items-center gap-2 text-sm">
								<span class="text-muted-foreground">Комиссия:</span>
								<span class="font-semibold">{{ link.reward_fee }}%</span>
							</div>

							<div class="flex items-center gap-2 text-sm">
								<ClockIcon class="h-4 w-4 text-muted-foreground" />
								<div>
									<div class="text-xs text-muted-foreground">Истекает:</div>
									<div>{{ formatDate(link.expires_at) }}</div>
								</div>
							</div>

							<div v-if="link.registered_user" class="flex items-center gap-2 text-sm">
								<CheckCircleIcon class="h-4 w-4 text-green-600" />
								<div>
									<div class="text-xs text-muted-foreground">Зарегистрирован:</div>
									<div class="font-medium">{{ link.registered_user.name }}</div>
								</div>
							</div>
						</div>

						<div class="flex justify-between gap-2 pt-3 border-t border-t-gray-200 dark:border-t-gray-800">
							<Button v-if="link.is_valid" @click="showLinkUrl(link.url)" variant="outline" class="flex-1">
								<LinkIcon class="h-4 w-4 mr-1" />
								Скопировать
							</Button>
							<div class="flex gap-2">
								<Button v-if="!link.is_used" @click="editLink(link)" size="icon" variant="outline">
									<PencilIcon class="h-4 w-4" />
								</Button>
								<Button @click="deleteLink(link.id)" size="icon" variant="secondary" class="hover:bg-destructive/10 hover:text-destructive bg-gray-100 dark:bg-gray-800">
									<TrashIcon class="h-4 w-4" />
								</Button>
							</div>
						</div>
					</div>
				</div>

				<!-- Desktop Table View -->
				<div class="hidden md:block">
					<div class="rounded-lg border overflow-hidden shadow-sm">
						<Table>
							<TableHeader>
								<TableRow class="bg-muted/30 hover:bg-muted/30">
									<TableHead class="font-semibold text-sm">ID</TableHead>
									<TableHead class="font-semibold text-sm">Создатель</TableHead>
									<TableHead class="font-semibold text-sm">Комиссия</TableHead>
									<TableHead class="font-semibold text-sm">Статус</TableHead>
									<TableHead class="font-semibold text-sm">Истекает</TableHead>
									<TableHead class="font-semibold text-sm">Зарегистрирован</TableHead>
									<TableHead class="font-semibold text-sm text-right">Действия</TableHead>
								</TableRow>
							</TableHeader>
							<TableBody>
								<TableRow 
									v-for="link in registrationLinks" 
									:key="link.id" 
									class="group hover:bg-muted/20 transition-colors duration-150"
								>
									<TableCell class="font-mono text-sm">{{ link.id }}</TableCell>
									<TableCell>
										<div class="space-y-1">
											<p class="font-medium">{{ link.creator.name }}</p>
											<p class="text-xs text-muted-foreground">{{ link.creator.email }}</p>
										</div>
									</TableCell>
									<TableCell class="font-semibold">{{ link.reward_fee }}%</TableCell>
									<TableCell>
										<Badge :variant="getStatusBadgeVariant(link.status)">
											{{ getStatusLabel(link.status) }}
										</Badge>
									</TableCell>
									<TableCell class="text-sm">
										<div>{{ formatDate(link.expires_at) }}</div>
									</TableCell>
									<TableCell>
										<div v-if="link.registered_user" class="space-y-1">
											<p class="font-medium text-sm">{{ link.registered_user.name }}</p>
											<p class="text-xs text-muted-foreground">{{ link.registered_user.email }}</p>
										</div>
										<span v-else class="text-muted-foreground text-sm">—</span>
									</TableCell>
									<TableCell class="text-right">
										<div class="flex justify-end gap-2">
											<Button v-if="link.is_valid" @click="showLinkUrl(link.url)" size="icon" variant="outline" title="Просмотреть ссылку">
												<LinkIcon class="h-4 w-4" />
											</Button>
											<Button v-if="!link.is_used" @click="editLink(link)" size="icon" variant="outline" title="Редактировать">
												<PencilIcon class="h-4 w-4" />
											</Button>
											<Button @click="deleteLink(link.id)" size="icon" variant="secondary" class="hover:bg-destructive/10 hover:text-destructive bg-gray-100 dark:bg-gray-800" title="Удалить">
												<TrashIcon class="h-4 w-4" />
											</Button>
										</div>
									</TableCell>
								</TableRow>
							</TableBody>
						</Table>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Create Link Dialog -->
	<Dialog v-model:open="showCreateLinkDialog">
		<DialogContent class="max-w-md">
			<DialogHeader>
				<DialogTitle>Создать ссылку регистрации</DialogTitle>
				<DialogDescription>Заполните форму для создания новой ссылки регистрации дилера</DialogDescription>
			</DialogHeader>
			<div class="space-y-4">
				<div class="space-y-2">
					<Label>Комиссия (%) *</Label>
					<Input v-model.number="newLink.reward_fee" type="number" min="0" max="100" step="0.01" required />
				</div>
			</div>
			<DialogFooter class="flex gap-2">
				<Button variant="outline" @click="showCreateLinkDialog = false; resetNewLink()">Отмена</Button>
				<Button @click="createLink">Создать</Button>
			</DialogFooter>
		</DialogContent>
	</Dialog>

	<!-- Edit Link Dialog -->
	<Dialog v-model:open="showEditLinkDialog">
		<DialogContent class="max-w-md" v-if="editingLink">
			<DialogHeader>
				<DialogTitle>Редактировать ссылку регистрации</DialogTitle>
				<DialogDescription>Обновите информацию о ссылке регистрации</DialogDescription>
			</DialogHeader>
			<div class="space-y-4">
				<div class="space-y-2">
					<Label>Комиссия (%) *</Label>
					<Input v-model.number="editingLink.reward_fee" type="number" min="0" max="100" step="0.01" required />
				</div>
			</div>
			<DialogFooter class="flex gap-2">
				<Button variant="outline" @click="showEditLinkDialog = false">Отмена</Button>
				<Button @click="updateLink">Сохранить</Button>
			</DialogFooter>
		</DialogContent>
	</Dialog>

	<!-- Show Link URL Dialog -->
	<Dialog v-model:open="showLinkUrlDialog">
		<DialogContent class="max-w-lg">
			<DialogHeader>
				<DialogTitle>Ссылка для регистрации</DialogTitle>
				<DialogDescription>Скопируйте эту ссылку и отправьте дилеру</DialogDescription>
			</DialogHeader>
			<div class="space-y-4">
				<div class="p-4 bg-muted rounded-lg font-mono text-sm break-all">
					{{ selectedLinkUrl }}
				</div>
				<Button @click="copyLinkUrl(selectedLinkUrl)" class="w-full">
					<CopyIcon class="h-4 w-4 mr-2" />
					Скопировать ссылку
				</Button>
			</div>
			<DialogFooter>
				<Button variant="outline" @click="showLinkUrlDialog = false">Закрыть</Button>
			</DialogFooter>
		</DialogContent>
	</Dialog>
</template>

