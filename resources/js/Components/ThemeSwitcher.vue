<script setup lang="ts">
import { ref } from "vue";
import { Button } from "./ui/button";

interface Props {
    variant?: 'icon' | 'inline'
    expanded?: boolean
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'icon',
    expanded: true
})

if (localStorage.getItem("theme")) {
    document.documentElement.classList.add(localStorage.getItem("theme") as string);
}

const isDarkMode = ref(document.documentElement.classList.contains("dark"));

const themeToggler = () => {
    document.documentElement.classList.toggle("dark");

    isDarkMode.value = document.documentElement.classList.contains("dark");

    localStorage.setItem("theme", isDarkMode.value ? "dark" : "");
}
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
    <svg
        class="w-6 h-6"
        :class="[isDarkMode ? 'hidden' : '']"
        fill="currentColor"
        viewBox="0 0 20 20"
        xmlns="http://www.w3.org/2000/svg"
    >
        <path
            d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"
        ></path>
    </svg>
    <svg
        class="w-6 h-6"
        :class="[isDarkMode ? '' : 'hidden']"
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
</Button>

<!-- Inline with text for desktop sidebar -->
<button 
    v-else
    @click="themeToggler"
    class="flex items-center gap-4 rounded-lg px-3 py-2.5 text-muted-foreground hover:bg-muted hover:text-muted-foreground transition-all duration-300 ease-in-out overflow-hidden w-full"
>
    <div class="flex-shrink-0 h-5 w-5 ml-[2px] flex items-center justify-center">
        <svg
            class="w-5 h-5"
            :class="[isDarkMode ? 'hidden' : '']"
            fill="currentColor"
            viewBox="0 0 20 20"
            xmlns="http://www.w3.org/2000/svg"
        >
            <path
                d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"
            ></path>
        </svg>
        <svg
            class="w-5 h-5"
            :class="[isDarkMode ? '' : 'hidden']"
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
    </div>
    <span 
        :class="[
            'text-sm font-medium whitespace-nowrap transition-all duration-300 ease-in-out',
        ]"
    >
        {{ isDarkMode ? 'Темная' : 'Светлая' }}
    </span>
</button>
</template>