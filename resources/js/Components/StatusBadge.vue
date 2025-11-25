<script setup lang="ts">
import { withDefaults, defineProps, computed } from 'vue';

interface Props {
  status: string;
}

// Define props with defaults
const props = withDefaults(defineProps<Props>(), {
  status: 'created',
});

// Base classes that are common for all statuses
const baseClasses = 'inline-flex items-center rounded-full px-3 py-0.5 text-xs font-semibold leading-5';

// Map statuses to their unique color classes
const statusColorClasses = {
  created: 'bg-blue-50 text-blue-700 dark:bg-blue-900 dark:text-blue-100',
  paid: 'bg-green-50 text-green-700 dark:bg-green-900 dark:text-green-100',
  expired: 'bg-yellow-50 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-100',
  assembled: 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-100',
  sent: 'bg-purple-50 text-purple-700 dark:bg-purple-900 dark:text-purple-100',
  completed: 'bg-teal-50 text-teal-700 dark:bg-teal-900 dark:text-teal-100',
  archived: 'bg-gray-50 text-gray-700 dark:bg-gray-900 dark:text-gray-100',
} as any;

const statusTranslations = {
  created: 'Создан',
  paid: 'Оплачен',
  expired: 'Просрочен',
  assembled: 'Собран',
  sent: 'Отправлен',
  completed: 'Завершен',
  archived: 'Архивирован',
} as any;

// Make badgeClasses reactive via computed
const badgeClasses = computed(() => {
  return `${baseClasses} ${statusColorClasses[props.status] || ''}`;
});
</script>

<template>
  <span :class="badgeClasses">
    {{ statusTranslations[props.status] }}
  </span>
</template>