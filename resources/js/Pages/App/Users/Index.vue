<script setup lang="ts">
import { ref, computed } from "vue";
import { Head, router } from "@inertiajs/vue3";
import { vMaska } from "maska/vue";
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
	Select,
	SelectContent,
	SelectGroup,
	SelectItem,
	SelectTrigger,
	SelectValue,
} from "../../../Components/ui/select";
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
import { Textarea } from "../../../Components/ui/textarea/";
import { Button } from "../../../Components/ui/button/";
import { Switch } from "../../../Components/ui/switch";
import { PencilIcon, TrashIcon, UserIcon, PhoneIcon, BuildingIcon, MapPinIcon, EyeClosedIcon, EyeIcon, GlobeIcon } from "lucide-vue-next";

const props = defineProps<{
	childUsers: Array<any>;
	canManageUsers: boolean;
	userRole: string;
}>();

const showCreateUserDialog = ref(false);
const showEditUserDialog = ref(false);
const showRequisites = ref(false);
const showPassword = ref(false);
const editingUser = ref<any>(null);

const hasUsers = computed(() => props.childUsers.length > 0);

const newUser = ref({
	name: "",
	email: "",
	phone: "",
	address: "",
	city: "",
	website: "",
	role: "", // 'Dealer' or 'Manager'
});

// Determine what roles the current user can create
const canCreateRole = computed(() => {
	const role = props.userRole;
	if (role === 'Dealer' || role === 'Dealer Ch') {
		return { canCreate: ['Manager'], showSelect: false, defaultRole: 'Manager' };
	} else if (role === 'ROP' || role === 'Operator') {
		return { canCreate: ['Dealer'], showSelect: false, defaultRole: 'Dealer' };
	} else if (role === 'Dealer R' || role === 'Super-Admin') {
		return { canCreate: ['Dealer', 'Manager'], showSelect: true, defaultRole: '' };
	}
	return { canCreate: [], showSelect: false, defaultRole: '' };
});

const countries = {
	Армения: ["Арагацотн", "Арарат", "Армавир", "Гегаркуник", "Котайк", "Лори", "Ширак", "Сюник", "Тавуш", "Вайоц Дзор", "Ереван"],
	Беларусь: ["Брестская область", "Гомельская область", "Гродненская область", "Минская область", "Могилевская область", "Витебская область", "Минск"],
	Казахстан: ["Акмолинская область", "Актюбинская область", "Алматинская область", "Атырауская область", "Восточно-Казахстанская область", "Жамбылская область", "Карагандинская область", "Костанайская область", "Кызылординская область", "Мангистауская область", "Северо-Казахстанская область", "Павлодарская область", "Туркестанская область", "Западно-Казахстанская область", "Алматы", "Астана", "Шымкент"],
	Киргизия: ["Баткенская область", "Чуйская область", "Джалал-Абадская область", "Нарынская область", "Ошская область", "Таласская область", "Иссык-Кульская область", "Бишкек", "Ош"],
	Россия: ["Республика Адыгея", "Республика Башкортостан", "Республика Бурятия", "Республика Алтай", "Республика Дагестан", "Республика Ингушетия", "Кабардино-Балкарская Республика", "Республика Калмыкия", "Карачаево-Черкесская Республика", "Республика Карелия", "Республика Крым", "Республика Коми", "Республика Марий Эл", "Республика Мордовия", "Республика Саха (Якутия)", "Республика Северная Осетия-Алания", "Республика Татарстан", "Республика Тыва", "Удмуртская Республика", "Республика Хакасия", "Чеченская Республика", "Чувашская Республика", "Алтайский край", "Забайкальский край", "Камчатский край", "Краснодарский край", "Красноярский край", "Пермский край", "Приморский край", "Ставропольский край", "Хабаровский край", "Амурская область", "Архангельская область", "Астраханская область", "Белгородская область", "Брянская область", "Владимирская область", "Волгоградская область", "Вологодская область", "Воронежская область", "Ивановская область", "Иркутская область", "Калининградская область", "Калужская область", "Кемеровская область", "Кировская область", "Костромская область", "Курганская область", "Курская область", "Ленинградская область", "Липецкая область", "Магаданская область", "Московская область", "Мурманская область", "Нижегородская область", "Новгородская область", "Новосибирская область", "Омская область", "Оренбургская область", "Орловская область", "Пензенская область", "Псковская область", "Ростовская область", "Рязанская область", "Самарская область", "Саратовская область", "Сахалинская область", "Свердловская область", "Смоленская область", "Тамбовская область", "Тверская область", "Томская область", "Тульская область", "Тюменская область", "Ульяновская область", "Челябинская область", "Ярославская область", "Москва", "Санкт-Петербург", "Севастополь", "Еврейская автономная область", "Ненецкий автономный округ", "Ханты-Мансийский автономный округ", "Чукотский автономный округ", "Ямало-Ненецкий автономный округ"],
};

