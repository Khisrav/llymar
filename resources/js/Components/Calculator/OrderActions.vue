<script setup lang="ts">
import {EllipsisVertical, FileText, Printer, ScrollText, ShoppingCartIcon } from "lucide-vue-next"
import Button from "../ui/button/Button.vue"
import DropdownMenu from "../ui/dropdown-menu/DropdownMenu.vue"
import DropdownMenuTrigger from "../ui/dropdown-menu/DropdownMenuTrigger.vue"
import DropdownMenuContent from "../ui/dropdown-menu/DropdownMenuContent.vue"
import DropdownMenuLabel from "../ui/dropdown-menu/DropdownMenuLabel.vue"
import DropdownMenuSeparator from "../ui/dropdown-menu/DropdownMenuSeparator.vue"
import DropdownMenuItem from "../ui/dropdown-menu/DropdownMenuItem.vue"
import DropdownMenuSub from "../ui/dropdown-menu/DropdownMenuSub.vue"
import DropdownMenuSubTrigger from "../ui/dropdown-menu/DropdownMenuSubTrigger.vue"
import DropdownMenuSubContent from "../ui/dropdown-menu/DropdownMenuSubContent.vue"
import { DropdownMenuPortal } from "radix-vue"
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from "../ui/dialog"
import { Input } from "../ui/input"
import { Label } from "../ui/label"
import { currencyFormatter } from "../../Utils/currencyFormatter"
import { useItemsStore } from "../../Stores/itemsStore"
import { Link, router, usePage } from "@inertiajs/vue3"
import { useOpeningStore } from "../../Stores/openingsStore"
import axios from 'axios';
import { useCommercialOfferStore } from "../../Stores/commercialOfferStore"
import { computed, ref, watch } from "vue"
import { Toaster } from "../ui/sonner"
import { toast } from "vue-sonner"

const itemsStore = useItemsStore()
const openingsStore = useOpeningStore()
const commercialOfferStore = useCommercialOfferStore()

const { can_access_app_cart, can_access_factors, user_default_factor } = usePage().props as any

// Factor management
const selectedFactor = ref(sessionStorage.getItem('selectedFactor') || user_default_factor || 'pz')
const factors = [
    { key: 'pz', label: 'ЗЦ' },
    { key: 'p1', label: 'Р1' },
    { key: 'p2', label: 'Р2' },
    { key: 'p3', label: 'Р3' },
    { key: 'pr', label: 'РЦ' },
]

// Watch for factor changes and update session storage
watch(selectedFactor, (newValue) => {
    sessionStorage.setItem('selectedFactor', newValue)
    // Trigger recalculation in items store
    itemsStore.selectedFactor = newValue
})



// Initialize factor in items store with user's default factor
itemsStore.initializeUserFactor(user_default_factor || 'pz')
itemsStore.selectedFactor = selectedFactor.value

// SNP for Surname Name Patronymic
const snp = ref({
    surname: itemsStore.user.name.split(" ")[0],
    name: itemsStore.user.name.split(" ")[1],
    patronymic: itemsStore.user.name.split(" ")[2],
})

const order_info = computed(() => ({
    name: `${snp.value.surname || ""} ${snp.value.name || ""} ${snp.value.patronymic || ""}`.trim(),
    phone: itemsStore.user.phone,
    address: itemsStore.user.address,
    email: itemsStore.user.email,
}))

// File name dialog state
const showFileNameDialog = ref(false);
const fileName = ref("");

const openFileNameDialog = () => {
	// Load existing file name if editing
	const commercialOfferId = commercialOfferStore.commercialOfferId;
	if (commercialOfferId) {
		// Try to get stored file name if available
		fileName.value = ''; // We don't have access to it here, user can enter new one
	} else {
		fileName.value = '';
	}
	showFileNameDialog.value = true;
}

