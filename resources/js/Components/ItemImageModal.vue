<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { X, ChevronLeft, ChevronRight } from 'lucide-vue-next'
import { Button } from './ui/button'
import { Dialog, DialogContent, DialogHeader, DialogTitle } from './ui/dialog'
import { getImageSource } from '../Utils/getImageSource'
import { currencyFormatter } from '../Utils/currencyFormatter'
import { Item } from '../lib/types'
import { useItemsStore } from '../Stores/itemsStore'

interface Props {
    item: Item | null
    isOpen: boolean
}

const props = defineProps<Props>()
const emit = defineEmits<{
    close: []
}>()

const itemsStore = useItemsStore()
const currentImageIndex = ref(0)
const touchStartX = ref(0)
const touchEndX = ref(0)
const isTransitioning = ref(false)
const imageContainer = ref<HTMLElement | null>(null)

// All images (main + additional)
const allImages = computed(() => {
    if (!props.item) return []
    
    const images = []
    
    // Add main image
    if (props.item.img) {
        images.push(props.item.img)
    }
    
    // Add additional images
    if (props.item.images && Array.isArray(props.item.images)) {
        images.push(...props.item.images)
    }
    
    return images
})

const currentImage = computed(() => {
    if (allImages.value.length === 0) return null
    return getImageSource(allImages.value[currentImageIndex.value])
})

const itemPrice = computed(() => {
    if (!props.item?.id) return 0
    return itemsStore.itemPrice(props.item.id)
})

const cartQuantity = computed(() => {
    if (!props.item?.id) return 0
    return itemsStore.cartItems[props.item.id]?.quantity || 0
})

// Navigation functions
const goToPrevious = () => {
    if (isTransitioning.value || allImages.value.length <= 1) return
    isTransitioning.value = true
    
    currentImageIndex.value = 
        currentImageIndex.value === 0 
            ? allImages.value.length - 1 
            : currentImageIndex.value - 1
    
    setTimeout(() => {
        isTransitioning.value = false
    }, 300)
}

const goToNext = () => {
    if (isTransitioning.value || allImages.value.length <= 1) return
    isTransitioning.value = true
    
    currentImageIndex.value = 
        currentImageIndex.value === allImages.value.length - 1 
            ? 0 
            : currentImageIndex.value + 1
    
    setTimeout(() => {
        isTransitioning.value = false
    }, 300)
}

const goToImage = (index: number) => {
    if (isTransitioning.value || index === currentImageIndex.value) return
    isTransitioning.value = true
    currentImageIndex.value = index
    
    setTimeout(() => {
        isTransitioning.value = false
    }, 300)
}

// Touch/swipe handlers
const handleTouchStart = (e: TouchEvent) => {
    touchStartX.value = e.changedTouches[0].screenX
}

const handleTouchEnd = (e: TouchEvent) => {
    touchEndX.value = e.changedTouches[0].screenX
    handleGesture()
}

const handleGesture = () => {
    const threshold = 50 // minimum swipe distance
    const swipeDistance = touchEndX.value - touchStartX.value
    
    if (Math.abs(swipeDistance) > threshold) {
        if (swipeDistance > 0) {
            goToPrevious()
        } else {
            goToNext()
        }
    }
}

// Keyboard navigation
const handleKeyDown = (e: KeyboardEvent) => {
    if (!props.isOpen) return
    
    switch (e.key) {
        case 'ArrowLeft':
            e.preventDefault()
            goToPrevious()
            break
        case 'ArrowRight':
            e.preventDefault()
            goToNext()
            break
        case 'Escape':
            e.preventDefault()
            emit('close')
            break
    }
}

// Reset current image when modal opens
watch(() => props.isOpen, (isOpen) => {
    if (isOpen) {
        currentImageIndex.value = 0
        nextTick(() => {
            // Focus the image container for keyboard navigation
            imageContainer.value?.focus()
        })
    }
})

onMounted(() => {
    document.addEventListener('keydown', handleKeyDown)
})

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeyDown)
})
</script>

