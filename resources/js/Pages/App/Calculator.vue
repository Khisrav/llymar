<script setup lang="ts">
import Openings from '../../Components/Calculator/Openings.vue';
import AuthenticatedHeaderLayout from '../../Layouts/AuthenticatedHeaderLayout.vue';
import { useItemsStore } from '../../Stores/itemsStore';
import { Head, usePage } from '@inertiajs/vue3';
import { Category, Item, User } from '../../lib/types';
import Items from '../../Components/Calculator/Items.vue';
import Additionals from '../../Components/Calculator/Additionals.vue';
import OrderActions from '../../Components/Calculator/OrderActions.vue';
import CommercialOfferFields from '../../Components/Calculator/CommercialOfferFields.vue';
import CustomPricing from '../../Components/Calculator/CustomPricing.vue';
import { MessageCircleWarningIcon } from 'lucide-vue-next';

const itemsStore = useItemsStore();
const { user_default_factor } = usePage().props as any

console.log(usePage().props.additional_items)
itemsStore.items = usePage().props.items as Item[]
itemsStore.additional_items = usePage().props.additional_items as { [key: number]: Item[] }
itemsStore.ghost_handles = usePage().props.ghost_handles as Item[]
itemsStore.glasses = usePage().props.glasses as Item[]
itemsStore.services = usePage().props.services as Item[]
itemsStore.user = usePage().props.user as User
itemsStore.categories = usePage().props.categories as Category[]

// Initialize user's default factor before calculating
itemsStore.initializeUserFactor(user_default_factor || 'pz')
itemsStore.initiateCartItems()
itemsStore.calculate()
</script>

<template>
<Head title="Калькулятор" />
<AuthenticatedHeaderLayout />
<div class="container p-2 pt-4 md:pt-8 rounded-xl">
    <!-- <h1 class="text-2xl font-bold">Калькулятор</h1> -->
    
    <div class="flex flex-row gap-4 p-4 border border-red-100 bg-red-50 text-red-600 rounded-md mb-8">
        <div>
            <MessageCircleWarningIcon />
        </div>
        <div>
            <p>С 20 июня 2026 по 21 июня 2026 будут вестись технические работы</p>
        </div>
    </div>

    <div>
        <CommercialOfferFields class="mb-8" />
        <Openings class="mb-8" />
        <CustomPricing class="mb-8" />
        <Items class="mb-8" />
        <Additionals class="mb-8" />
        <br>
    </div>
</div>
<OrderActions />
</template>