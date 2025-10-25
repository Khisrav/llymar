<script setup lang="ts">
import AuthenticatedHeaderLayout from "../../Layouts/AuthenticatedHeaderLayout.vue";
import { Head, usePage, Link, router } from "@inertiajs/vue3";
import { EllipsisVerticalIcon, FileTextIcon, FolderClockIcon, TrashIcon, ArrowLeftIcon, ChevronLeftIcon, ChevronRightIcon, PenIcon } from "lucide-vue-next";
import { computed, ref } from "vue";
import { toast, Toaster } from "vue-sonner";
import { CommercialOfferRecord, CartItem } from "../../lib/types";
import { 
    Table, 
    TableHeader, 
    TableRow, 
    TableHead, 
    TableBody, 
    TableCell 
} from "../../Components/ui/table";
import { Badge } from "../../Components/ui/badge";
import { currencyFormatter } from "../../Utils/currencyFormatter";
import { 
    DropdownMenu, 
    DropdownMenuTrigger, 
    DropdownMenuContent, 
    DropdownMenuLabel, 
    DropdownMenuSeparator,
    DropdownMenuItem 
} from "../../Components/ui/dropdown-menu";
import { Button } from "../../Components/ui/button";
import axios from "axios";

interface PaginationData {
    current_page: number;
    data: CommercialOfferRecord[];
    first_page_url: string;
    from: number;
    last_page: number;
    last_page_url: string;
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number;
    total: number;
}

const formatDate = (dateString: string) => {
	return new Date(dateString).toLocaleDateString("ru-RU", {
		year: "numeric",
		month: "short",
		day: "numeric",
	})
}

const pageProps = usePage().props as unknown as { offers: PaginationData };
const offers = ref(pageProps.offers.data);
const pagination = ref({
    current_page: pageProps.offers.current_page,
    last_page: pageProps.offers.last_page,
    total: pageProps.offers.total,
    per_page: pageProps.offers.per_page,
    links: pageProps.offers.links,
});

const hasOffers = computed(() => offers.value.length > 0)

const deleteOffer = (id: number) => {
	axios.delete(`/app/commercial-offers/${id}`)
		.then(() => {
			offers.value = offers.value.filter((offer) => offer.id !== id)
			toast("Коммерческое предложение успешно удалено")
		})
		.catch((error) => {
			console.error(error)
			toast("Произошла ошибка при удалении коммерческого предложения")
		})
}

const downloadPDF = (id: number) => {
    window.open(`/app/commercial-offers/${id}/pdf`, '_blank');
}

const goToPage = (page: number) => {
    if (page >= 1 && page <= pagination.value.last_page) {
        router.get('/app/commercial-offers', { page }, {
            preserveState: true,
            preserveScroll: true,
            onSuccess: (page) => {
                const newProps = page.props as unknown as { offers: PaginationData };
                offers.value = newProps.offers.data;
                pagination.value = {
                    current_page: newProps.offers.current_page,
                    last_page: newProps.offers.last_page,
                    total: newProps.offers.total,
                    per_page: newProps.offers.per_page,
                    links: newProps.offers.links,
                };
            }
        });
    }
}

const countCartItems = (cartItems: CartItem[] | Record<string, CartItem>): number => {
    if (Array.isArray(cartItems)) {
        return cartItems.length;
    }
    return (Object as any).values(cartItems).reduce((sum: number, item: CartItem) => sum + 1, 0);
}

