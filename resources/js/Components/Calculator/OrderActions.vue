<script setup lang="ts">
import { ArrowRightIcon, EllipsisVertical, Printer, Ruler, ScrollText } from "lucide-vue-next"
import Button from "../ui/button/Button.vue"
import DropdownMenu from "../ui/dropdown-menu/DropdownMenu.vue"
import DropdownMenuTrigger from "../ui/dropdown-menu/DropdownMenuTrigger.vue"
import DropdownMenuContent from "../ui/dropdown-menu/DropdownMenuContent.vue"
import DropdownMenuLabel from "../ui/dropdown-menu/DropdownMenuLabel.vue"
import DropdownMenuSeparator from "../ui/dropdown-menu/DropdownMenuSeparator.vue"
import DropdownMenuItem from "../ui/dropdown-menu/DropdownMenuItem.vue"
import { currencyFormatter } from "../../Utils/currencyFormatter"
import { useItemsStore } from "../../Stores/itemsStore"
import { Link, router } from "@inertiajs/vue3"
import { useOpeningStore } from "../../Stores/openingsStore"
import axios from 'axios';
import { useCommercialOfferStore } from "../../Stores/commercialOfferStore"
import { computed, ref } from "vue"

const itemsStore = useItemsStore()
const openingsStore = useOpeningStore()
const commercialOfferStore = useCommercialOfferStore()

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

const downloadCommercialOffer = async () => {
    try {
        const formData = {
            customer: commercialOfferStore.commercialOffer.customer,
            manufacturer: commercialOfferStore.commercialOffer.manufacturer,
            openings: openingsStore.openings,
            additional_items: itemsStore.additional_items,
            glass: itemsStore.glasses.find(glass => glass.id === itemsStore.selectedGlassID),
            services: itemsStore.services.filter(service => itemsStore.selectedServicesID.includes(service.id)),
            cart_items: itemsStore.cartItems,
            total_price: itemsStore.total_price.with_discount,
            markup_percentage: itemsStore.markupPercentage.toFixed(2),
        }

        const response = await axios.post('/orders/commercial-offer', formData, {
            responseType: 'blob',
            headers: { 'Content-Type': 'application/json' }
        })

        const url = window.URL.createObjectURL(new Blob([response.data]))
        const link = document.createElement('a')

        link.href = url
        link.setAttribute('download', `offer_${new Date().toISOString().split('T')[0]}.pdf`)
        document.body.appendChild(link)
        link.click()

        link.parentNode.removeChild(link)
    } catch (error) {
        console.error('Error downloading the PDF:', error)
        alert('Ошибка скачивания PDF')
    }
}

const downloadListPDF = async () => {
    try {
        const formData = {
            name: order_info.value.name,
            phone: order_info.value.phone,
            address: order_info.value.address,
            email: order_info.value.email,
            cart_items: itemsStore.cartItems,
            openings: openingsStore.openings,
            total_price: itemsStore.total_price.with_discount,
        }
    
        const response = await axios.post('/orders/list-pdf-from-calc', formData, {
            responseType: 'blob', 
            headers: { 'Content-Type': 'application/json' }
        })
    
        const url = window.URL.createObjectURL(new Blob([response.data]))
        const link = document.createElement('a')
    
        link.href = url
        link.setAttribute('download', `list_${new Date().toISOString().split('T')[0]}.pdf`)
        document.body.appendChild(link)
        link.click()
        
        link.parentNode.removeChild(link)
    } catch (error) {
        console.error('Error downloading the PDF:', error)
        alert('Ошибка скачивания PDF')
    }
}

const downloadSketchPDF = async () => {
    try {
        const formData = {
            openings: openingsStore.openings,
        }
    
        const response = await axios.post('/app/order/sketch', formData, {
            responseType: 'blob',
            headers: { 'Content-Type': 'application/json' }
        })
    
        const url = window.URL.createObjectURL(new Blob([response.data]))
        const link = document.createElement('a')
    
        link.href = url
        link.setAttribute('download', `sketch_${new Date().toISOString().split('T')[0]}.pdf`)
        document.body.appendChild(link)
        link.click()
        
        link.parentNode.removeChild(link)
    } catch (error) {
            console.error('Error downloading the PDF:', error)
            alert('Ошибка скачивания PDF')
    }
}
</script>

<template>
    <div class="h-6 md:h-10"></div>
    <div class="z-20 fixed bottom-0 sm:bottom-2 left-1/2 w-full max-w-96 transform -translate-x-1/2 bg-white dark:bg-slate-900 p-2 sm:p-4 border sm:rounded-xl md:rounded-2xl shadow-sm">
        <div class="flex items-center justify-between">
            <span class="font-bold text-xl text-primary">{{ currencyFormatter(itemsStore.total_price.with_discount) }}</span>

            <div class="flex gap-2 md:gap-4 items-center">
                <DropdownMenu>
                    <DropdownMenuTrigger>
                        <Button variant="outline" size="icon">
                            <EllipsisVertical />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent>
                        <DropdownMenuLabel>Действия</DropdownMenuLabel>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem @click="downloadCommercialOffer">
                            <Printer class="size-4" />
                            <span>Печать КП</span>
                        </DropdownMenuItem>
                        <DropdownMenuItem @click="downloadListPDF">
                            <ScrollText class="size-4" />
                            <span>Перечень</span>
                        </DropdownMenuItem>
                        <DropdownMenuItem @click="downloadSketchPDF">
                            <Ruler class="size-4" />
                            <span>Чертеж</span>
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
                <Link href="/app/cart">
                    <Button>
                        <span class="text-base">В корзину</span>
                        <ArrowRightIcon class="size-4" />
                    </Button>
                </Link>
            </div>
        </div>
    </div>
</template>
