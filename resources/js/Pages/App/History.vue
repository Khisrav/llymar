<script setup lang="ts">
import { Head, Link, usePage } from "@inertiajs/vue3";
import AuthenticatedHeaderLayout from "../../Layouts/AuthenticatedHeaderLayout.vue";
import { ArrowLeft, EllipsisVerticalIcon, FolderClockIcon, ReceiptRussianRubleIcon, ScrollTextIcon, TrashIcon } from "lucide-vue-next";
import { ref, computed } from "vue";
import { Order, Pagination } from "../../lib/types";
import Table from "../../Components/ui/table/Table.vue";
import TableHeader from "../../Components/ui/table/TableHeader.vue";
import TableRow from "../../Components/ui/table/TableRow.vue";
import TableHead from "../../Components/ui/table/TableHead.vue";
import TableBody from "../../Components/ui/table/TableBody.vue";
import TableCell from "../../Components/ui/table/TableCell.vue";
import { currencyFormatter } from "../../Utils/currencyFormatter";
import Button from "../../Components/ui/button/Button.vue";
import StatusBadge from "../../Components/StatusBadge.vue";
import DropdownMenuTrigger from "../../Components/ui/dropdown-menu/DropdownMenuTrigger.vue";
import DropdownMenuContent from "../../Components/ui/dropdown-menu/DropdownMenuContent.vue";
import DropdownMenuItem from "../../Components/ui/dropdown-menu/DropdownMenuItem.vue";
import DropdownMenu from "../../Components/ui/dropdown-menu/DropdownMenu.vue";
import DropdownMenuLabel from "../../Components/ui/dropdown-menu/DropdownMenuLabel.vue";
import DropdownMenuSeparator from "../../Components/ui/dropdown-menu/DropdownMenuSeparator.vue";

const page = usePage();
const orders = ref(page.props.orders.data as Order[]);
const pagination = ref(page.props.orders.links as Pagination[]);

const hasOrders = computed(() => orders.value.length > 0);

const formatDate = (dateString: string) => {
	return new Date(dateString).toLocaleDateString("ru-RU", {
		year: "numeric",
		month: "short",
		day: "numeric",
	});
};

declare const window: any;
const downloadListPDF = (order_id: number) => {
	return window.open("/orders/" + order_id + "/list-pdf", "_blank").focus();
}
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

			<div v-else>
				<div class="rounded-lg border">
					<Table>
						<TableHeader>
							<TableRow>
								<TableHead class="font-bold">№ заказа</TableHead>
								<TableHead class="font-bold">Дата</TableHead>
								<TableHead class="font-bold">Получатель</TableHead>
								<TableHead class="font-bold">Сумма</TableHead>
								<TableHead class="font-bold">Статус</TableHead>
								<!-- <TableHead class="font-bold">Действия</TableHead> -->
							</TableRow>
						</TableHeader>
						<TableBody>
							<TableRow v-for="order in orders" :key="order.id">
								<TableCell>{{ order.order_number }}</TableCell>
								<TableCell>{{ formatDate(order.created_at || '') }}</TableCell>
								<TableCell class="flex flex-col gap-1">
								    <p class="font-medium">{{ order.customer_name }}</p>
								    <a :href="'tel:' + order.customer_phone" class="underline font-mono">{{ order.customer_phone }}</a>
								</TableCell>
								<TableCell>{{ currencyFormatter(order.total_price) }}</TableCell>
								<TableCell>
								    <StatusBadge :status="order.status" />
								</TableCell>
								<TableCell class="text-right">
								    <DropdownMenu>
								        <DropdownMenuTrigger>
								            <Button variant="outline" size="icon">
								                <EllipsisVerticalIcon />
								            </Button>
								        </DropdownMenuTrigger>
								        <DropdownMenuContent>
								            <DropdownMenuLabel>Действия</DropdownMenuLabel>
								            <DropdownMenuSeparator />
								            <DropdownMenuItem>
								                    <TrashIcon class="size-4" />
								                    <span>Удалить</span>
								            </DropdownMenuItem>
								            <DropdownMenuItem @click="downloadListPDF(order.id)">
								                    <ScrollTextIcon class="size-4" />
								                    <span>Перечень PDF</span>
								            </DropdownMenuItem>
								            <DropdownMenuItem>
								                    <ReceiptRussianRubleIcon class="size-4" />
								                    <span>Счет PDF</span>
								            </DropdownMenuItem>
								        </DropdownMenuContent>
								    </DropdownMenu>
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
	</div>
</template>