<template>
    <Dialog :open="isOpen" @update:open="(open) => !open && emit('close')">
        <DialogContent class="max-w-4xl w-full max-h-screen h-screen md:max-h-[90vh] md:h-auto overflow-hidden p-4">
                <DialogHeader>
                        <DialogTitle>
                            {{ item?.name || 'Товар' }}
                        </DialogTitle>
                </DialogHeader>
                
                <!-- Main content -->
                <div>
                    <div class="grid grid-cols-1 md:grid-cols-2 h-full min-h-0 gap-4">
                        <!-- Image section -->
                        <div class="relative flex items-center justify-center min-h-64 lg:min-h-0">
                            <div 
                                ref="imageContainer"
                                class="relative w-full h-full flex items-center justify-center focus:outline-none"
                                tabindex="0"
                                @touchstart="handleTouchStart"
                                @touchend="handleTouchEnd"
                            >
                                <!-- Main image -->
                                <div v-if="currentImage" class="relative w-full h-full max-w-full max-h-full">
                                    <img 
                                        :src="currentImage" 
                                        :alt="item?.name || 'Item image'"
                                        class="w-full h-full object-contain transition-opacity duration-300"
                                        :class="{ 'opacity-70': isTransitioning }"
                                    />
                                </div>
                                
                                <!-- No image placeholder -->
                                <div v-else class="flex items-center justify-center w-full h-64 bg-gray-200 dark:bg-gray-800 text-gray-400">
                                    Нет изображения
                                </div>
                                
                                <!-- Navigation arrows -->
                                <div v-if="allImages.length > 1" class="absolute inset-0 flex items-center justify-between p-2 pointer-events-none">
                                    <Button 
                                        variant="secondary" 
                                        size="icon"
                                        @click="goToPrevious"
                                        :disabled="isTransitioning"
                                        class="pointer-events-auto bg-white/80 hover:bg-white dark:bg-gray-800/80 dark:hover:bg-gray-800 shadow-lg"
                                    >
                                        <ChevronLeft class="w-4 h-4" />
                                    </Button>
                                    
                                    <Button 
                                        variant="secondary" 
                                        size="icon"
                                        @click="goToNext"
                                        :disabled="isTransitioning"
                                        class="pointer-events-auto bg-white/80 hover:bg-white dark:bg-gray-800/80 dark:hover:bg-gray-800 shadow-lg"
                                    >
                                        <ChevronRight class="w-4 h-4" />
                                    </Button>
                                </div>
                                
                                <!-- Image indicators -->
                                <div v-if="allImages.length > 1" class="absolute bottom-4 left-1/2 transform -translate-x-1/2 bg-black p-1 py-1 rounded-full">
                                    <div class="flex space-x-2">
                                        <button
                                            v-for="(_, index) in allImages"
                                            :key="index"
                                            @click="goToImage(index)"
                                            :disabled="isTransitioning"
                                            class="w-2 h-2 rounded-full transition-colors duration-200"
                                            :class="[
                                                index === currentImageIndex 
                                                    ? 'bg-white shadow-lg' 
                                                    : 'bg-white/50 hover:bg-white/70'
                                            ]"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Item details section -->
                        <div class="space-y-4 overflow-y-auto">
                            <!-- Item info -->
                            <div class="space-y-3">
                                <div v-if="item?.vendor_code" class="text-sm text-muted-foreground">
                                    <span class="text-sm text-muted-foreground">Артикул:</span>
                                    <span class="font-medium">{{ item.vendor_code }}</span>
                                </div>
                                
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-muted-foreground">Цена за ед.:</span>
                                    <span class="font-medium">{{ currencyFormatter(itemPrice) }}/{{ item?.unit }}</span>
                                </div>
                                
                                <div v-if="cartQuantity > 0" class="flex items-center justify-between">
                                    <span class="text-sm text-muted-foreground">Кол-во в корзине:</span>
                                    <span class="font-medium">{{ cartQuantity }} {{ item?.unit }}</span>
                                </div>
                                
                                <div v-if="cartQuantity > 0" class="flex items-center justify-between">
                                    <span class="text-sm text-muted-foreground">Итого:</span>
                                    <span class="font-medium">{{ currencyFormatter(itemPrice * cartQuantity) }}</span>
                                </div>
                            </div>
                            
                            <!-- Description -->
                            <div v-if="item?.description" class="space-y-2 text-sm">
                                <h3>Описание</h3>
                                <div class="text-sm text-muted-foreground whitespace-pre-wrap leading-relaxed">
                                    {{ item.description }}
                                </div>
                            </div>
                            
                            <!-- Thumbnail gallery -->
                            <div v-if="allImages.length > 1" class="space-y-2 text-sm p-1">
                                <h3>Все изображения</h3>
                                <div class="grid grid-cols-4 gap-2">
                                    <button
                                        v-for="(image, index) in allImages"
                                        :key="index"
                                        @click="goToImage(index)"
                                        :disabled="isTransitioning"
                                        class="aspect-square bg-gray-100 dark:bg-gray-800 rounded-lg overflow-hidden hover:ring-2 hover:ring-primary transition-all"
                                        :class="[
                                            index === currentImageIndex 
                                                ? 'ring-2 ring-primary' 
                                                : ''
                                        ]"
                                    >
                                        <img 
                                            :src="getImageSource(image)" 
                                            :alt="`${item?.name} - изображение ${index + 1}`"
                                            class="w-full h-full object-cover"
                                        />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        </DialogContent>
    </Dialog>
</template>
