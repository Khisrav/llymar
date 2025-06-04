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
        'blind-glazing': 'Глухое остекление',
        triangle: 'Треугольник',
    }
    
    const defaultHeight = ref(2700)

    const opening_images: OpeningImages = {
        left: '/assets/openings/openings-left.jpg',
        right: '/assets/openings/openings-right.jpg',
        center: '/assets/openings/openings-center.jpg',
        'inner-left': '/assets/openings/openings-inner-left.jpg',
        'inner-right': '/assets/openings/openings-inner-right.jpg',
        'blind-glazing': '/assets/openings/openings-blind-glazing.jpg',
        triangle: '/assets/openings/openings-triangle.jpg',
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
        // Get the width of the last opening and increment by 1
        const lastOpening = openings.value[openings.value.length - 1]
        const newWidth = lastOpening ? lastOpening.width + 1 : 3000
        
        openings.value.push({
            doors: 2,
            width: newWidth,
            height: defaultHeight.value,
            type: 'left',
        })
    }
    
    const addDuplicateOpening = (index: number) => {
        console.log('addDuplicateOpening', index)
        openings.value.splice(index + 1, 0, openings.value[index])
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
    
    const setDefaultHeightToAll = () => {
        openings.value.forEach(opening => {
            opening.height = defaultHeight.value
        })
    }

    return {
        openingTypes,
        defaultHeight,
        opening_images,
        openings,
        addOpening,
        removeOpening,
        setDefaultHeightToAll,
        addDuplicateOpening,
    }
})
