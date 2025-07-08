<script setup lang="ts">
import { ref, watch } from 'vue'
import Button from '../ui/button/Button.vue'
import { Eye, EyeOff } from 'lucide-vue-next'
import Input from '../ui/input/Input.vue'
import Label from '../ui/label/Label.vue'
import { useCommercialOfferStore } from '../../Stores/commercialOfferStore'
import { useItemsStore } from '../../Stores/itemsStore'
import { vMaska } from 'maska/vue'
import Select from '../ui/select/Select.vue'
import SelectTrigger from '../ui/select/SelectTrigger.vue'
import SelectValue from '../ui/select/SelectValue.vue'
import SelectContent from '../ui/select/SelectContent.vue'
import SelectItem from '../ui/select/SelectItem.vue'
import { usePage } from '@inertiajs/vue3'

const isCommercialOfferHidden = ref(true)
const itemsStore = useItemsStore()
const commercialOfferStore = useCommercialOfferStore()

// Initialize manufacturer info
commercialOfferStore.commercialOffer.manufacturer = {
    title: 'Информация о производителе',
    manufacturer: itemsStore.user.company || '',
    company: itemsStore.user.company || '',
    phone: itemsStore.user.phone || '',
}


</script>

<template>
    <div class="border p-2 md:p-4 rounded-2xl bg-background">
        <div class="flex items-center">
            <h2 class="text-xl font-bold text-muted-foreground">Коммерческое предложение</h2>
            <Button variant="outline" size="icon" class="ml-auto rounded-lg" @click="isCommercialOfferHidden = !isCommercialOfferHidden">
                <Eye v-if="!isCommercialOfferHidden" class="size-6" />
                <EyeOff v-else class="size-6" />
            </Button>
        </div>

        <div v-show="!isCommercialOfferHidden" class="grid md:grid-cols-2 gap-2 md:gap-4 mt-4">
            <!-- Customer Information -->
            <div class="bg-white dark:bg-slate-900 border rounded-xl hover:shadow-2xl hover:shadow-slate-100 dark:hover:shadow-slate-800 transition-all hover:z-10 p-2 md:p-4 space-y-2 md:space-y-4">
                <h4 class="font-semibold text-muted-foreground">Информация о клиенте</h4>
                <div>
                    <Label class="mb-2 block">Ф.И.О. клиента:</Label>
                    <Input v-model="commercialOfferStore.commercialOffer.customer.name" class="w-full" placeholder="Иванов Иван Иванович" />
                </div>
                
                <div>
                    <Label class="mb-2 block">Адрес:</Label>
                    <Input v-model="commercialOfferStore.commercialOffer.customer.address" class="w-full" placeholder="Москва, ул. Пушкина 123, №11" />
                </div>
                
                <div>
                    <Label class="mb-2 block">Телефон:</Label>
                    <Input v-maska="'+7 (###) ###-##-##'" 
                           v-model="commercialOfferStore.commercialOffer.customer.phone" 
                           class="w-full" 
                           placeholder="+7 (999) 999-99-99" />
                </div>
                
                <div>
                    <Label class="mb-2 block">Примечание:</Label>
                    <textarea v-model="commercialOfferStore.commercialOffer.customer.comment" 
                              class="w-full block border rounded p-2 text-sm" 
                              placeholder="Примечание"></textarea>
                </div>
            </div>

            <!-- Manufacturer Information -->
            <div class="bg-white dark:bg-slate-900 border rounded-xl hover:shadow-2xl hover:shadow-slate-100 dark:hover:shadow-slate-800 transition-all hover:z-10 p-2 md:p-4 space-y-2 md:space-y-4">
                <Input v-model="commercialOfferStore.commercialOffer.manufacturer.title" 
                       class="w-full font-semibold" />
                <div>
                    <div class="mb-4">
                        <Label class="mb-2 block">Производитель:</Label>
                        <Input v-model="commercialOfferStore.commercialOffer.manufacturer.manufacturer" 
                               class="w-full" 
                               placeholder="Название компании" />
                    </div>
                    
                    <div class="mb-4">
                        <Label class="mb-2 block">Телефон:</Label>
                        <Input v-maska="'+7 (###) ###-##-##'"
                               v-model="commercialOfferStore.commercialOffer.manufacturer.phone"
                               class="w-full"
                               placeholder="+7 (999) 999-99-99" />
                    </div>


                </div>
            </div>
        </div>
    </div>
</template>