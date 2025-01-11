<script setup lang="ts">
import { Head, Link, usePage } from "@inertiajs/vue3";
import AuthenticatedHeaderLayout from "../../Layouts/AuthenticatedHeaderLayout.vue";
import { ArrowLeft, FolderClockIcon, PencilIcon, TrashIcon } from "lucide-vue-next";
import { ref, computed } from "vue";
import { Order, Pagination } from "../../lib/types";
import Table from "../../Components/ui/table/Table.vue";
import TableHeader from "../../Components/ui/table/TableHeader.vue";
import TableRow from "../../Components/ui/table/TableRow.vue";
import TableHead from "../../Components/ui/table/TableHead.vue";
import TableBody from "../../Components/ui/table/TableBody.vue";
import TableCell from "../../Components/ui/table/TableCell.vue";
import { currencyFormatter } from "../../Utils/currencyFormatter";
import Badge from "../../Components/ui/badge/Badge.vue";
import Button from "../../Components/ui/button/Button.vue";

const page = usePage();
const orders = ref(page.props.orders.data as Order[]);
const pagination = ref(page.props.orders.links as Pagination[]);

const hasOrders = computed(() => orders.value.length > 0);

const formatDate = (dateString: string) => {
	return new Date(dateString).toLocaleDateString("ru-RU", {
		year: "numeric",
		month: "long",
		day: "numeric",
	});
};

const formatStatus = (status: string) => {
	switch (status) {
		case "completed":
			return "Завершен";
		case "pending":
			return "В обработке";
		case "cancelled":
			return "Отменен";
		default:
			return status;
	}
};
</script>

<template>
	<Head title="История заказов" />
	<AuthenticatedHeaderLayout />

	<div class="container p-4 md:p-8">
		<div class="md:p-8 md:mt-8 md:border rounded-2xl">
			<h2 class="text-3xl font-semibold mb-6">История заказов</h2>

			<div v-if="!hasOrders" class="text-center py-8">
				<FolderClockIcon class="h-16 w-16 mx-auto mb-4 text-gray-400" />
				<p class="text-lg text-gray-600">У вас пока нет заказов</p>
				<Link href="/app/calculator" class="inline-block mt-4">
					<Button variant="outline"> <ArrowLeft class="mr-2 h-4 w-4" /> Перейти к калькулятору </Button>
				</Link>
			</div>

			<div v-else class="rounded-lg border">
				<Table>
					<TableHeader>
						<TableRow>
							<TableHead class="font-bold">ID</TableHead>
							<TableHead class="font-bold">Дата</TableHead>
							<TableHead class="font-bold">Заказчик</TableHead>
							<TableHead class="font-bold">Сумма</TableHead>
							<TableHead class="font-bold">Статус</TableHead>
							<TableHead class="font-bold">Действия</TableHead>
						</TableRow>
					</TableHeader>
					<TableBody>
						<TableRow v-for="order in orders" :key="order.id">
							<TableCell>{{ order.id }}</TableCell>
							<TableCell>{{ formatDate(order.created_at || '') }}</TableCell>
							<TableCell class="flex flex-col gap-1">
							    <p class="font-medium">{{ order.customer_name }}</p>
							    <a :href="'tel:' + order.customer_phone" class="underline font-mono">{{ order.customer_phone }}</a>
							</TableCell>
							<TableCell>{{ currencyFormatter(order.total_price) }}</TableCell>
							<TableCell>
							    <Badge variant="outline">{{ order.status }}</Badge>
							</TableCell>
							<TableCell class="flex gap-2">
							    <Button variant="outline" size="icon">
							        <PencilIcon />
							    </Button>
							    <Button variant="secondary" size="icon">
							        <TrashIcon />
							    </Button>
							</TableCell>
						</TableRow>
					</TableBody>
				</Table>
			</div>

            <div class="mt-4 md:mt-8 flex justify-center">
                <Button v-for="link in pagination" :key="link.label" :disabled="!link.url" :variant="link.active ? 'default' : 'outline'" class="mx-1" @click="$inertia.visit(link.url || '#')" v-html="link.label" />
            </div>
		</div>
	</div>
</template>
