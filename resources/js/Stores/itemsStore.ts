import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import { useOpeningStore } from './openingsStore'
import { Item, CartItem } from '../lib/types'

export const useItemsStore = defineStore('itemsStore', () => {
    const openingsStore = useOpeningStore()

    const items = ref<Item[]>([])
    const additional_items = ref<Item[]>([])
    const glasses = ref<Item[]>([])
    const base_url = ref('')
    const cartItems = ref<{ [key: number]: CartItem }>({})
    
    // const selectedServices = 
    // const selectedGlasses = ref(0)

    const LEFT_RIGHT = ['left', 'right']
    const INNER_TYPES = ['inner-left', 'inner-right']
    const CENTER_TYPE = ['center']

    const initiateCartItems = () => {
        const allItems = [...items.value, ...additional_items.value]
        allItems.forEach(item => { cartItems.value[item.id] = { quantity: 0 } })
    }

    const getItemQuantity = (vendorCode: string): number => {
        const item = items.value.find(i => i.vendor_code === vendorCode)
        return cartItems.value[item?.id as number]?.quantity || 0
    }
    
    const L1_L3_multiplier = (vendor_code: 'L1' | 'L3', doors: number, type: 'left' | 'right' | 'center' | 'inner-left' | 'inner-right'): number => {
        const multipliers: Record<string, Record<string, Record<number, number>>> = {
            left: {
                L1: { 2: 0, 3: 1, 4: 0, 5: 1, 6: 2, 7: 1, 8: 2 },
                L3: { 2: 1, 3: 0, 4: 2, 5: 1, 6: 0, 7: 2, 8: 1 },
            },
            right: {
                L1: { 2: 0, 3: 1, 4: 0, 5: 1, 6: 2, 7: 1, 8: 2 },
                L3: { 2: 1, 3: 0, 4: 2, 5: 1, 6: 0, 7: 2, 8: 1 },
            },
            center: {
                L1: { 4: 0, 6: 1, 8: 0, 10: 1 },
                L3: { 4: 1, 6: 0, 8: 2, 10: 1 },
            },
            'inner-left': {
                L1: { 3: 0 },
                L3: { 3: 1 },
            },
            'inner-right': {
                L1: { 3: 0 },
                L3: { 3: 1 },
            },
        };
    
        return multipliers[type]?.[vendor_code]?.[doors] ?? 0; // Return 0 if any key is missing
    };
    

    const calculateQuantity = (item: Item): number => {
        const { vendor_code } = item
        const openings = openingsStore.openings

        const quantityMap: { [key: string]: () => number } = {
            L1: () => openings.reduce((acc, { type, width, doors }) => {
                let a, b, c
                a = Math.floor(width / 1000 / 3) * 3
                b = L1_L3_multiplier(vendor_code as 'L1' | 'L3', doors, type)
                c = ((width % 3) ? 3 : 0)
                return acc + a * b + c
            }, 0),
            L2: () => getItemQuantity('L1'),
            L3: () => openings.reduce((acc, { type, width, doors }) => {
                let a, b, c
                a = Math.floor(width / 1000 / 3) * 3
                b = L1_L3_multiplier(vendor_code as 'L1' | 'L3', doors, type)
                c = ((width % 3) ? 3 : 0)
                return acc + a * b + c
            }, 0),
            L4: () => getItemQuantity('L3'),
            L5: () => Math.ceil((openings.reduce((acc, { width }) => acc + width - 1800, 0) - 1800) / 1000) + 3,
            L6: () => openings.length * 6,
            L8: () => openings.reduce((acc, { type, doors }) => {
                if (LEFT_RIGHT.includes(type)) return acc + (doors * 2 - 2)
                if (CENTER_TYPE.includes(type)) return acc + (doors * 2 - 4)
                if (INNER_TYPES.includes(type)) return acc + 2
                return acc
            }, 0),
            L9: () => openings.reduce((acc, { type }) => acc + ([...CENTER_TYPE, ...INNER_TYPES].includes(type) ? 1 : 0), 0),
            L12: () => getItemQuantity('L9') * 9 + getItemQuantity('L2') * 6 + getItemQuantity('L4') * 4,
            L13: () => getItemQuantity('L8') * 3 + getItemQuantity('L5') * 2,
            L14: () => getItemQuantity('L6') * 2,
            L15: () => openings.reduce((acc, { type, doors }) => acc + (type === 'right' ? doors - 1 : 0)
                + (CENTER_TYPE.includes(type) ? doors / 2 - 1 : 0)
                + (type === 'inner-right' ? 1 : 0), 0),
            L16: () => openings.reduce((acc, { type, doors }) => acc + (CENTER_TYPE.includes(type) ? doors / 2 : 0)
                + (type === 'left' ? 1 : 0)
                + (type === 'right' ? doors : 0)
                + (type === 'inner-left' ? 1 : 0)
                + (type === 'inner-right' ? 2 : 0), 0),
            L17: () => openings.reduce((acc, { type }) => acc + ([...CENTER_TYPE, ...INNER_TYPES].includes(type) ? 1 : 0), 0),
            L18: () => openings.reduce((acc, { type, doors }) => acc + (type === 'left' ? doors - 1 : 0)
                + (CENTER_TYPE.includes(type) ? doors / 2 - 1 : 0)
                + (type === 'inner-left' ? 1 : 0), 0),
            L19: () => openings.reduce((acc, { type, doors }) => acc + (type === 'left' ? doors : 0)
                + (type === 'right' ? 1 : 0)
                + (type === 'center' ? doors / 2 : 0)
                + (type === 'inner-left' ? 2 : 0)
                + (type === 'inner-right' ? 1 : 0), 0),
            L20: () => openings.reduce((acc, { type }) => acc + (type === 'center' ? 1 : 0)
                + (INNER_TYPES.includes(type) ? 1 : 0), 0),
            L21: () => openings.reduce((acc, { type, doors }) => acc + (type === 'center' ? doors - 2 : 0)
                + (LEFT_RIGHT.includes(type) ? doors - 1 : 0)
                + (INNER_TYPES.includes(type) ? 1 : 0), 0),
            L22: () => openings.reduce((acc, { type, doors }) => acc + (type === 'center' ? doors - 4 : 0)
                + (LEFT_RIGHT.includes(type) ? doors - 2 : 0), 0),
            L26: () => openings.reduce((acc, { doors }) => acc + doors * 2, 0),
        }

        return quantityMap[vendor_code as string] ? quantityMap[vendor_code as string]() : 0
    }

    const calculate = () => {
        items.value.forEach(item => {
            const quantity = calculateQuantity(item)
            cartItems.value[item.id] = { quantity }
        })
    }

    const total_price = computed(() => {
        let totalPrice = 0
        const allItems = [...items.value, ...additional_items.value]

        allItems.forEach(item => {
            const quantity = cartItems.value[item.id]?.quantity || 0
            if (quantity > 0) {
                totalPrice += item.retail_price * quantity
            }
        })

        return totalPrice
    })

    return { items, glasses, additional_items, base_url, cartItems, total_price, calculate, initiateCartItems }
})