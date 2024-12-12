<script setup lang="ts">
import { computed } from 'vue';
import Openings from '../../Components/Calculator/Openings.vue';
import AuthenticatedHeaderLayout from '../../Layouts/AuthenticatedHeaderLayout.vue';
import { useItemsStore } from '../../Stores/itemsStore';
import { usePage } from '@inertiajs/vue3';
import { Item } from '../../lib/types';
import Items from '../../Components/Calculator/Items.vue';
import Additionals from '../../Components/Calculator/Additionals.vue';
import OrderActions from '../../Components/Calculator/OrderActions.vue';

const itemsStore = useItemsStore();

itemsStore.items = usePage().props.items as Item[]
itemsStore.base_url = usePage().props.base_url as string

itemsStore.calculate()
</script>

<template>
<AuthenticatedHeaderLayout />
<div class="container p-4 rounded-xl">
    <h1 class="text-2xl font-bold">Калькулятор</h1>
    
    <div class="">
        <Openings class="mb-8"/>
        <Additionals class="mb-8"/>
        <Items class="mb-8"/>
    </div>
</div>
<OrderActions />
</template>