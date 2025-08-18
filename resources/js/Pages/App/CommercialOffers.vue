<script setup lang="ts">
import AuthenticatedHeaderLayout from "../../Layouts/AuthenticatedHeaderLayout.vue";
import { Head, usePage } from "@inertiajs/vue3";
import { FolderClockIcon } from "lucide-vue-next";
import { computed } from "vue";
import { Toaster } from "vue-sonner";
import { CommercialOfferRecord, CartItem } from "../../lib/types";
import { ref } from "vue";
import Table from "../../Components/ui/table/Table.vue";
import TableHeader from "../../Components/ui/table/TableHeader.vue";
import TableRow from "../../Components/ui/table/TableRow.vue";
import TableHead from "../../Components/ui/table/TableHead.vue";
import TableBody from "../../Components/ui/table/TableBody.vue";
import TableCell from "../../Components/ui/table/TableCell.vue";
import Badge from "../../Components/ui/badge/Badge.vue";
import { currencyFormatter } from "../../Utils/currencyFormatter";

const formatDate = (dateString: string) => {
	return new Date(dateString).toLocaleDateString("ru-RU", {
		year: "numeric",
		month: "short",
		day: "numeric",
	})
}

const offers = ref(usePage().props.offers.data as CommercialOfferRecord[])
const hasOffers = computed(() => offers.value.length > 0)
</script>

<template>
	<Head title="Коммерческие предложения" />

	<Toaster />

	<AuthenticatedHeaderLayout />

	<div class="container mx-auto p-0 md:p-6 lg:p-8">
		<div class="bg-background md:border md:rounded-2xl md:shadow-sm overflow-hidden">
			<div class="flex items-center gap-4 p-4 md:p-8">
				<div class="p-2 bg-primary/10 rounded-lg">
					<FolderClockIcon class="h-6 w-6 text-primary" />
				</div>
				<div>
					<h1 class="text-2xl font-bold tracking-tight">Коммерческие предложения</h1>
					<p class="text-sm text-muted-foreground mt-1">
						{{ hasOffers ? `${offers.length} коммерческих предложений` : "Управляйте своими коммерческими предложениями" }}
					</p>
				</div>
			</div>
			
			<div v-if="!hasOffers" class="p-8 md:p-16">
				<div class="text-center max-w-md mx-auto">
					<div class="mb-6">
						<div class="mx-auto w-20 h-20 bg-muted/50 rounded-full flex items-center justify-center mb-4">
							<FolderClockIcon class="h-10 w-10 text-muted-foreground" />
						</div>
					</div>
					<h3 class="text-xl font-semibold mb-2">Коммерческих предложений пока нет</h3>
					<p class="text-muted-foreground mb-6">Создайте свой первое коммерческое предложение, используя калькулятор</p>
					<Link href="/app/calculator">
						<Button size="lg" class="gap-2">
							<ArrowLeft class="h-4 w-4" />
							Перейти к калькулятору
						</Button>
					</Link>
				</div>
			</div>
			
			<div v-else>
				<div class="p-4 md:p-6 lg:p-8" style="padding-top: 0px !important;">
				    <!-- Mobile View -->
					<div class="md:hidden space-y-4">
						<div 
							v-for="(offer, index) in offers" 
							:key="offer.id" 
							class="border rounded-xl p-4 bg-card transition-all duration-200 hover:border-border/60"
						>
							<div class="flex items-start justify-between mb-4">
								<div class="flex items-center gap-2">
									<span class="text-xs font-mono text-muted-foreground bg-muted px-2 py-1 rounded">№</span>
									<span class="font-semibold text-lg">{{ offer.id }}</span>
								</div>
							</div>
						</div>
					</div>
					
					<!-- Desktop View -->
					<div class="hidden md:block">
						<div class="rounded-lg border overflow-hidden shadow-sm">
							<Table>
								<TableHeader>
									<TableRow class="bg-muted/30 hover:bg-muted/30">
										<TableHead class="font-semibold text-sm">ID</TableHead>
										<TableHead class="font-semibold text-sm">Заказчик</TableHead>
										<TableHead class="font-semibold text-sm">Производитель</TableHead>
										<TableHead class="font-semibold text-sm">Цена</TableHead>
										<TableHead class="font-semibold text-sm">Наценка</TableHead>
										<TableHead class="font-semibold text-sm">Проемов</TableHead>
										<TableHead class="font-semibold text-sm">Товаров</TableHead>
										<TableHead class="font-semibold text-sm">Дата создания</TableHead>
									</TableRow>
								</TableHeader>
								
								<TableBody>
								    <TableRow v-for="(offer, index) in offers" :key="offer.id">
								        <TableCell>
								            {{ offer.id }}
								        </TableCell>
								        <TableCell>
								            {{ offer.customer_name ?? '--' }}
								            <br>
								            {{ offer.customer_phone ?? '--' }}
								        </TableCell>
								        <TableCell>
								            {{ offer.manufacturer_name ?? '--' }}
								            <br>
								            {{ offer.manufacturer_phone }}
								        </TableCell>
								        <TableCell>
								            <span class="font-bold text-primary">{{ currencyFormatter(offer.total_price) }}</span>
								        </TableCell>
								        <TableCell>
								            <Badge variant="outline">
								                {{ offer.markup_percentage }}%
								            </Badge>
								        </TableCell>
								        <TableCell>
								            <Badge variant="outline">
								                {{ offer.openings.length }}
								            </Badge>
								        </TableCell>
								        <TableCell>
								            <Badge variant="outline">
								                {{ Object.values(offer.cart_items).reduce((sum: number, item: CartItem) => sum + 1, 0) }}
								            </Badge>
								        </TableCell>
								        <TableCell class="text-muted-foreground text-sm">
								            {{ formatDate(offer.created_at) }}
								        </TableCell>
								    </TableRow>
								</TableBody>
							</Table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>
