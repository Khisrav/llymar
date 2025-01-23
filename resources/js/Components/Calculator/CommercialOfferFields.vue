<script setup lang="ts">
import { ref } from 'vue'
import Button from '../ui/button/Button.vue'
import { Eye, EyeOff } from 'lucide-vue-next'
import Input from '../ui/input/Input.vue'
import Label from '../ui/label/Label.vue'
import { useCommercialOfferStore } from '../../Stores/commercialOfferStore'

const isCommercialOfferHidden = ref(true)
const commercialOfferStore = useCommercialOfferStore()
</script>

<template>
    <div class="border p-2 md:p-4 rounded-2xl">
        <div class="flex items-center">
			<h2 class="text-xl font-bold text-muted-foreground">Коммерческое предложение</h2>
            <Button variant="outline" size="icon" class="ml-auto rounded-lg" @click="isCommercialOfferHidden = !isCommercialOfferHidden">
                <Eye v-if="!isCommercialOfferHidden" class="size-6" />
                <EyeOff v-else class="size-6" />
            </Button>
		</div>
		
        <div v-if="!isCommercialOfferHidden" class="grid md:grid-cols-2 gap-2 md:gap-4 mt-4">
            <div class="bg-white dark:bg-slate-900 border rounded-xl hover:shadow-2xl hover:shadow-slate-100 dark:hover:shadow-slate-800 transition-all hover:z-10 p-2 md:p-4 space-y-2 md:space-y-4">
                <h4 class="font-semibold text-muted-foreground">Информация о клиенте</h4>
                <div>
                    <Label class="mb-2 block">Ф.И.О. клиента:</Label>
                    <Input class="w-full" placeholder="Иванов Иван Иванович" />
                </div>
                
                <div>
                    <Label class="mb-2 block">Адрес:</Label>
                    <Input class="w-full" placeholder="Москва, ул. Пушкина 123, №11" />
                </div>
                
                <div>
                    <Label class="mb-2 block">Телефон:</Label>
                    <Input class="w-full" placeholder="Москва, ул. Пушкина 123, №11" />
                </div>
            </div>
            
            <div class="bg-white dark:bg-slate-900 border rounded-xl hover:shadow-2xl hover:shadow-slate-100 dark:hover:shadow-slate-800 transition-all hover:z-10 p-2 md:p-4 space-y-2 md:space-y-4">
                <h4 class="font-semibold text-muted-foreground">Информация о производителе</h4>
                <div class="flex items-center justify-between md:gap-4">
                    <span>Организация/Компания: </span>
                    <span>{{ itemsStore.user.company ?? '-' }}</span>
                </div>
                
                    <span>Представитель: </span>
                    <span>Номер телефона: </span>
                    <span>Электронная почта: </span>
            </div>
		</div>
    </div>
</template>