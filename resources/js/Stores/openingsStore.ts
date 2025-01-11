import { defineStore } from 'pinia'
import { ref, watch } from 'vue'
import { Opening, OpeningImages, OpeningType } from '../lib/types'

export const useOpeningStore = defineStore('openingStore', () => {
    const openingTypes: OpeningType = {
        left: 'Левый проем',
        right: 'Правый проем',
        center: 'Центральный проем',
        'inner-left': 'Входная группа левая',
        'inner-right': 'Входная группа правая',
    }

    const opening_images: OpeningImages = {
        left: 'https://llymar.ru/assets/openings-left.jpg',
        right: 'https://llymar.ru/assets/openings-right.jpg',
        center: 'https://llymar.ru/assets/openings-center.jpg',
        'inner-left': 'https://llymar.ru/assets/openings-inner-left.jpg',
        'inner-right': 'https://llymar.ru/assets/openings-inner-right.jpg',
        'blind-glazing': 'https://llymar.ru/assets/openings-blind-glazing.jpg',
        triangle: 'https://llymar.ru/assets/openings-triangle.jpg',
    }

    const openings = ref<Opening[]>(sessionStorage.getItem('openings') ? JSON.parse(sessionStorage.getItem('openings') as string) : [
        {
            doors: 2,
            width: 3000,
            height: 2700,
            type: 'left',
        },
    ])

    const addOpening = () => {
        openings.value.push({
            doors: 2,
            width: 3000,
            height: 2700,
            type: 'left',
        })
    }
    
    watch(openings, () => {
        sessionStorage.setItem('openings', JSON.stringify(openings.value))
    }, { deep: true })
    
    if (!sessionStorage.getItem('openings')) {
        sessionStorage.setItem('openings', JSON.stringify(openings.value))
    }

    const removeOpening = (index: number) => {
        openings.value.splice(index, 1)
    }

    return {
        openingTypes,
        opening_images,
        openings,
        addOpening,
        removeOpening,
    }
})
