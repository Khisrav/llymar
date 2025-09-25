<script setup lang="ts">
import { cn } from "../../../lib/utils"

export interface TextareaProps {
  class?: any
  rows?: number
  placeholder?: string
  disabled?: boolean
  readonly?: boolean
}

const props = withDefaults(defineProps<TextareaProps>(), {
  rows: 3
})

defineEmits<{
  (e: 'update:modelValue', payload: string): void
}>()

const modelValue = defineModel<string>()
</script>

<template>
  <textarea
    v-model="modelValue"
    :class="cn(
      'flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
      props.class
    )"
    :rows="props.rows"
    :placeholder="props.placeholder"
    :disabled="props.disabled"
    :readonly="props.readonly"
  />
</template> 