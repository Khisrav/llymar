import { defineStore } from 'pinia'
import { computed, ref, watch } from 'vue'
import { useOpeningStore } from './openingsStore'
import { Item, CartItem, User, Category } from '../lib/types'
import { discountRate } from '../Utils/discountRate'
import { parseQuantity } from '../Utils/quantityFormatter'

export const useItemsStore = defineStore('itemsStore', () => {
    const openingsStore = useOpeningStore()

    const items = ref<Item[]>([])
    const additional_items = ref<{ [key: number]: Item[] }>([])
    const glasses = ref<Item[]>([])
    const services = ref<Item[]>([])
    const cartItems = ref<{ [key: number]: CartItem }>({})
    const user = ref<User>({} as User)
    const categories = ref<Category[]>([])
    const markupPercentage = ref(0)
    const selectedFactor = ref('kz')
    const userDefaultFactor = ref('kz')

    const selectedServicesID = ref<number[]>([])
    const selectedGlassID = ref(287)
    
    watch(markupPercentage, (newMarkupPercentage) => {
        if (newMarkupPercentage === undefined) markupPercentage.value = 0
        else markupPercentage.value = parseFloat(newMarkupPercentage.toFixed(4))
    })

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
        // Clean up invalid items
        Object.keys(newCartItems).forEach(key => {
            const itemId = +key
            const item = newCartItems[itemId]
            
            // Remove invalid item IDs or invalid cart items
            if (!itemId || itemId <= 0 || 
                !item || 
                typeof item !== 'object' || 
                typeof item.quantity !== 'number' || 
                item.quantity <= 0) {
                console.warn(`Removing invalid cart item: ID=${key}`, item)
                delete newCartItems[itemId]
            }
        })

        // Only save valid cart items to session storage
        const validCartItems: { [key: number]: CartItem } = {}
        Object.keys(newCartItems).forEach(key => {
            const itemId = +key
            const item = newCartItems[itemId]
            if (itemId > 0 && item && typeof item === 'object' && item.quantity > 0) {
                validCartItems[itemId] = item
            }
        })

        sessionStorage.setItem('cartItems', JSON.stringify(validCartItems))
    }, { deep: true })

    // Watch for factor changes and recalculate totals
    watch(selectedFactor, () => {
        // No need to recalculate individual item quantities since factor only affects pricing
        // The total_price computed will automatically recalculate when selectedFactor changes
    })

    const LEFT_RIGHT = ['left', 'right']
    const INNER_TYPES = ['inner-left', 'inner-right']
    const CENTER_TYPE = ['center']

    const initializeUserFactor = (defaultFactor: string) => {
        userDefaultFactor.value = defaultFactor
        // Use saved factor from session or fallback to user's default factor
        const savedFactor = sessionStorage.getItem('selectedFactor')
        selectedFactor.value = savedFactor || defaultFactor
    }

    const initiateCartItems = () => {
        cartItems.value = {}
    
        if (sessionStorage.getItem('cartItems')) {
            const savedCartItems = JSON.parse(sessionStorage.getItem('cartItems') as string)
            // Clean up and validate saved cart items
            const cleanedCartItems: { [key: number]: CartItem } = {}
            
            Object.keys(savedCartItems).forEach(key => {
                const itemId = +key
                const item = savedCartItems[itemId]
                
                // Skip invalid item IDs (must be positive numbers)
                if (!itemId || itemId <= 0) {
                    console.warn(`Skipping invalid item ID: ${key}`)
                    return
                }
                
                // Check if the cart item exists and is valid
                if (item && typeof item === 'object' && typeof item.quantity === 'number') {
                    // Only keep items with positive quantities
                    if (item.quantity > 0) {
                        cleanedCartItems[itemId] = {
                            quantity: item.quantity,
                            checked: item.checked !== false // Default to true if undefined
                        }
                    }
                } else {
                    console.warn(`Skipping invalid cart item for ID ${itemId}:`, item)
                }
            })
            
            cartItems.value = cleanedCartItems
        }

        if (sessionStorage.getItem('selectedGlassID')) {
            selectedGlassID.value = JSON.parse(sessionStorage.getItem('selectedGlassID') as string)
        } else {
            if (glasses.value.length) {
                selectedGlassID.value = glasses.value[0].id as number
            }
        }

        if (sessionStorage.getItem('selectedServicesID')) {
            selectedServicesID.value = JSON.parse(sessionStorage.getItem('selectedServicesID') as string)
        } else { selectedServicesID.value = [] }

        if (!sessionStorage.getItem('cartItems')) {
            const allItems = [...items.value]

            Object.keys(additional_items.value).forEach(key => {
                allItems.push(...additional_items.value[+key])
            })

            // Only initialize items with valid IDs
            allItems.forEach(item => {
                if (item.id && item.id > 0) {
                    cartItems.value[item.id as number] = { quantity: 0, checked: true }
                }
            })
        }

        // Clean up any invalid items that might have been loaded
        cleanupCartItems()
    }

    // Utility function to clean up invalid cart items
    const cleanupCartItems = () => {
        const itemsToRemove: number[] = []
        
        Object.keys(cartItems.value).forEach(key => {
            const itemId = +key
            const item = cartItems.value[itemId]
            
            if (!itemId || itemId <= 0 || 
                !item || 
                typeof item !== 'object' || 
                typeof item.quantity !== 'number' || 
                item.quantity < 0) {
                itemsToRemove.push(itemId)
            }
        })
        
        itemsToRemove.forEach(itemId => {
            console.warn(`Removing invalid cart item with ID: ${itemId}`)
            delete cartItems.value[itemId]
        })
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
                        if (acc.group + width <= (width <= 3000 ? 3000 : 6000)) {
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
                        if (acc.group + width <= (width <= 3000 ? 3000 : 6000)) {
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
            if (glass.id && cartItems.value[glass.id as number]) {
                existingCheckedStates[glass.id as number] = cartItems.value[glass.id as number].checked ?? true
            }
        })

        // Remove all glass items from cart
        glasses.value.forEach(glass => {
            if (glass.id) {
                delete cartItems.value[glass.id as number]
            }
        })

        // Only add valid glass items
        if (glassID <= 0) return

        const quantity = parseQuantity((openingsStore.openings.reduce((acc, { width, height }) => {
            return acc + width * height
        }, 0) / 1000000))

        // Only add if quantity is positive
        if (quantity > 0) {
            const existingCheckedState = existingCheckedStates[glassID] ?? true
            cartItems.value[glassID] = {
                quantity: quantity,
                checked: existingCheckedState
            }
        }
    }

    const updateServicesQuantity = (servicesID: number[]) => {
        // Store existing checked states before deletion
        const existingCheckedStates: { [key: number]: boolean } = {}
        services.value.forEach(service => {
            if (service.id && cartItems.value[service.id as number]) {
                existingCheckedStates[service.id as number] = cartItems.value[service.id as number].checked ?? true
            }
        })

        // Remove all service items from cart
        services.value.forEach(service => {
            if (service.id) {
                delete cartItems.value[service.id as number]
            }
        })

        servicesID.forEach(serviceID => {
            // Skip invalid service IDs
            if (!serviceID || serviceID <= 0) return

            const existingCheckedState = existingCheckedStates[serviceID] ?? true
            let quantity = 0

            //388 это распил
            if ([388].includes(serviceID)) {
                quantity = openingsStore.openings.reduce((acc, { doors }) => acc + doors, 0)
            }
            else if (serviceID == 386) {
                const m_p_ = [391, 393, 394, 395, 396, 397]
                m_p_.forEach(mp => {
                    if ([396, 397].includes(mp)) quantity += (cartItems.value[mp]?.quantity || 0) * 3
                    else quantity += cartItems.value[mp]?.quantity || 0
                })
                additional_items.value[30]?.forEach(item => {
                    if (item.id == 363) quantity += cartItems.value[item.id as number]?.quantity * 2 || 0
                    else if (item.id == 425) quantity += cartItems.value[item.id as number]?.quantity || 0
                })
            }
            //387 & 389 это монтаж и изготовление створок соответственно
            else if ([387, 389].includes(serviceID)) {
                quantity = parseQuantity(openingsStore.openings.reduce((acc, { width, height }) => {
                    return acc + width * height
                }, 0) / 1000000)
            }
            else if ([435].includes(serviceID)) {
                quantity = getItemQuantity('L1') + getItemQuantity('L3')
            }

            // Only add if quantity is positive
            if (quantity > 0) {
                cartItems.value[serviceID] = { quantity, checked: existingCheckedState }
            }
        })
    }

    const calculate = () => {
        items.value.forEach(item => {
            const quantity = parseQuantity(calculateQuantity(item))
            const existingItem = cartItems.value[item.id as number]
            const checked = existingItem?.checked !== false // preserve existing checked state, default to true
            cartItems.value[item.id as number] = { quantity, checked }
        })

        updateGlassQuantity(selectedGlassID.value)
        updateServicesQuantity(selectedServicesID.value)
    }

    const itemPrice = (item_id: number): number => {
        const item = getItemInfo(item_id)

        if (!item) return 0;

        // Apply the selected factor to the purchase price
        const factor = selectedFactor.value.toLowerCase()
        let multiplier = 1.0

        switch (factor) {
            case 'k1':
                multiplier = (item as any).k1 ?? 1.0
                break
            case 'k2':
                multiplier = (item as any).k2 ?? 1.0
                break
            case 'k3':
                multiplier = (item as any).k3 ?? 1.0
                break
            case 'k4':
                multiplier = (item as any).k4 ?? 1.0
                break
            case 'kz':
            default:
                multiplier = (item as any).kz ?? 1.0
                break
        }

        return item.purchase_price * multiplier;
    }

    const total_price = computed(() => {
        let totalPriceWithoutDiscount = 0, totalPriceWithDiscount = 0
        // const allItems = [...items.value, ...additional_items.value, ...glasses.value, ...services.value]
        const allItems = [...items.value, ...glasses.value, ...services.value]

        Object.keys(additional_items.value).forEach(key => {
            allItems.push(...additional_items.value[+key])
        })

        allItems.forEach(item => {
            const cartItem = cartItems.value[item.id as number]
            const quantity = cartItem?.quantity || 0
            const isChecked = cartItem?.checked !== false // default to true if undefined

            if (!quantity || !isChecked) return

            totalPriceWithoutDiscount += 0
            totalPriceWithDiscount += itemPrice(item.id as number) * quantity
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
        initializeUserFactor,
        getItemInfo,
        toggleItemChecked,
        cleanupCartItems,
        user,
        categories,
        itemPrice,
        markupPercentage,
        selectedFactor,
        userDefaultFactor,
    }
})