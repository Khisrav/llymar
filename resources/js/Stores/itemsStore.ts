import { defineStore } from 'pinia'
import { computed, ref, watch } from 'vue'
import { useOpeningStore } from './openingsStore'
import { Item, CartItem, User, Category, WholesaleFactor } from '../lib/types'
import { discountRate } from '../Utils/discountRate'

export const useItemsStore = defineStore('itemsStore', () => {
    const openingsStore = useOpeningStore()

    const items = ref<Item[]>([])
    const additional_items = ref<{ [key: number]: Item[] }>([])
    const glasses = ref<Item[]>([])
    const services = ref<Item[]>([])
    const cartItems = ref<{ [key: number]: CartItem }>({})
    const user = ref<User>({} as User)
    const categories = ref<Category[]>([])
    const wholesale_factor = ref<WholesaleFactor>()
    const markupPercentage = ref(0)

    const factor_groups = ref<WholesaleFactor[]>([])

    const selectedServicesID = ref<number[]>([])
    const selectedGlassID = ref(287)

    watch(selectedGlassID, (newSelectedGlassID) => {
        sessionStorage.setItem('selectedGlassID', JSON.stringify(newSelectedGlassID))
        updateGlassQuantity(newSelectedGlassID)
        calculate()
    }, { deep: true })

    watch(selectedServicesID, (newSelectedServicesID) => {
        sessionStorage.setItem('selectedServicesID', JSON.stringify(newSelectedServicesID))
        updateServicesQuantity(newSelectedServicesID)
        calculate()
    }, { deep: true })

    watch(cartItems, (newCartItems) => {
        Object.keys(newCartItems).forEach(key => {
            if (newCartItems[+key].quantity === 0) {
                delete newCartItems[+key]
            }
        })

        sessionStorage.setItem('cartItems', JSON.stringify(newCartItems))
    }, { deep: true })

    const LEFT_RIGHT = ['left', 'right']
    const INNER_TYPES = ['inner-left', 'inner-right']
    const CENTER_TYPE = ['center']

    const initiateCartItems = () => {
        if (sessionStorage.getItem('cartItems')) {
            const savedCartItems = JSON.parse(sessionStorage.getItem('cartItems') as string)
            // Ensure all existing cart items have the checked property
            Object.keys(savedCartItems).forEach(key => {
                if (savedCartItems[+key].checked === undefined) {
                    savedCartItems[+key].checked = true // Default to checked for existing items
                }
            })
            cartItems.value = savedCartItems
        }

        if (sessionStorage.getItem('selectedGlassID')) {
            selectedGlassID.value = JSON.parse(sessionStorage.getItem('selectedGlassID') as string)
        } else {
            if (glasses.value.length) {
                selectedGlassID.value = glasses.value[0].id
            }
        }

        if (sessionStorage.getItem('selectedServicesID')) {
            selectedServicesID.value = JSON.parse(sessionStorage.getItem('selectedServicesID') as string)
        } else { selectedServicesID.value = [] }

        if (!sessionStorage.getItem('cartItems')) {
            // const allItems = [...items.value, ...additional_items.value]
            const allItems = [...items.value]

            Object.keys(additional_items.value).forEach(key => {
                allItems.push(...additional_items.value[+key])
            })

            allItems.forEach(item => {
                cartItems.value[item.id] = { quantity: 0, checked: true }
            })
        }
    }

    const getItemQuantity = (vendorCode: string): number => {
        const item = items.value.find(i => i.vendor_code === vendorCode)
        return cartItems.value[item?.id as number]?.quantity || 0
    }

    const L1_L3_multiplier = (vendor_code: 'L1' | 'L3', doors: number, type: string): number => {
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
        }

        return multipliers[type]?.[vendor_code]?.[doors] ?? 0
    }

    const calculateQuantity = (item: Item): number => {
        const { vendor_code } = item
        const openings = openingsStore.openings

        const quantityMap: { [key: string]: () => number } = {
            L1: () => {
                const res = openings.reduce(
                    (acc, { width, type, doors }) => {
                        // If adding the opening does not exceed 6000, include it in the current group.
                        if (acc.group + width <= 6000) {
                            // For a new group, record type and doors.
                            if (acc.group === 0) {
                                acc.groupType = type;
                                acc.groupDoors = doors;
                            }
                            acc.group += width;
                        } else {
                            // Finalize the current group: convert total width to a rounded value and multiply.
                            acc.total += Math.ceil(acc.group / 1000 / 3) * 3 * L1_L3_multiplier("L1", acc.groupDoors, acc.groupType);
                            // Start a new group with the current opening.
                            acc.group = width;
                            acc.groupType = type;
                            acc.groupDoors = doors;
                        }
                        return acc;
                    },
                    { total: 0, group: 0, groupType: "", groupDoors: 0 }
                );
                // Finalize any remaining group.
                return res.total + (res.group ? Math.ceil(res.group / 1000 / 3) * 3 * L1_L3_multiplier("L1", res.groupDoors, res.groupType) : 0);
            },
            L2: () => getItemQuantity("L1"),
            L3: () => {
                const res = openings.reduce(
                    (acc, { width, type, doors }) => {
                        if (acc.group + width <= 6000) {
                            if (acc.group === 0) {
                                acc.groupType = type;
                                acc.groupDoors = doors;
                            }
                            acc.group += width;
                        } else {
                            acc.total += Math.ceil(acc.group / 1000 / 3) * 3 * L1_L3_multiplier("L3", acc.groupDoors, acc.groupType);
                            acc.group = width;
                            acc.groupType = type;
                            acc.groupDoors = doors;
                        }
                        return acc;
                    },
                    { total: 0, group: 0, groupType: "", groupDoors: 0 }
                );
                return res.total + (res.group ? Math.ceil(res.group / 1000 / 3) * 3 * L1_L3_multiplier("L3", res.groupDoors, res.groupType) : 0);
            },
            L4: () => getItemQuantity("L3"),


            L5: () => Math.floor((openings.reduce((acc, { width, type }) => {
                if (type === 'triangle' || type === 'blind-glazing') return acc
                return acc + Math.floor((width - 1800) / 1000 + 3)
            }, 0))),
            // (ширина - 1800) / 1000 + 3
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
            L501: () => (getItemQuantity('L15') + getItemQuantity('L16') + getItemQuantity('L17') + getItemQuantity('L18') + getItemQuantity('L19') + getItemQuantity('L20')) * 3 + 2,
            L502: () => (getItemQuantity('L1') / 3 * 8 + 2) + (getItemQuantity('L3') / 3 * 4 + 2),
            L503: () => getItemQuantity('L26') * 2 + 2 + getItemQuantity('L21') + 2
        }

        return quantityMap[vendor_code as string] ? quantityMap[vendor_code as string]() : 0
    }

    const updateGlassQuantity = (glassID: number) => {
        // Store existing checked states before deletion
        const existingCheckedStates: { [key: number]: boolean } = {}
        glasses.value.forEach(glass => {
            if (cartItems.value[glass.id]) {
                existingCheckedStates[glass.id] = cartItems.value[glass.id].checked ?? true
            }
        })

        glasses.value.forEach(glass => {
            delete cartItems.value[glass.id]
        })

        if (glassID == -1) return

        const existingCheckedState = existingCheckedStates[glassID] ?? true // Use stored state or default to true
        cartItems.value[glassID] = {
            quantity: parseFloat((openingsStore.openings.reduce((acc, { width, height }) => {
                return acc + width * height
            }, 0) / 1000000).toFixed(2)),
            checked: existingCheckedState
        }
    }

    const updateServicesQuantity = (servicesID: number[]) => {
        // Store existing checked states before deletion
        const existingCheckedStates: { [key: number]: boolean } = {}
        services.value.forEach(service => {
            if (cartItems.value[service.id]) {
                existingCheckedStates[service.id] = cartItems.value[service.id].checked ?? true
            }
        })

        services.value.forEach(service => {
            delete cartItems.value[service.id]
        })

        servicesID.forEach(serviceID => {
            const existingCheckedState = existingCheckedStates[serviceID] ?? true
            //388 это распил
            if ([388].includes(serviceID)) {
                // console.log({ quantity: openingsStore.openings.reduce((acc, { doors }) => acc + doors, 0) })
                cartItems.value[serviceID] = { quantity: openingsStore.openings.reduce((acc, { doors }) => acc + doors, 0), checked: existingCheckedState }
            }
            else if (serviceID == 386) {
                let q = 0
                const m_p_ = [391, 393, 394, 395, 396, 397];
                // const m_p_ = [390, 391, 392, 393, 394, 395, 396, 397, 363, 425, 426];
                m_p_.forEach(mp => {
                    if ([396, 397].includes(mp)) q += (cartItems.value[mp]?.quantity || 0) * 3
                    else q += cartItems.value[mp]?.quantity || 0
                })
                // q = getItemQuantity('L1') + getItemQuantity('L3')
                cartItems.value[serviceID] = { quantity: q, checked: existingCheckedState }
            }
            //387 & 389 это монтаж и изготовление створок соответственно
            else if ([387, 389].includes(serviceID)) {
                cartItems.value[serviceID] = {
                    quantity: openingsStore.openings.reduce((acc, { width, height }) => {
                        return acc + width * height
                    }, 0) / 1000000,
                    checked: existingCheckedState
                }
            }
            else if ([435].includes(serviceID)) {
                let q = getItemQuantity('L1') + getItemQuantity('L3')
                cartItems.value[serviceID] = { quantity: q, checked: existingCheckedState }
            }
        })
    }

    const calculate = () => {
        items.value.forEach(item => {
            const quantity = calculateQuantity(item)
            const existingItem = cartItems.value[item.id]
            const checked = existingItem?.checked !== false // preserve existing checked state, default to true
            cartItems.value[item.id] = { quantity, checked }
        })

        updateGlassQuantity(selectedGlassID.value)
        updateServicesQuantity(selectedServicesID.value)
    }

    const itemPrice = (item_id: number): number => {
        const item = getItemInfo(item_id)

        if (!item) return 0;

        const category = categories.value.find(c => c.id === item?.category_id)
        const ku = category?.reduction_factors?.find(ku => ku.key === wholesale_factor.value?.reduction_factor_key)

        return item.purchase_price * ((ku?.value as number) || 1) * (wholesale_factor.value?.value || 1);
    }

    const total_price = computed(() => {
        let totalPriceWithoutDiscount = 0, totalPriceWithDiscount = 0
        // const allItems = [...items.value, ...additional_items.value, ...glasses.value, ...services.value]
        const allItems = [...items.value, ...glasses.value, ...services.value]

        Object.keys(additional_items.value).forEach(key => {
            allItems.push(...additional_items.value[+key])
        })

        allItems.forEach(item => {
            const cartItem = cartItems.value[item.id]
            const quantity = cartItem?.quantity || 0
            const isChecked = cartItem?.checked !== false // default to true if undefined

            if (!quantity || !isChecked) return

            totalPriceWithoutDiscount += 0
            totalPriceWithDiscount += itemPrice(item.id) * quantity
        })

        return {
            without_discount: totalPriceWithoutDiscount,
            with_discount: totalPriceWithDiscount
        }
    })

    const getItemInfo = (id: number) => {
        // const allItems = [...items.value, ...additional_items.value, ...glasses.value, ...services.value]
        const allItems = [...items.value, ...glasses.value, ...services.value]

        Object.keys(additional_items.value).forEach(key => {
            allItems.push(...additional_items.value[+key])
        })

        const item = allItems.find(i => i.id === id)
        return item
    }

    // const resetItem = (itemId: number) => {
    //     if (cartItems.value[itemId]) {
    //         cartItems.value[itemId].quantity = 0
    //     }
    // }

    const toggleItemChecked = (itemId: number) => {
        // Initialize item if it doesn't exist
        if (!cartItems.value[itemId]) {
            // Check if this is a calculated item or manually added item
            const item = items.value.find(i => i.id === itemId)
            const initialQuantity = item ? calculateQuantity(item) : 0
            cartItems.value[itemId] = { quantity: initialQuantity, checked: true }
        }

        const currentChecked = cartItems.value[itemId].checked ?? true
        const newCheckedState = !currentChecked
        cartItems.value[itemId].checked = newCheckedState
        
        // Don't modify quantity when toggling checked state
        // The quantity should remain as is for user flexibility
    }

    return {
        items,
        glasses,
        services,
        selectedGlassID,
        selectedServicesID,
        updateServicesQuantity,
        additional_items,
        cartItems,
        total_price,
        calculate,
        initiateCartItems,
        getItemInfo,
        toggleItemChecked,
        user,
        categories,
        wholesale_factor,
        itemPrice,
        markupPercentage,
        factor_groups,
    }
})