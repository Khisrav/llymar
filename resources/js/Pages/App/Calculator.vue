<script setup lang="ts">
import Openings from '../../Components/Calculator/Openings.vue';
import AuthenticatedHeaderLayout from '../../Layouts/AuthenticatedHeaderLayout.vue';
import { useItemsStore } from '../../Stores/itemsStore';
import { Head, usePage } from '@inertiajs/vue3';
import { Item, User } from '../../lib/types';
import Items from '../../Components/Calculator/Items.vue';
import Additionals from '../../Components/Calculator/Additionals.vue';
import OrderActions from '../../Components/Calculator/OrderActions.vue';

const itemsStore = useItemsStore();

itemsStore.items = usePage().props.items as Item[]
itemsStore.additional_items = usePage().props.additional_items as Item[]
itemsStore.glasses = usePage().props.glasses as Item[]
itemsStore.services = usePage().props.services as Item[]
itemsStore.user = usePage().props.user as User
console.log(usePage().props.categories)
itemsStore.initiateCartItems()
itemsStore.calculate()
</script>

<template>
<Head title="Калькулятор" />
<AuthenticatedHeaderLayout />
<div class="container p-2 pt-4 md:pt-8 rounded-xl">
    <h1 class="text-2xl font-bold">Калькулятор</h1>
    
    <div class="mt-4">
        <Openings class="mb-8" />
        <Additionals class="mb-8" />
        <Items class="mb-8" />
    </div>
</div>
<OrderActions />
</template>