const downloadOnlyCommercialOffer = async () => {
    try {
        toast.info("Подготовка коммерческого предложения...")
        const formData = {
            customer: commercialOfferStore.commercialOffer.customer,
            manufacturer: commercialOfferStore.commercialOffer.manufacturer,
            openings: openingsStore.openings,
            additional_items: Object.values(itemsStore.additional_items).flat(),
            glass: itemsStore.glasses.find(glass => glass.id === itemsStore.selectedGlassID) || [],
            ghost_glasses: itemsStore.glasses.filter(glass => glass.id && itemsStore.selectedGhostGlassesID.includes(glass.id)),
            services: itemsStore.services.filter(service => service.id && itemsStore.selectedServicesID.includes(service.id)),
            cart_items: itemsStore.cartItems,
            total_price: itemsStore.total_price.with_discount,
            markup_percentage: itemsStore.markupPercentage,
            selected_factor: selectedFactor.value,
            file_name: fileName.value || null,
            generate_pdf: true,
        }

        const response = await axios.post('/orders/commercial-offer', formData, {
            responseType: 'blob',
            headers: { 'Content-Type': 'application/json' }
        })

        const url = window.URL.createObjectURL(new Blob([response.data]))
        const link = document.createElement('a')

        // Use user-entered filename or default
        const downloadFileName = fileName.value 
            ? `${fileName.value}.pdf`
            : `offer_${new Date().toISOString().split('T')[0]}.pdf`;

        link.href = url
        link.setAttribute('download', downloadFileName)
        document.body.appendChild(link)
        link.click()

        link.parentNode?.removeChild(link)
        
        // Close dialog and reset file name
        showFileNameDialog.value = false;
        fileName.value = "";
        
        toast.success("Коммерческое предложение успешно загружено")
    } catch (error) {
        console.error('Error downloading the PDF:', error)
        toast.error("Ошибка при загрузке коммерческого предложения")
    }
}

const saveCommercialOffer = async () => {
    try {
        toast.info("Сохранение коммерческого предложения...")
        const formData = {
            customer: commercialOfferStore.commercialOffer.customer,
            manufacturer: commercialOfferStore.commercialOffer.manufacturer,
            openings: openingsStore.openings,
            additional_items: Object.values(itemsStore.additional_items).flat(),
            glass: itemsStore.glasses.find(glass => glass.id === itemsStore.selectedGlassID) || [],
            ghost_glasses: itemsStore.glasses.filter(glass => glass.id && itemsStore.selectedGhostGlassesID.includes(glass.id)),
            services: itemsStore.services.filter(service => service.id && itemsStore.selectedServicesID.includes(service.id)),
            cart_items: itemsStore.cartItems,
            total_price: itemsStore.total_price.with_discount,
            markup_percentage: itemsStore.markupPercentage,
            selected_factor: selectedFactor.value,
            file_name: fileName.value || null,
            generate_pdf: false, // Don't generate PDF for save-only
        }

        const commercialOfferId = commercialOfferStore.commercialOfferId
        const isEditing = commercialOfferId !== null

        const response = isEditing 
            ? await axios.put(`/app/commercial-offers/${commercialOfferId}`, formData, {
                headers: { 'Content-Type': 'application/json' }
            })
            : await axios.post('/app/commercial-offers', formData, {
                headers: { 'Content-Type': 'application/json' }
            })

        // Update the commercial offer ID if it was a new one
        if (!isEditing && response.data.id) {
            commercialOfferStore.commercialOfferId = response.data.id
        }
        
        // Close dialog and reset file name
        showFileNameDialog.value = false;
        fileName.value = "";
        
        const successMessage = isEditing 
            ? "Коммерческое предложение успешно обновлено"
            : "Коммерческое предложение успешно сохранено"
        toast.success(successMessage)
    } catch (error) {
        console.error('Error saving commercial offer:', error)
        toast.error("Ошибка при сохранении коммерческого предложения")
    }
}

