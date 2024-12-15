import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import { useOpeningStore } from './openingsStore'
import { Item, CartItem } from '../lib/types'

export const useItemsStore = defineStore('itemsStore', () => {
    const openingsStore = useOpeningStore()

    const items = ref<Item[]>([])
    const additional_items = ref<Item[]>([])
    const base_url = ref('')
    const cartItems = ref<{ [key: number]: CartItem }>({})

    const LEFT_RIGHT = ['left', 'right']
    const INNER_TYPES = ['inner-left', 'inner-right']
    const CENTER_TYPES = ['center', 'inner-left', 'inner-right']
    
    const initiateCartItems = () => {
        items.value.forEach(item => {
            cartItems.value[item.id] = { quantity: 0 }
        })

        additional_items.value.forEach(item => {
            cartItems.value[item.id] = { quantity: 0 }
        })
    }

    const calculate = () => {
        items.value.forEach(item => {
            let quantity = 0

            switch (item.vendor_code) {
                case 'L6':
                    quantity = openingsStore.openings.length * 6
                    break
                case 'L8':
                    quantity = openingsStore.openings.reduce((acc, opening) => {
                        if (LEFT_RIGHT.includes(opening.type)) {
                            return acc + (opening.doors * 2 - 2)
                        } else if (opening.type === 'center') {
                            return acc + (opening.doors * 2 - 4)
                        } else if (INNER_TYPES.includes(opening.type)) {
                            return acc + (opening.doors * 2 - 4)
                        }
                        return acc
                    }, 0)
                    break
                case 'L9':
                    quantity = openingsStore.openings.reduce((acc, opening) => {
                        return acc + (CENTER_TYPES.includes(opening.type) ? 1 : 0)
                    }, 0)
                    break
                case 'L12':
                    quantity = openingsStore.openings.reduce((acc, opening) => {
                        return acc + (LEFT_RIGHT.includes(opening.type) ? opening.doors * 2 - 2 : 0)
                    }, 0)
                    break
            }
            
            cartItems.value[item.id] = { quantity: quantity }
        })
    }
    
    const total_price = computed(() => {
        let totalPrice = 0;
    
        Object.keys(cartItems.value).forEach((key) => {
            const item = items.value.find((i) => i.id === Number(key)) || additional_items.value.find((i) => i.id === Number(key));
            if (item && cartItems.value[+key].quantity > 0) {
                totalPrice += item.retail_price * cartItems.value[+key].quantity;
            }
        });
    
        return totalPrice;
    });

    return { items, additional_items, base_url, cartItems, total_price, calculate, initiateCartItems }
})
