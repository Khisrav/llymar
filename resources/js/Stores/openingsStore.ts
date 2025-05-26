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
        const randomChoice = (arr: any) => arr[Math.floor(Math.random() * arr.length)]
        
        const randomType = randomChoice(Object.keys(openingTypes))
        const randomWidth = randomChoice([3000, 3100, 3200, 3300, 3400, 3500, 3600, 3700, 3800, 3900, 4000])
        
        const doorsArr = ['left', 'right'].includes(randomType) ? [2, 3, 4, 5, 6, 8] : (randomType === 'center' ? [4, 6, 8, 10] : [3])
        const randomDoors = randomChoice(doorsArr)

        openings.value.push({
            doors: randomDoors,
            width: randomWidth,
            height: defaultHeight.value,
            type: randomType,
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