const editRegions = computed(() => {
	return editingUser.value?.country ? countries[editingUser.value.country as keyof typeof countries] || [] : [];
});

const openCreateDialog = () => {
	// Set default role based on user permissions
	newUser.value.role = canCreateRole.value.defaultRole;
	showCreateUserDialog.value = true;
};

const createUser = () => {
	router.post("/app/users", newUser.value, {
		onSuccess: () => {
			toast.success("Пользователь успешно создан");
			showCreateUserDialog.value = false;
			resetNewUser();
		},
		onError: (errors) => {
			toast.error("Не удалось создать пользователя");
		},
	});
};

const updateUser = () => {
	router.put(`/app/users/${editingUser.value.id}`, editingUser.value, {
		onSuccess: () => {
			toast.success("Пользователь успешно обновлен");
			showEditUserDialog.value = false;
			editingUser.value = null;
		},
		onError: (errors) => {
			toast.error("Не удалось обновить пользователя");
		},
	});
};

const deleteUser = (userId: number) => {
	if (!confirm("Вы уверены, что хотите удалить этого пользователя?")) {
		return;
	}

	axios.delete(`/app/users/${userId}`).then(() => {
		// Remove user from the list instead of refreshing page
		const index = props.childUsers.findIndex(user => user.id === userId);
		if (index > -1) {
			props.childUsers.splice(index, 1);
		}
		toast.success("Пользователь успешно удален");
	}).catch((error) => {
		console.error(error);
		toast.error("Произошла ошибка при удалении пользователя");
	});
};

const editUser = (user: any) => {
	editingUser.value = { ...user };
	showEditUserDialog.value = true;
};

const resetNewUser = () => {
	newUser.value = {
		name: "",
		email: "",
		phone: "",
		address: "",
		city: "",
		website: "",
		role: canCreateRole.value.defaultRole,
	};
	showPassword.value = false;
	showRequisites.value = false;
};
</script>

