<script setup lang="ts">
import { ref } from 'vue'
import Button from '../ui/button/Button.vue'
import { Eye, EyeOff } from 'lucide-vue-next'
import Input from '../ui/input/Input.vue'
import Label from '../ui/label/Label.vue'
import { useCommercialOfferStore } from '../../Stores/commercialOfferStore'
import { useItemsStore } from '../../Stores/itemsStore'
import { vMaska } from 'maska/vue'

const isCommercialOfferHidden = ref(true)
const itemsStore = useItemsStore()
const commercialOfferStore = useCommercialOfferStore()

commercialOfferStore.commercialOffer.manufacturer = {
    manufacturer: itemsStore.user.name,
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
                    <Input v-maska="'+7 (###) ###-##-##'" v-model="commercialOfferStore.commercialOffer.customer.phone" class="w-full" placeholder="Москва, ул. Пушкина 123, №11" />
                </div>
                
                <div>
                    <Label class="mb-2 block">Примечание:</Label>
                    <textarea v-model="commercialOfferStore.commercialOffer.customer.comment" class="w-full block border rounded p-2 text-sm" placeholder="Примечание"></textarea>
                </div>
            </div>
            
            <div class="bg-white dark:bg-slate-900 border rounded-xl hover:shadow-2xl hover:shadow-slate-100 dark:hover:shadow-slate-800 transition-all hover:z-10 p-2 md:p-4 space-y-2 md:space-y-4">
                <h4 class="font-semibold text-muted-foreground">Информация о производителе</h4>
                <div>
                    <div class="flex items-center justify-between md:gap-4">
                        <span>Производитель: </span>
                        <span class="font-semibold">{{ itemsStore.user.company }}</span>
                    </div>
                    <div class="flex items-center justify-between md:gap-4">
                        <span>Телефон: </span>
                        <span class="font-semibold">{{ itemsStore.user.phone }}</span>
                    </div>
                    <div class="flex items-center justify-between md:gap-4">
                        <span>Почта: </span>
                        <span class="font-semibold">{{ itemsStore.user.email }}</span>
                    </div>
                </div>
            </div>
		</div>
    </div>
</template>