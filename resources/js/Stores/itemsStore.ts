import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useOpeningStore } from './openingsStore'
import { Item, CartItem } from '../lib/types'

export const useItemsStore = defineStore('itemsStore', () => {
    const openingsStore = useOpeningStore()

    const items = ref<Item[]>([])
    const additional_items = ref<Item[]>([])
    const base_url = ref('')
    const cartItems = ref<{ [key: number]: CartItem }>({})
    const total_price = ref(0)

    const calculate = () => {
        console.log('calculate() executed')
        cartItems.value = []
        total_price.value = 0

        items.value.forEach(item => {
            let quantity = 0
            
            switch (item.vendor_code) {
                case 'L6':
                    quantity = openingsStore.openings.length * 6
                    cartItems.value[item.id] = {
                        quantity: quantity,
                    }
                    total_price.value += quantity * item.retail_price
                    break
                case 'L8':
                    quantity = openingsStore.openings.reduce((acc, opening) => acc + (['left', 'right'].includes(opening.type) ? opening.doors * 2 - 2 : 0), 0)
                        + openingsStore.openings.reduce((acc, opening) => acc + (opening.type === 'center' ? opening.doors * 2 - 4 : 0), 0)
                        + openingsStore.openings.reduce((acc, opening) => acc + (['inner-left', 'inner-right'].includes(opening.type) ? opening.doors * 2 - 4 : 0), 0)
                    break
                case 'L9':
                    quantity = openingsStore.openings.reduce((acc, opening) => acc + (['center', 'inner-left', 'inner-right'].includes(opening.type) ? 1 : 0), 0)
                    break
                case 'L12':
                    quantity = openingsStore.openings.reduce((acc, opening) => {
                        return acc + (opening.type === 'left' || opening.type === 'right' ? opening.doors * 2 - 2 : 0)
                    }, 0)
                    break
            }
            
            cartItems.value[item.id] = { quantity: quantity }
            total_price.value += quantity * item.retail_price
        })
    }

    return { items, additional_items, base_url, cartItems, total_price, calculate }
})