const downloadAndSaveCommercialOffer = async () => {
    try {
        toast.info("Сохранение и подготовка коммерческого предложения...")
        const formData = {
            customer: commercialOfferStore.commercialOffer.customer,
            manufacturer: commercialOfferStore.commercialOffer.manufacturer,
            openings: openingsStore.openings,
            additional_items: Object.values(itemsStore.additional_items).flat(),
            glass: itemsStore.glasses.find(glass => glass.id === itemsStore.selectedGlassID) || [],
            ghost_glasses: itemsStore.glasses.filter(glass => glass.id && itemsStore.selectedGhostGlassesID.includes(glass.id)),
            services: itemsStore.services.filter(service => service.id && itemsStore.selectedServicesID.includes(service.id)),
            cart_items: itemsStore.cartItems,
            total_price: itemsStore.total_price.with_discount,
            markup_percentage: itemsStore.markupPercentage,
            selected_factor: selectedFactor.value,
            file_name: fileName.value || null,
            generate_pdf: true, // Generate PDF for download
        }

        const commercialOfferId = commercialOfferStore.commercialOfferId
        const isEditing = commercialOfferId !== null

        // Both create and update use /app/commercial-offers endpoint
        // The backend will save to DB and return PDF when generate_pdf is true
        const response = isEditing 
            ? await axios.put(`/app/commercial-offers/${commercialOfferId}`, formData, {
                responseType: 'blob',
                headers: { 'Content-Type': 'application/json' }
            })
            : await axios.post('/app/commercial-offers', formData, {
                responseType: 'blob',
                headers: { 'Content-Type': 'application/json' }
            })

        // Update the commercial offer ID if it was a new one
        if (!isEditing && response.headers['x-commercial-offer-id']) {
            commercialOfferStore.commercialOfferId = parseInt(response.headers['x-commercial-offer-id'])
        }

        const url = window.URL.createObjectURL(new Blob([response.data]))
        const link = document.createElement('a')

        // Use user-entered filename or default
        const downloadFileName = fileName.value 
            ? `${fileName.value}.pdf`
            : `offer_${new Date().toISOString().split('T')[0]}.pdf`;

        link.href = url
        link.setAttribute('download', downloadFileName)
        document.body.appendChild(link)
        link.click()

        link.parentNode?.removeChild(link)
        
        // Close dialog and reset file name
        showFileNameDialog.value = false;
        fileName.value = "";
        
        const successMessage = isEditing 
            ? "Коммерческое предложение успешно обновлено и загружено"
            : "Коммерческое предложение успешно сохранено и загружено"
        toast.success(successMessage)
    } catch (error) {
        console.error('Error downloading the PDF:', error)
        toast.error("Ошибка при загрузке коммерческого предложения")
    }
}

const downloadSpecificationPDF = async () => {
    try {
        toast.info("Подготовка спецификации...")
        const formData = {
            name: order_info.value.name,
            phone: order_info.value.phone,
            address: order_info.value.address,
            email: order_info.value.email,
            cart_items: itemsStore.cartItems,
            openings: openingsStore.openings,
            total_price: itemsStore.total_price.with_discount,
            selected_factor: selectedFactor.value,
        }
    
        const response = await axios.post('/orders/list-pdf-from-calc', formData, {
            responseType: 'blob', 
            headers: { 'Content-Type': 'application/json' }
        })
    
        const url = window.URL.createObjectURL(new Blob([response.data]))
        const link = document.createElement('a')
    
        link.href = url
        link.setAttribute('download', `specification_${new Date().toISOString().split('T')[0]}.pdf`)
        document.body.appendChild(link)
        link.click()
        
        link.parentNode?.removeChild(link)
        toast.success("Спецификация успешно загружена")
    } catch (error) {
        console.error('Error downloading the PDF:', error)
        toast.error("Ошибка при загрузке спецификации")
    }
}

const downloadListPDF = async () => {
    try {
        toast.info("Подготовка перечня...")
        const formData = {
            name: order_info.value.name,
            phone: order_info.value.phone,
            address: order_info.value.address,
            email: order_info.value.email,
            cart_items: itemsStore.cartItems,
            openings: openingsStore.openings,
            total_price: itemsStore.total_price.with_discount,
            selected_factor: selectedFactor.value,
        }
    
        const response = await axios.post('/orders/simple-list-from-calc', formData, {
            responseType: 'blob', 
            headers: { 'Content-Type': 'application/json' }
        })
    
        const url = window.URL.createObjectURL(new Blob([response.data]))
        const link = document.createElement('a')
    
        link.href = url
        link.setAttribute('download', `list_${new Date().toISOString().split('T')[0]}.pdf`)
        document.body.appendChild(link)
        link.click()
        
        link.parentNode?.removeChild(link)
        toast.success("Перечень успешно загружен")
    } catch (error) {
        console.error('Error downloading the PDF:', error)
        toast.error("Ошибка при загрузке перечня")
    }
}
</script>