<template>
	<Head>
		<title>Управление пользователями</title>
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
							<UserIcon class="h-6 w-6 text-primary" />
						</div>
						<div>
							<h1 class="text-2xl font-bold tracking-tight">Пользователи</h1>
							<p class="text-sm text-muted-foreground mt-1">
								{{ hasUsers ? `${childUsers.length} пользователей` : 'Управляйте пользователями' }}
							</p>
						</div>
					</div>

					<Button @click="openCreateDialog" variant="outline">
						<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
						</svg>
						Создать пользователя
					</Button>
				</div>
			</div>

			<!-- Empty State -->
			<div v-if="!hasUsers" class="p-8 md:p-16">
				<div class="text-center max-w-md mx-auto">
					<div class="mb-6">
						<div class="mx-auto w-20 h-20 bg-muted/50 rounded-full flex items-center justify-center mb-4">
							<UserIcon class="h-10 w-10 text-muted-foreground" />
						</div>
					</div>
					<h3 class="text-xl font-semibold mb-2">Пользователей пока нет</h3>
					<p class="text-muted-foreground mb-6">Создайте первого пользователя</p>
					<Button @click="openCreateDialog" size="lg" class="gap-2">
						<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
						</svg>
						Создать пользователя
					</Button>
				</div>
			</div>

			<!-- Users Content -->
			<div v-else class="p-4 md:p-6 lg:p-8" style="padding-top: 0px !important;">
				<!-- Mobile Cards View -->
				<div class="md:hidden space-y-4">
					<div 
						v-for="(user, index) in childUsers" 
						:key="user.id" 
						class="border rounded-xl p-4 bg-card transition-all duration-200 hover:border-border/60"
						:style="{ animationDelay: `${index * 50}ms` }"
					>
						<div class="flex items-center justify-between gap-2 mb-2">
							<!-- <Badge variant="outline">{{ user.role }}</Badge> -->
						</div>
						<div class="flex items-start justify-between mb-4">
							<div class="flex-1">
								<div class="font-semibold text-lg mb-1">{{ user.name }}</div>
								<div class="text-sm text-muted-foreground">{{ user.email }}</div>
							</div>
						</div>

						<div class="space-y-3">
							<div class="flex items-center gap-2 text-sm" v-if="user.company">
								<BuildingIcon class="h-4 w-4 text-muted-foreground" />
								<span>{{ user.company }}</span>
							</div>

							<div class="flex items-center gap-2 text-sm">
								<PhoneIcon class="h-4 w-4 text-muted-foreground" />
								<a :href="'tel:' + user.phone" class="text-primary hover:underline font-mono">
									{{ user.phone }}
								</a>
							</div>

							<div class="flex items-center gap-2 text-sm">
								<MapPinIcon class="h-4 w-4 text-muted-foreground" />
								<span>{{ user.country }}, {{ user.region }}</span>
							</div>

							<div class="flex justify-between items-center gap-2 pt-3 border-t border-t-gray-200 dark:border-t-gray-800 text-sm">
								<div v-if="userRole == 'Super-Admin'">
									<div>
										<span class="text-muted-foreground">Комиссия:</span>
										<span class="font-semibold ml-1">{{ user.reward_fee }}%</span>
									</div>
									<!-- <div>
										<span class="text-muted-foreground">DXF:</span>
										<span class="font-semibold ml-1">{{ user.can_access_dxf ? "Да" : "Нет" }}</span>
									</div> -->
								</div>
								<div class="flex items-center justify-center gap-1">
									<div class="flex gap-2">
										<Button @click="editUser(user)" size="icon" variant="outline">
											<PencilIcon class="h-4 w-4" />
										</Button>
										<Button @click="deleteUser(user.id)" size="icon" variant="secondary" class="hover:bg-destructive/10 hover:text-destructive bg-gray-100 dark:bg-gray-800">
											<TrashIcon class="h-4 w-4" />
										</Button>
									</div>
								</div>
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
									<TableHead class="font-semibold text-sm">Пользователь</TableHead>
									<TableHead class="font-semibold text-sm">Компания</TableHead>
									<TableHead class="font-semibold text-sm">Город</TableHead>
									<TableHead v-if="userRole == 'Super-Admin'" class="font-semibold text-sm">Комиссия</TableHead>
									<!-- <TableHead class="font-semibold text-sm">DXF</TableHead> -->
									<TableHead v-if="['Super-Admin', 'Dealer R'].includes(userRole)" class="font-semibold text-sm">Роль</TableHead>
									<TableHead class="font-semibold text-sm text-right">Действия</TableHead>
								</TableRow>
							</TableHeader>
							<TableBody>
								<TableRow 
									v-for="user in childUsers" 
									:key="user.id" 
									class="group hover:bg-muted/20 transition-colors duration-150"
								>
									<TableCell>
										<div class="space-y-1">
											<p class="font-medium">{{ user.name ?? '-' }}</p>
											<p class="text-sm text-muted-foreground">{{ user.email }}</p>
											<a :href="'tel:' + user.phone" class="text-sm text-primary hover:underline font-mono">
												{{ user.phone }}
											</a>
										</div>
									</TableCell>
									<TableCell class="font-medium">{{ user.company }}</TableCell>
									<TableCell class="text-sm text-muted-foreground">
										<div>{{ user.city }}</div>
										<div class="text-xs text-wrap">{{ user.country }} <span v-if="user.region">, {{ user.region }}</span> </div>
									</TableCell>
								<TableCell v-if="userRole == 'Super-Admin'" class="font-semibold">{{ user.reward_fee }}%</TableCell>
								<!-- <TableCell>
									<Badge :variant="user.can_access_dxf ? 'default' : 'outline'">
										{{ user.can_access_dxf ? "Да" : "Нет" }}
									</Badge>
								</TableCell> -->
									<TableCell v-if="['Super-Admin', 'Dealer R'].includes(userRole)">
										<Badge variant="outline" class="font-normal">
											{{ user.role }}
										</Badge>
									</TableCell>
									<TableCell class="text-right">
										<div class="flex justify-end gap-2">
											<Button @click="editUser(user)" size="icon" variant="outline">
												<PencilIcon class="h-4 w-4" />
											</Button>
											<Button @click="deleteUser(user.id)" size="icon" variant="secondary" class="hover:bg-destructive/10 hover:text-destructive bg-gray-100 dark:bg-gray-800">
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

	<!-- Create User Dialog -->
	<Dialog v-model:open="showCreateUserDialog">
		<DialogContent class="max-w-md max-h-[100vh] overflow-y-auto">
			<DialogHeader>
				<DialogTitle>Создать пользователя</DialogTitle>
				<DialogDescription>Заполните форму для создания нового пользователя</DialogDescription>
			</DialogHeader>
			<div class="space-y-4">
				<!-- Role Selection (if applicable) -->
				<div v-if="canCreateRole.showSelect" class="space-y-2">
					<Label>Роль *</Label>
					<Select v-model="newUser.role" required>
						<SelectTrigger>
							<SelectValue placeholder="Выберите роль" />
						</SelectTrigger>
						<SelectContent>
							<SelectGroup>
								<SelectItem 
									v-for="role in canCreateRole.canCreate" 
									:key="role" 
									:value="role"
								>
									{{ role === 'Dealer' ? 'Дилер' : 'Менеджер' }}
								</SelectItem>
							</SelectGroup>
						</SelectContent>
					</Select>
				</div>

				<!-- Email Field -->
				<div class="space-y-2">
					<Label>Email *</Label>
					<Input v-model="newUser.email" type="email" required />
				</div>

				<!-- Phone Field -->
				<div class="space-y-2">
					<Label>Телефон</Label>
					<Input v-model="newUser.phone" type="tel" v-maska="'+7 (###) ### ##-##'" placeholder="+7 (___) ___ __-__" />
				</div>
			</div>
			<DialogFooter class="flex gap-2">
				<Button variant="outline" @click="showCreateUserDialog = false; resetNewUser()">Отмена</Button>
				<Button @click="createUser">Создать</Button>
			</DialogFooter>
		</DialogContent>
	</Dialog>

	<!-- Edit User Dialog -->
	<Dialog v-model:open="showEditUserDialog">
		<DialogContent class="max-w-4xl max-h-[100vh] overflow-y-auto" v-if="editingUser">
			<DialogHeader>
				<DialogTitle>Редактировать пользователя</DialogTitle>
				<DialogDescription>Обновите информацию о пользователе</DialogDescription>
			</DialogHeader>
			<div class="space-y-2 md:space-y-0 md:grid md:grid-cols-2 gap-4">
				<div class="space-y-2">
					<Label>ФИО</Label>
					<Input v-model="editingUser.name" />
				</div>
				<div class="space-y-2">
					<Label>Email *</Label>
					<Input v-model="editingUser.email" type="email" required />
				</div>
				<div class="space-y-2">
					<Label>Телефон</Label>
					<Input v-model="editingUser.phone" type="tel" v-maska="'+7 (###) ### ##-##'" placeholder="+7 (___) ___ __-__" />
				</div>
				<div class="space-y-2">
					<Label>Telegram</Label>
					<Input v-model="editingUser.telegram" placeholder="@username" />
				</div>
				<div class="space-y-2">
					<Label>Страна</Label>
					<Select v-model="editingUser.country">
						<SelectTrigger>
							<SelectValue />
						</SelectTrigger>
						<SelectContent>
							<SelectGroup>
								<SelectItem value="Армения">Армения</SelectItem>
								<SelectItem value="Беларусь">Беларусь</SelectItem>
								<SelectItem value="Казахстан">Казахстан</SelectItem>
								<SelectItem value="Киргизия">Киргизия</SelectItem>
								<SelectItem value="Россия">Россия</SelectItem>
							</SelectGroup>
						</SelectContent>
					</Select>
				</div>
				<div class="space-y-2">
					<Label>Регион</Label>
					<Select v-model="editingUser.region" :disabled="!editingUser.country">
						<SelectTrigger>
							<SelectValue />
						</SelectTrigger>
						<SelectContent>
							<SelectGroup>
								<SelectItem v-for="region in editRegions" :key="region" :value="region">
									{{ region }}
								</SelectItem>
							</SelectGroup>
						</SelectContent>
					</Select>
				</div>
				<div class="space-y-2">
					<Label>Город</Label>
					<Input v-model="editingUser.city" placeholder="Название города" />
				</div>
			<div class="col-span-2 space-y-2">
				<Label>Адрес</Label>
				<Textarea v-model="editingUser.address" :rows="2" />
			</div>
				<div class="space-y-2">
					<Label>Компания</Label>
					<Input v-model="editingUser.company" />
				</div>
				<div class="space-y-2">
					<Label>Веб-сайт</Label>
					<Input v-model="editingUser.website" type="url" placeholder="https://example.com" />
				</div>
				<div v-if="userRole === 'Super-Admin'" class="col-span-2 space-y-2">
					<Label>Комиссия (%)</Label>
					<Input v-model.number="editingUser.reward_fee" type="number" min="0" max="100" step="0.01" />
				</div>
				<!-- <div class="col-span-2 flex items-center space-x-2">
					<Switch v-model:checked="editingUser.can_access_dxf" />
					<Label>Доступ к DXF</Label>
				</div> -->
				
				<!-- Private Note (Admin Only) -->
				<div v-if="['Super-Admin', 'Operator'].includes(userRole)" class="col-span-2 space-y-2">
					<Label class="text-muted-foreground">Примечание (видно только вам)</Label>
					<Textarea v-model="editingUser.private_note" :rows="4" placeholder="Внутренние заметки о пользователе..." />
					<!-- <p class="text-xs text-muted-foreground">Это примечание видно только администраторам</p> -->
				</div>
			</div>
			<DialogFooter class="flex gap-2">
				<Button variant="outline" @click="showEditUserDialog = false">Отмена</Button>
				<Button @click="updateUser">Сохранить</Button>
			</DialogFooter>
		</DialogContent>
	</Dialog>
</template>