const openCommercialOfferInCalculator = (id: number) => {
	const offer = offers.value.find((offer) => offer.id === id);
	if (offer) {
		// Clear existing data
		sessionStorage.removeItem('openings');
		sessionStorage.removeItem('cartItems');
		
		// Store commercial offer ID for updating
		sessionStorage.setItem('commercialOfferId', id.toString());
		
		// Store openings and cart items
		sessionStorage.setItem('openings', JSON.stringify(offer.openings));
		sessionStorage.setItem('cartItems', JSON.stringify(offer.cart_items));
		sessionStorage.setItem('selectedGlassID', offer.glass?.id?.toString() || '');
		sessionStorage.setItem('selectedServicesID', JSON.stringify(offer.services?.map((service) => service.id) || []));
		
		// Store commercial offer customer and manufacturer data
		const customerData = {
			name: offer.customer_name || '',
			phone: offer.customer_phone || '',
			address: offer.customer_address || '',
			comment: offer.customer_comment || '',
		};
		const manufacturerData = {
			title: 'Информация о производителе',
			manufacturer: offer.manufacturer_name || '',
			company: '',
			phone: offer.manufacturer_phone || ''
		};
		sessionStorage.setItem('commercialOfferCustomer', JSON.stringify(customerData));
		sessionStorage.setItem('commercialOfferManufacturer', JSON.stringify(manufacturerData));
		
		// Navigate to calculator
		window.location.href = '/app/calculator';
	} else {
		toast.error("Коммерческое предложение не найдено")
	}
}
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
						{{ hasOffers ? `${pagination.total} коммерческих предложений` : "Управляйте своими коммерческими предложениями" }}
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
							<ArrowLeftIcon class="h-4 w-4" />
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
							v-for="offer in offers" 
							:key="offer.id" 
							class="border rounded-xl p-4 bg-card transition-all duration-200 hover:border-border/60"
						>
							<div class="flex items-start justify-between mb-4">
								<div class="flex items-center gap-2">
									<span class="text-xs font-mono text-muted-foreground bg-muted px-2 py-1 rounded">№</span>
									<span class="font-semibold text-lg">{{ offer.id }}</span>
								</div>
								<DropdownMenu>
									<DropdownMenuTrigger>
										<Button variant="outline" size="icon" class="h-8 w-8">
											<EllipsisVerticalIcon class="h-4 w-4" />
										</Button>
									</DropdownMenuTrigger>
									<DropdownMenuContent align="end">
										<DropdownMenuLabel>Действия</DropdownMenuLabel>
										<DropdownMenuSeparator />
										<DropdownMenuItem @click="openCommercialOfferInCalculator(offer.id)">
											<PenIcon class="h-4 w-4" />
											<span>Редактировать</span>
										</DropdownMenuItem>
										<DropdownMenuItem @click="downloadPDF(offer.id)">
											<FileTextIcon class="h-4 w-4" />
											<span>Скачать PDF</span>
										</DropdownMenuItem>
										<DropdownMenuSeparator />
										<DropdownMenuItem @click="deleteOffer(offer.id)" class="text-destructive focus:text-destructive hover:bg-destructive/10 focus:bg-destructive/10">
											<TrashIcon class="h-4 w-4" />
											<span>Удалить</span>
										</DropdownMenuItem>
									</DropdownMenuContent>
								</DropdownMenu>
							</div>
							
							<div class="space-y-3">
								<div>
									<div class="text-xs text-muted-foreground mb-1">Заказчик</div>
									<div class="font-medium">{{ offer.customer_name ?? '--' }}</div>
									<div class="text-sm text-muted-foreground">{{ offer.customer_phone ?? '--' }}</div>
								</div>
								
								<div>
									<div class="text-xs text-muted-foreground mb-1">Производитель</div>
									<div class="font-medium">{{ offer.manufacturer_name ?? '--' }}</div>
									<div class="text-sm text-muted-foreground">{{ offer.manufacturer_phone ?? '--' }}</div>
								</div>
								
								<div class="flex items-center justify-between">
									<div>
										<div class="text-xs text-muted-foreground mb-1">Цена</div>
										<div class="font-bold text-primary">{{ currencyFormatter(offer.total_price) }}</div>
									</div>
									<div>
										<div class="text-xs text-muted-foreground mb-1">Наценка</div>
										<Badge variant="outline">{{ offer.markup_percentage }}%</Badge>
									</div>
								</div>
								
								<div class="flex items-center justify-between">
									<div>
										<div class="text-xs text-muted-foreground mb-1">Проемов</div>
										<Badge variant="outline">{{ offer.openings.length }}</Badge>
									</div>
									<div>
										<div class="text-xs text-muted-foreground mb-1">Товаров</div>
										<Badge variant="outline">{{ countCartItems(offer.cart_items) }}</Badge>
									</div>
								</div>
								
								<div>
									<div class="text-xs text-muted-foreground mb-1">Дата создания</div>
									<div class="text-sm">{{ formatDate(offer.created_at) }}</div>
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
										<TableHead class="font-semibold text-sm text-right">Действия</TableHead>
									</TableRow>
								</TableHeader>
								
								<TableBody>
								    <TableRow v-for="offer in offers" :key="offer.id">
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
								                {{ countCartItems(offer.cart_items) }}
								            </Badge>
								        </TableCell>
								        <TableCell class="text-muted-foreground text-sm">
								            {{ formatDate(offer.created_at) }}
								        </TableCell>
								        <TableCell class="text-right">
    										<DropdownMenu>
    											<DropdownMenuTrigger>
    												<Button variant="outline" size="icon" class="h-8 w-8">
    													<EllipsisVerticalIcon class="h-4 w-4" />
    												</Button>
    											</DropdownMenuTrigger>
    											<DropdownMenuContent align="end">
    												<DropdownMenuLabel>Действия</DropdownMenuLabel>
    												<DropdownMenuSeparator />
													<DropdownMenuItem @click="openCommercialOfferInCalculator(offer.id)">
														<PenIcon class="h-4 w-4" />
														<span>Редактировать</span>
													</DropdownMenuItem>
    												<DropdownMenuItem @click="downloadPDF(offer.id)">
    													<FileTextIcon class="h-4 w-4" />
    													<span>Скачать PDF</span>
    												</DropdownMenuItem>
    												<DropdownMenuSeparator />
    												<DropdownMenuItem @click="deleteOffer(offer.id)" class="text-destructive focus:text-destructive hover:bg-destructive/10 focus:bg-destructive/10">
    													<TrashIcon class="h-4 w-4" />
    													<span>Удалить</span>
    												</DropdownMenuItem>
    											</DropdownMenuContent>
    										</DropdownMenu>
    									</TableCell>
								    </TableRow>
								</TableBody>
							</Table>
						</div>
					</div>
					
					<!-- Pagination -->
					<div v-if="pagination.last_page > 1" class="flex items-center justify-between px-2 py-4">
						<div class="text-sm text-muted-foreground">
							Показано {{ (pagination.current_page - 1) * pagination.per_page + 1 }} - {{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }} из {{ pagination.total }} записей
						</div>
						
						<div class="flex items-center space-x-2">
							<!-- Previous Button -->
							<!-- <Button 
								variant="outline" 
								size="sm" 
								@click="goToPage(pagination.current_page - 1)"
								:disabled="pagination.current_page === 1"
								class="gap-1"
							>
								<ChevronLeftIcon class="h-4 w-4" />
								<span class="hidden sm:inline">Назад</span>
							</Button> -->
							
							<!-- Page Numbers -->
							<div class="hidden sm:flex items-center space-x-1">
								<template v-for="link in pagination.links" :key="link.label">
									<Button
										v-if="link.label !== '&laquo; Previous' && link.label !== 'Next &raquo;' && link.url"
										variant="outline"
										size="sm"
										v-html="link.label"
										:class="{ 'bg-primary text-primary-foreground': link.active }"
										@click="goToPage(parseInt(link.label))"
									>
									</Button>
									<span v-else-if="link.label === '...'" class="px-2 text-muted-foreground">...</span>
								</template>
							</div>
							
							<!-- Mobile Page Info -->
							<div class="sm:hidden text-sm text-muted-foreground">
								{{ pagination.current_page }} / {{ pagination.last_page }}
							</div>
							
							<!-- Next Button -->
							<!-- <Button 
								variant="outline" 
								size="sm" 
								@click="goToPage(pagination.current_page + 1)"
								:disabled="pagination.current_page === pagination.last_page"
								class="gap-1"
							>
								<span class="hidden sm:inline">Вперед</span>
								<ChevronRightIcon class="h-4 w-4" />
							</Button> -->
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>
