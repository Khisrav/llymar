<script setup lang="ts">
import Openings from '../../Components/Calculator/Openings.vue';
import AuthenticatedHeaderLayout from '../../Layouts/AuthenticatedHeaderLayout.vue';
import { useItemsStore } from '../../Stores/itemsStore';
import { Head, usePage } from '@inertiajs/vue3';
import { Category, Item, User, WholesaleFactor } from '../../lib/types';
import Items from '../../Components/Calculator/Items.vue';
import Additionals from '../../Components/Calculator/Additionals.vue';
import OrderActions from '../../Components/Calculator/OrderActions.vue';
import CommercialOfferFields from '../../Components/Calculator/CommercialOfferFields.vue';

const itemsStore = useItemsStore();

itemsStore.items = usePage().props.items as Item[]
itemsStore.additional_items = usePage().props.additional_items as Item[]
itemsStore.glasses = usePage().props.glasses as Item[]
itemsStore.services = usePage().props.services as Item[]
itemsStore.user = usePage().props.user as User
itemsStore.categories = usePage().props.categories as Category[]
itemsStore.wholesale_factor = usePage().props.wholesale_factor as WholesaleFactor

itemsStore.initiateCartItems()
itemsStore.calculate()
</script>

<template>
<Head title="Калькулятор" />
<AuthenticatedHeaderLayout />
<div class="container p-2 pt-4 md:pt-8 rounded-xl">
    <h1 class="text-2xl font-bold">Калькулятор</h1>
    
    <div class="mt-4">
        <CommercialOfferFields class="mb-8" />
        <Openings class="mb-8" />
        <Additionals class="mb-8" />
        <Items class="mb-8" />
    </div>
</div>
<OrderActions />
</template>