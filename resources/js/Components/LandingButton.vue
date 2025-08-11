<script setup>
import { computed } from 'vue';

const props = defineProps({
    variant: {
        type: String,
        default: 'primary',
        validator: (value) => ['primary', 'secondary', 'outline', 'badge', 'dark'].includes(value)
    },
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['sm', 'md', 'lg', 'icon'].includes(value)
    },
    disabled: {
        type: Boolean,
        default: false
    },
    loading: {
        type: Boolean,
        default: false
    },
    icon: {
        type: Object,
        default: null
    },
    iconPosition: {
        type: String,
        default: 'right',
        validator: (value) => ['left', 'right'].includes(value)
    },
    href: {
        type: String,
        default: null
    },
    external: {
        type: Boolean,
        default: false
    },
    showArrow: {
        type: Boolean,
        default: false,
    }
});

const emit = defineEmits(['click']);

const buttonClasses = computed(() => {
    const baseClasses = 'group inline-flex items-center justify-center font-medium transition-all duration-300 focus:outline-none focus-visible:outline-2 focus-visible:outline-light-gold focus-visible:outline-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';
    
    const variantClasses = {
        primary: 'bg-light-gold hover:bg-light-gold/90 text-black hover:shadow-lg hover:shadow-light-gold/25 hover:scale-105',
        secondary: 'border-2 border-white/30 hover:border-light-gold hover:bg-light-gold/10 backdrop-blur-md text-white hover:text-light-gold',
        outline: 'border border-dark-green text-dark-green hover:bg-dark-green hover:text-white',
        badge: 'bg-light-gold/10 backdrop-blur-md border border-light-gold text-light-gold hover:bg-light-gold/20',
        dark: 'bg-dark-green text-white hover:scale-105'
    };
    
    const sizeClasses = {
        sm: 'px-4 py-2 text-sm rounded-full',
        md: 'px-6 py-4 text-sm md:text-base rounded-full',
        lg: 'px-8 py-5 text-base md:text-lg rounded-full',
        icon: 'p-4 rounded-full'
    };
    
    return [
        baseClasses,
        variantClasses[props.variant],
        sizeClasses[props.size]
    ].join(' ');
});

const iconClasses = computed(() => {
    const baseIconClasses = 'transition-transform duration-300';
    
    if (props.variant === 'primary') {
        return `${baseIconClasses} group-hover:translate-x-1`;
    }
    
    if (props.variant === 'secondary' && props.icon) {
        return `${baseIconClasses} group-hover:rotate-12`;
    }
    
    if (props.variant === 'dark') {
        return `${baseIconClasses} group-hover:translate-x-1 text-light-gold`;
    }
    
    return baseIconClasses;
});

const handleClick = (event) => {
    if (!props.disabled && !props.loading) {
        emit('click', event);
    }
};
</script>

<template>
    <div class="contents">
        <component 
            :is="href ? 'a' : 'button'"
            :href="href"
            :target="external ? '_blank' : undefined"
            :rel="external ? 'noopener noreferrer' : undefined"
            :class="buttonClasses"
            :disabled="disabled || loading"
            @click="handleClick"
        >
            <!-- Left icon -->
            <component 
                v-if="icon && iconPosition === 'left'" 
                :is="icon" 
                :class="['w-5 h-5', iconClasses, iconPosition === 'left' ? 'mr-3' : 'ml-3']"
            />
            
            <!-- Loading spinner -->
            <svg 
                v-if="loading" 
                class="animate-spin -ml-1 mr-3 h-5 w-5" 
                xmlns="http://www.w3.org/2000/svg" 
                fill="none" 
                viewBox="0 0 24 24"
            >
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            
            <!-- Button text -->
            <span class="montserrat">
                <slot />
            </span>
            
            <!-- Right icon or arrow -->
            <component 
                v-if="icon && iconPosition === 'right'" 
                :is="icon" 
                :class="[
                    'w-5 h-5',
                    iconClasses,
                    iconPosition === 'right'
                        ? ($slots.default ? 'ml-3' : '')
                        : ($slots.default ? 'mr-3' : '')
                ]"
            />
            
            <!-- Default arrow for primary buttons -->
            <svg 
                v-else-if="variant === 'primary' && !icon || showArrow" 
                width="49" height="8" viewBox="0 0 49 8" fill="none" xmlns="http://www.w3.org/2000/svg" class="ml-3 md:ml-4"
                :class="iconClasses"
            >
                <path d="M48.3536 4.35356C48.5488 4.1583 48.5488 3.84171 48.3536 3.64645L45.1716 0.464471C44.9763 0.269209 44.6597 0.269209 44.4645 0.464471C44.2692 0.659733 44.2692 0.976316 44.4645 1.17158L47.2929 4.00001L44.4645 6.82843C44.2692 7.02369 44.2692 7.34028 44.4645 7.53554C44.6597 7.7308 44.9763 7.7308 45.1716 7.53554L48.3536 4.35356ZM0 4L-5.73712e-08 4.5L48 4.50001L48 4.00001L48 3.50001L5.73712e-08 3.5L0 4Z" :fill="variant === 'dark' ? 'white' : 'black'"/>
            </svg>

        </component>
    </div>
</template> 