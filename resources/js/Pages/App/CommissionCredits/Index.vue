<script setup lang="ts">
import { Head, Link, usePage } from "@inertiajs/vue3"
import AuthenticatedHeaderLayout from "../../../Layouts/AuthenticatedHeaderLayout.vue"
import { BanknoteIcon, TrendingUpIcon, TrendingDownIcon, ScaleIcon, CalendarIcon, UserIcon, PackageIcon, RussianRubleIcon } from "lucide-vue-next"
import { ref, computed } from "vue"
import { CommissionCredit, CommissionCreditStatistics, Pagination } from "../../../lib/types"
import { Table, TableHeader, TableRow, TableHead, TableBody, TableCell } from "../../../Components/ui/table"
import { currencyFormatter } from "../../../Utils/currencyFormatter"
import { Button } from "../../../Components/ui/button"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "../../../Components/ui/card"
import { Badge } from "../../../Components/ui/badge"

const page = usePage() as any
const commissionCredits = ref(page.props.commissionCredits.data as CommissionCredit[])
const pagination = ref(page.props.commissionCredits.links as Pagination[])
const statistics = ref(page.props.statistics as CommissionCreditStatistics)
const isSuperAdmin = ref(page.props.isSuperAdmin as boolean)

const hasRecords = computed(() => commissionCredits.value.length > 0)

const formatDate = (dateString: string) => {
	return new Date(dateString).toLocaleDateString("ru-RU", {
		year: "numeric",
		month: "short",
		day: "numeric",
		hour: "2-digit",
		minute: "2-digit",
	})
}

const getTypeBadgeVariant = (type: 'accrual' | 'write-off') => {
	return type === 'accrual' ? 'default' : 'secondary'
}

const getTypeLabel = (type: 'accrual' | 'write-off') => {
	return type === 'accrual' ? 'Начисление' : 'Выплата'
}

const getBalanceColor = (balance: number) => {
	if (balance === 0) return 'text-green-600'
	if (balance > 0) return 'text-yellow-600'
	return 'text-blue-600'
}
</script>

