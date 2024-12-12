import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useOpeningStore } from './openingsStore'
import { Item, CartItem } from '../lib/types'

export const useItemsStore = defineStore('itemsStore', () => {
    const openingsStore = useOpeningStore()

    const items = ref<Item[]>([])
    const base_url = ref('')
    const cartItems = ref<{ [key: string]: CartItem }>({})
    const total_price = ref(0)

    const calculate = () => {
        console.log('calculate() executed')
        cartItems.value = {} 
        total_price.value = 0

        items.value.forEach(item => {
            switch (item.vendor_code) {
                case 'L6':
                    cartItems.value[item.vendor_code] = {
                        quantity: openingsStore.openings.length * 2,
                    }
                    total_price.value += (openingsStore.openings.length * 2) * item.retail_price
                    break
                case 'L12':
                    const quantity = openingsStore.openings.reduce((acc, opening) => {
                        return acc + (opening.type === 'left' || opening.type === 'right' ? opening.doors * 2 - 2 : 0)
                    }, 0)
                    cartItems.value[item.vendor_code] = {
                        quantity: quantity,
                    }
                    total_price.value += quantity * item.retail_price
                    break
            }
        })
    }

    return {
        items,
        cartItems,
        base_url,
        total_price,
        calculate,
    }
})
