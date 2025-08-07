<script setup>
import { computed } from 'vue';

const props = defineProps({
    variant: {
        type: String,
        default: 'gold',
        validator: (value) => ['gold', 'dark', 'white', 'green'].includes(value)
    },
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['sm', 'md', 'lg'].includes(value)
    },
    icon: {
        type: Object,
        default: null
    },
    animated: {
        type: Boolean,
        default: false
    },
    glowing: {
        type: Boolean,
        default: false
    }
});

const badgeClasses = computed(() => {
    const baseClasses = 'inline-flex items-center gap-2 font-medium transition-all duration-300 select-none';
    
    const variantClasses = {
        gold: 'bg-light-gold/10 backdrop-blur-md border border-light-gold text-light-gold hover:bg-light-gold/20',
        dark: 'bg-dark-green/10 backdrop-blur-md border border-dark-green text-dark-green hover:bg-dark-green/20',
        white: 'bg-white/10 backdrop-blur-md border border-white text-white hover:bg-white/20',
        green: 'bg-dark-green text-light-gold border border-dark-green/30'
    };
    
    const sizeClasses = {
        sm: 'px-3 py-1 text-xs rounded-full',
        md: 'px-4 py-2 text-sm rounded-full',
        lg: 'px-6 py-3 text-base rounded-full'
    };
    
    const animationClasses = props.animated ? 'animate-pulse' : '';
    const glowClasses = props.glowing ? 'shadow-lg shadow-light-gold/25' : '';
    
    return [
        baseClasses,
        variantClasses[props.variant],
        sizeClasses[props.size],
        animationClasses,
        glowClasses
    ].filter(Boolean).join(' ');
});

const iconClasses = computed(() => {
    const sizeMap = {
        sm: 'w-3 h-3',
        md: 'w-4 h-4',
        lg: 'w-5 h-5'
    };
    
    return [sizeMap[props.size], props.animated ? 'animate-spin' : ''].filter(Boolean).join(' ');
});
</script>

<template>
    <div :class="badgeClasses">
        <!-- Icon -->
        <component 
            v-if="icon" 
            :is="icon" 
            :class="iconClasses"
        />
        
        <!-- Badge content -->
        <span class="montserrat">
            <slot />
        </span>
    </div>
</template>

<style scoped>
/* Custom glow animation for special badges */
@keyframes glow-pulse {
    0%, 100% {
        box-shadow: 0 0 5px var(--light-gold), 0 0 10px var(--light-gold), 0 0 15px var(--light-gold);
    }
    50% {
        box-shadow: 0 0 10px var(--light-gold), 0 0 20px var(--light-gold), 0 0 30px var(--light-gold);
    }
}

.animate-glow {
    animation: glow-pulse 2s ease-in-out infinite;
}
</style> 