<script setup lang="ts">
import { ref, onMounted, onUnmounted } from "vue";
import { Button } from "./ui/button";

interface Props {
    variant?: 'icon' | 'inline'
    expanded?: boolean
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'icon',
    expanded: true
})

type Theme = 'light' | 'dark' | 'system';

const currentTheme = ref<Theme>('system');

const getSystemTheme = (): 'light' | 'dark' => {
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
};

const applyTheme = (theme: Theme) => {
    if (theme === 'system') {
        const systemTheme = getSystemTheme();
        document.documentElement.classList.toggle('dark', systemTheme === 'dark');
    } else {
        document.documentElement.classList.toggle('dark', theme === 'dark');
    }
};

const initializeTheme = () => {
    const savedTheme = localStorage.getItem("theme") as Theme | null;
    
    // Default to system if no theme is saved
    currentTheme.value = savedTheme || 'system';
    
    applyTheme(currentTheme.value);
};

const themeToggler = () => {
    // Cycle through: system -> light -> dark -> system
    if (currentTheme.value === 'system') {
        currentTheme.value = 'light';
    } else if (currentTheme.value === 'light') {
        currentTheme.value = 'dark';
    } else {
        currentTheme.value = 'system';
    }
    
    localStorage.setItem("theme", currentTheme.value);
    applyTheme(currentTheme.value);
};

// Listen for system theme changes when in system mode
let mediaQuery: MediaQueryList | null = null;
const handleSystemThemeChange = () => {
    if (currentTheme.value === 'system') {
        applyTheme('system');
    }
};

onMounted(() => {
    initializeTheme();
    
    mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
    mediaQuery.addEventListener('change', handleSystemThemeChange);
});

onUnmounted(() => {
    if (mediaQuery) {
        mediaQuery.removeEventListener('change', handleSystemThemeChange);
    }
});
</script>

<script lang="ts">
export default {
    name: 'ThemeSwitcher'
}
</script>

<template>
<!-- Icon-only button for mobile -->
<Button 
    v-if="variant === 'icon'" 
    variant="outline" 
    size="icon" 
    @click="themeToggler" 
    class="rounded-full"
>
    <!-- Moon icon (Dark mode) -->
    <svg
        v-if="currentTheme === 'dark'"
        class="w-6 h-6"
        fill="currentColor"
        viewBox="0 0 20 20"
        xmlns="http://www.w3.org/2000/svg"
    >
        <path
            d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"
        ></path>
    </svg>
    
    <!-- Sun icon (Light mode) -->
    <svg
        v-else-if="currentTheme === 'light'"
        class="w-6 h-6"
        fill="currentColor"
        viewBox="0 0 20 20"
        xmlns="http://www.w3.org/2000/svg"
    >
        <path
            d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"
            fill-rule="evenodd"
            clip-rule="evenodd"
        ></path>
    </svg>
    
    <!-- Computer/Monitor icon (System mode) -->
    <svg
        v-else
        class="w-6 h-6"
        fill="currentColor"
        viewBox="0 0 20 20"
        xmlns="http://www.w3.org/2000/svg"
    >
        <path
            fill-rule="evenodd"
            d="M3 5a2 2 0 012-2h10a2 2 0 012 2v8a2 2 0 01-2 2h-2.22l.123.489.804.804A1 1 0 0113 18H7a1 1 0 01-.707-1.707l.804-.804L7.22 15H5a2 2 0 01-2-2V5zm5.771 7H5V5h10v7H8.771z"
            clip-rule="evenodd"
        />
    </svg>
</Button>

<!-- Inline with text for desktop sidebar -->
<button 
    v-else
    @click="themeToggler"
    class="flex items-center gap-4 rounded-lg px-3 py-2.5 text-muted-foreground hover:bg-muted hover:text-muted-foreground transition-all duration-300 ease-in-out overflow-hidden"
>
    <div class="flex-shrink-0 h-5 ml-[2px] flex items-center justify-center w-full">
        <!-- Moon icon (Dark mode) -->
        <svg
            v-if="currentTheme === 'dark'"
            class="w-5 h-5"
            fill="currentColor"
            viewBox="0 0 20 20"
            xmlns="http://www.w3.org/2000/svg"
        >
            <path
                d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"
            ></path>
        </svg>
        
        <!-- Sun icon (Light mode) -->
        <svg
            v-else-if="currentTheme === 'light'"
            class="w-5 h-5"
            fill="currentColor"
            viewBox="0 0 20 20"
            xmlns="http://www.w3.org/2000/svg"
        >
            <path
                d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"
                fill-rule="evenodd"
                clip-rule="evenodd"
            ></path>
        </svg>
        
        <!-- Computer/Monitor icon (System mode) -->
        <svg
            v-else
            class="w-5 h-5"
            fill="currentColor"
            viewBox="0 0 20 20"
            xmlns="http://www.w3.org/2000/svg"
        >
            <path
                fill-rule="evenodd"
                d="M3 5a2 2 0 012-2h10a2 2 0 012 2v8a2 2 0 01-2 2h-2.22l.123.489.804.804A1 1 0 0113 18H7a1 1 0 01-.707-1.707l.804-.804L7.22 15H5a2 2 0 01-2-2V5zm5.771 7H5V5h10v7H8.771z"
                clip-rule="evenodd"
            />
        </svg>
    </div>
    <!-- <span 
        :class="[
            'text-sm font-medium whitespace-nowrap transition-all duration-300 ease-in-out',
        ]"
    >
        {{ currentTheme === 'dark' ? 'Темная' : currentTheme === 'light' ? 'Светлая' : 'Системная' }}
    </span> -->
</button>
</template>