<template>
	<Head title="Комиссионные операции" />
	<AuthenticatedHeaderLayout />

	<div class="container mx-auto p-0 md:p-6 lg:p-8">
		<div class="bg-background md:border md:rounded-2xl md:shadow-sm overflow-hidden">
			<!-- Header Section -->
			<div class="p-4 md:p-8">
				<div class="flex items-center gap-3 mb-6">
					<div class="p-2 bg-primary/10 rounded-lg">
						<BanknoteIcon class="h-6 w-6 text-primary" />
					</div>
					<div>
						<h1 class="text-2xl font-bold tracking-tight">Комиссионные операции</h1>
						<p class="text-sm text-muted-foreground mt-1">
							{{ hasRecords ? `${commissionCredits.length} операций` : 'Управление комиссионными операциями' }}
						</p>
					</div>
				</div>

				<!-- Statistics Cards -->
				<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
					<!-- Total Accruals -->
					<Card>
						<CardHeader class="pb-2">
							<CardDescription class="flex items-center gap-2">
								<TrendingUpIcon class="h-4 w-4 text-green-600" />
								<span>Общие начисления</span>
							</CardDescription>
							<CardTitle class="text-2xl font-bold text-green-600">
								{{ currencyFormatter(statistics.totalAccruals) }}
							</CardTitle>
						</CardHeader>
						<CardContent>
							<p class="text-xs text-muted-foreground">
								{{ statistics.accrualCount > 0 ? `Операций: ${statistics.accrualCount}` : 'Нет начислений' }}
							</p>
						</CardContent>
					</Card>

					<!-- Total Write-offs -->
					<Card>
						<CardHeader class="pb-2">
							<CardDescription class="flex items-center gap-2">
								<TrendingDownIcon class="h-4 w-4 text-orange-600" />
								<span>Общие выплаты</span>
							</CardDescription>
							<CardTitle class="text-2xl font-bold text-orange-600">
								{{ currencyFormatter(statistics.totalWriteOffs) }}
							</CardTitle>
						</CardHeader>
						<CardContent>
							<p class="text-xs text-muted-foreground">
								{{ statistics.writeOffCount > 0 ? `Операций: ${statistics.writeOffCount}` : 'Нет выплат' }}
							</p>
						</CardContent>
					</Card>

					<!-- Total Balance -->
					<Card>
						<CardHeader class="pb-2">
							<CardDescription class="flex items-center gap-2">
								<ScaleIcon class="h-4 w-4" />
								<span>Общий баланс</span>
							</CardDescription>
							<CardTitle class="text-2xl font-bold" :class="getBalanceColor(statistics.totalBalance)">
								{{ currencyFormatter(statistics.totalBalance) }}
							</CardTitle>
						</CardHeader>
						<CardContent>
							<p class="text-xs text-muted-foreground">
								{{ statistics.totalBalance === 0 ? 'Сбалансированная система' : statistics.totalBalance > 0 ? 'Долг к выплате' : 'Переплата по системе' }}
							</p>
						</CardContent>
					</Card>

					<!-- Pending Balance (Super Admin Only) -->
					<Card v-if="isSuperAdmin">
						<CardHeader class="pb-2">
							<CardDescription class="flex items-center gap-2">
								<RussianRubleIcon class="h-4 w-4 text-blue-500" />
								<span>К выплате</span>
							</CardDescription>
							<CardTitle class="text-2xl font-bold text-blue-500">
								{{ currencyFormatter(statistics.pendingBalance) }}
							</CardTitle>
						</CardHeader>
						<CardContent>
							<p class="text-xs text-muted-foreground">
								{{ statistics.usersWithBalance > 0 ? `Пользователей: ${statistics.usersWithBalance}` : 'Нет долгов' }}
							</p>
						</CardContent>
					</Card>
				</div>

				<!-- Empty State -->
				<div v-if="!hasRecords" class="p-8 md:p-16">
					<div class="text-center max-w-md mx-auto">
						<div class="mb-6">
							<div class="mx-auto w-20 h-20 bg-muted/50 rounded-full flex items-center justify-center mb-4">
								<BanknoteIcon class="h-10 w-10 text-muted-foreground" />
							</div>
						</div>
						<h3 class="text-xl font-semibold mb-2">Операций пока нет</h3>
						<p class="text-muted-foreground mb-6">Комиссионные операции будут отображаться здесь</p>
					</div>
				</div>

				<!-- Table Content -->
				<div v-else>
					<!-- Mobile Cards View -->
					<div class="md:hidden space-y-4">
						<div 
							v-for="(record, index) in commissionCredits" 
							:key="record.id" 
							class="border rounded-xl p-4 bg-card transition-all duration-200 hover:border-border/60 shadow-md shadow-neutral-100 dark:shadow-neutral-900 hover:shadow-neutral-200 dark:hover:shadow-neutral-800"
							:style="{ animationDelay: `${index * 50}ms` }"
						>
							<div class="flex items-start justify-between mb-4">
								<div class="flex-1">
									<div class="flex items-center gap-2 mb-2">
										<Badge :variant="getTypeBadgeVariant(record.type)">
											{{ getTypeLabel(record.type) }}
										</Badge>
									</div>
									<div class="text-2xl font-bold text-primary">
										{{ currencyFormatter(record.amount) }}
									</div>
								</div>
							</div>
							
							<div class="space-y-3">
								<div class="flex items-center gap-2 text-sm">
									<CalendarIcon class="h-4 w-4 text-muted-foreground" />
									<span>{{ formatDate(record.created_at) }}</span>
								</div>
								
								<div v-if="record.recipient" class="flex items-center gap-2 text-sm">
									<UserIcon class="h-4 w-4 text-muted-foreground" />
									<div>
										<div class="font-medium">{{ record.recipient.name }}</div>
										<div class="text-xs text-muted-foreground">Получатель</div>
									</div>
								</div>
								
								<div v-if="record.user" class="flex items-center gap-2 text-sm">
									<UserIcon class="h-4 w-4 text-muted-foreground" />
									<div>
										<div class="font-medium">{{ record.user.name }}</div>
										<div class="text-xs text-muted-foreground">Инициатор</div>
									</div>
								</div>

								<div v-if="record.order" class="flex items-center gap-2 text-sm">
									<PackageIcon class="h-4 w-4 text-muted-foreground" />
									<Link :href="`/app/orders/${record.order.id}`" class="text-primary hover:underline font-mono">
										Заказ №{{ record.order.order_number || record.order.id }}
									</Link>
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
										<TableHead class="font-semibold text-sm">Заказ</TableHead>
										<TableHead class="font-semibold text-sm">Получатель</TableHead>
										<TableHead class="font-semibold text-sm">Инициатор</TableHead>
										<TableHead class="font-semibold text-sm">Дата</TableHead>
										<TableHead class="font-semibold text-sm">Тип</TableHead>
										<TableHead class="font-semibold text-sm text-right">Сумма</TableHead>
									</TableRow>
								</TableHeader>
								<TableBody>
									<TableRow 
										v-for="record in commissionCredits" 
										:key="record.id" 
										class="group hover:bg-muted/20 transition-colors duration-150"
									>
										<TableCell>
											<Link 
												v-if="record.order" 
												:href="`/app/orders/${record.order.id}`" 
												class="font-mono hover:text-primary transition-colors border-b-2 border-primary border-dotted"
											>
												{{ record.order.order_number || record.order.id }}
											</Link>
											<span v-else class="text-muted-foreground">—</span>
										</TableCell>
										<TableCell>
											<div v-if="record.recipient" class="space-y-1">
												<p class="font-medium">{{ record.recipient.name }}</p>
												<p class="text-sm text-muted-foreground">{{ record.recipient.email }}</p>
											</div>
											<span v-else class="text-muted-foreground">—</span>
										</TableCell>
										<TableCell>
											<div v-if="record.user" class="space-y-1">
												<p class="font-medium">{{ record.user.name }}</p>
												<p class="text-sm text-muted-foreground">{{ record.user.company }}</p>
											</div>
											<span v-else class="text-muted-foreground">—</span>
										</TableCell>
										<TableCell class="text-muted-foreground text-sm">
											{{ formatDate(record.created_at) }}
										</TableCell>
										<TableCell>
											<Badge :variant="getTypeBadgeVariant(record.type)">
												{{ getTypeLabel(record.type) }}
											</Badge>
										</TableCell>
										<TableCell class="text-right">
											<span class="font-bold text-primary">{{ currencyFormatter(record.amount) }}</span>
										</TableCell>
									</TableRow>
								</TableBody>
							</Table>
						</div>
					</div>

					<!-- Pagination -->
					<div v-if="pagination.length > 3" class="mt-6 md:mt-8 flex justify-center">
						<div class="flex items-center gap-1">
							<template v-for="link in pagination" :key="link.label">
								<Button 
									v-if="link.url"
									:disabled="!link.url" 
									:variant="link.active ? 'default' : 'outline'" 
									size="sm"
									@click="$inertia.visit(link.url || '#')"
									class="min-w-[2.5rem]"
								>
									<span v-html="link.label" />
								</Button>
								<span v-else class="px-3 py-1.5 text-sm text-muted-foreground" v-html="link.label" />
							</template>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

