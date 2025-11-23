<script setup lang="ts">
import { CheckIcon } from 'lucide-vue-next';

interface Props {
    currentStep: 1 | 2 | 3;
}

const props = defineProps<Props>();

const steps = [
    { number: 1, label: 'Калькулятор' },
    { number: 2, label: 'Корзина' },
    { number: 3, label: 'Оформление' },
];

const isCompleted = (stepNumber: number) => stepNumber < props.currentStep;
const isActive = (stepNumber: number) => stepNumber === props.currentStep;
</script>

<template>
    <div class="w-full py-6 px-2">
        <div class="flex items-center justify-center">
            <div class="flex items-center w-full max-w-2xl">
                <template v-for="(step, index) in steps" :key="step.number">
                    <!-- Step circle -->
                    <div class="flex flex-col items-center flex-1">
                        <div class="relative flex items-center justify-center">
                            <!-- Circle -->
                            <div 
                                class="w-10 h-10 md:w-12 md:h-12 rounded-full flex items-center justify-center font-semibold transition-all duration-300"
                                :class="{
                                    'bg-muted text-primary': isCompleted(step.number) || isActive(step.number),
                                    // 'bg-muted text-primary': isActive(step.number),
                                    'bg-muted text-muted-foreground border-2 border-muted-foreground/30': !isActive(step.number) && !isCompleted(step.number)
                                }"
                            >
                                <CheckIcon v-if="isCompleted(step.number)" class="w-5 h-5 md:w-6 md:h-6" />
                                <span v-else class="text-sm md:text-base">{{ step.number }}</span>
                            </div>
                        </div>
                        <!-- Label -->
                        <span 
                            class="mt-2 text-xs md:text-sm font-medium text-center transition-all duration-300"
                            :class="{
                                'text-primary': isCompleted(step.number),
                                'text-foreground': isActive(step.number),
                                'text-muted-foreground': !isActive(step.number) && !isCompleted(step.number)
                            }"
                        >
                            {{ step.label }}
                        </span>
                    </div>

                    <!-- Connector line (not after last step) -->
                    <div 
                        v-if="index < steps.length - 1"
                        class="flex-1 h-0.5 mx-2 md:mx-4 rounded-sm transition-all duration-300"
                        :class="{
                            'bg-primary': isCompleted(steps[index].number),
                            'bg-muted-foreground/30': !isCompleted(steps[index].number)
                        }"
                    />
                </template>
            </div>
        </div>
    </div>
</template>