<template>
    <Toaster />
    <div class="h-6 md:h-10"></div>
    <div class="z-20 fixed bottom-0 sm:bottom-2 left-1/2 w-full max-w-96 transform -translate-x-1/2 backdrop-blur-sm p-2 sm:p-4 bg-white/75 dark:bg-slate-900/75 ring-1 ring-black/10 sm:rounded-xl md:rounded-2xl shadow-lg">
        <div class="flex items-center justify-between">
            <div class="flex flex-col">
                <span class="text-xs font-thin text-muted-foreground">Цена ({{ selectedFactor.toUpperCase() }}):</span>
                <span class="font-bold text-xl text-primary">{{ currencyFormatter(itemsStore.total_price.with_discount) }}</span>
            </div>

            <div class="flex gap-2 md:gap-2 items-center actions">
                <DropdownMenu v-if="can_access_factors">
                    <DropdownMenuTrigger>
                        <Button variant="outline" size="sm" class="min-w-12">
                            <span class="text-sm font-medium">{{ factors.find(factor => factor.key === selectedFactor)?.label || 'ЗЦ' }}</span>
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent class="w-32">
                        <DropdownMenuLabel>Цены</DropdownMenuLabel>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem 
                            v-for="factor in factors" 
                            :key="factor.key" 
                            @click="selectedFactor = factor.key" 
                            :class="{ 'bg-accent text-white': factor.key === selectedFactor }" 
                            class="mb-1 cursor-pointer flex items-center gap-2"
                        >
                            <span>{{ factor.label }}</span>
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
                
                <DropdownMenu>
                    <DropdownMenuTrigger>
                        <Button variant="outline" size="sm" class="">
                            <EllipsisVertical class="h-4 w-4" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent class="w-56">
                        <DropdownMenuLabel>Действия</DropdownMenuLabel>
                        
                        <DropdownMenuSeparator />
                        
                        <DropdownMenuItem @click="openFileNameDialog" class="cursor-pointer">
                            <Printer class="size-4 mr-2" />
                            <span>Печать КП</span>
                        </DropdownMenuItem>
                        <DropdownMenuItem @click="downloadSpecificationPDF" class="cursor-pointer">
                            <ScrollText class="size-4 mr-2" />
                            <span>Спецификация</span>
                        </DropdownMenuItem>
                        <DropdownMenuItem @click="downloadListPDF" class="cursor-pointer">
                            <FileText class="size-4 mr-2" />
                            <span>Перечень</span>
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
                
                <Link href="/app/cart" v-if="can_access_app_cart">
                    <Button class="hover:bg-primary/90 px-3" size="sm">
                        <!-- <span class="text-base">Заказать</span> -->
                        <ShoppingCartIcon class="size-4 ml-0" />
                    </Button>
                </Link>
            </div>
        </div>
    </div>
    
    <!-- File Name Dialog -->
    <Dialog v-model:open="showFileNameDialog">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle>Скачать коммерческое предложение</DialogTitle>
                <DialogDescription>
                    Введите имя файла (необязательно). Если оставите пустым, будет использовано имя по умолчанию.
                </DialogDescription>
            </DialogHeader>
            <div class="grid gap-4">
                <div class="grid gap-2">
                    <Label for="fileName">
                        Имя файла
                    </Label>
                    <div class="flex gap-2 items-center">
                        <Input
                            id="fileName"
                            v-model="fileName"
                            placeholder="commercial_offer_1"
                            class="flex-1"
                            @keyup.enter="downloadAndSaveCommercialOffer"
                        />
                        <span class="text-sm text-muted-foreground">.pdf</span>
                    </div>
                </div>
            </div>
            <DialogFooter class="grid grid-cols-3 gap-2">
                <Button variant="outline" @click="downloadOnlyCommercialOffer">
                    Скачать
                </Button>
                <Button variant="secondary" @click="saveCommercialOffer">
                    Сохранить
                </Button>
                <Button @click="downloadAndSaveCommercialOffer" class="col-span-3">
                    Сохранить и скачать
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>