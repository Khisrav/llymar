import { defineStore } from 'pinia';
import { ref } from 'vue';
import { useOpeningStore } from './openingsStore';
import { Item, CartItem } from '../lib/types'; // Assuming you have a types file with the Item interface

export const useItemsStore = defineStore('itemsStore', () => {
    const openingsStore = useOpeningStore();

    const items = ref<Item[]>([]);
    const base_url = ref('');
    const cartItems = ref<CartItem[]>([]);
    const total_price = ref(0);

    const calculate = () => {
        items.value.forEach(item => {
            switch (item.vendor_code) {
                case '12':
                    cartItems.value.push({
                        vendor_code: item.vendor_code,
                        quantity: openingsStore.openings.reduce((acc, opening) => {
                            return acc + (opening.type === 'left' || opening.type === 'right' ? opening.doors * 2 - 2 : 0);
                        }, 0)
                    });
                    break;
            }
        });
    };

    return {
        items,
        cartItems,
        base_url,
        total_price,
        calculate
    };
});
