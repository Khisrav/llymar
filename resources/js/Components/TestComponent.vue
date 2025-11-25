<script setup lang="ts">
import { defineModel } from 'vue';

const doors = defineModel<number>();
</script>
<template>
    <input v-model="doors" type="number"/>
</template>