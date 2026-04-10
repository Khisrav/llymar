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
    const ghost_handles = ref<Item[]>([])
    const glasses = ref<Item[]>([])
    const services = ref<Item[]>([])
    const cartItems = ref<{ [key: number]: CartItem }>({})
    const manualOverrides = ref<{ [key: number]: number }>({})
    const user = ref<User>({} as User)
    const categories = ref<Category[]>([])
    const markupPercentage = ref(0)
    const selectedFactor = ref('pz')
    const userDefaultFactor = ref('pz')

    const selectedServicesID = ref<number[]>([])
    const selectedGlassID = ref(287)
    const selectedGhostHandlesID = ref<number[]>([])
    const selectedGhostGlassesID = ref<number[]>([])
    
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

    watch(selectedGhostHandlesID, (newSelectedGhostHandlesID) => {
        sessionStorage.setItem('selectedGhostHandlesID', JSON.stringify(newSelectedGhostHandlesID))
        updateGhostHandlesQuantity(newSelectedGhostHandlesID)
        calculate()
    }, { deep: true })

    watch(selectedGhostGlassesID, (newSelectedGhostGlassesID) => {
        sessionStorage.setItem('selectedGhostGlassesID', JSON.stringify(newSelectedGhostGlassesID))
        updateGhostGlassesQuantity(newSelectedGhostGlassesID)
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

    const persistManualOverrides = () => {
        sessionStorage.setItem('manualOverrides', JSON.stringify(manualOverrides.value))
    }

    const initiateCartItems = () => {
        if (sessionStorage.getItem('manualOverrides')) {
            try {
                manualOverrides.value = JSON.parse(sessionStorage.getItem('manualOverrides') as string)
            } catch {
                manualOverrides.value = {}
            }
        } else {
            manualOverrides.value = {}
        }

        const rawCart = sessionStorage.getItem('cartItems')
        let cleanedCartItems: { [key: number]: CartItem } = {}

        if (rawCart) {
            try {
                const savedCartItems = JSON.parse(rawCart)
                Object.keys(savedCartItems).forEach(key => {
                    const itemId = +key
                    const item = savedCartItems[itemId]

                    if (!itemId || itemId <= 0) {
                        console.warn(`Skipping invalid item ID: ${key}`)
                        return
                    }

                    if (item && typeof item === 'object' && typeof item.quantity === 'number') {
                        if (item.quantity > 0) {
                            cleanedCartItems[itemId] = {
                                quantity: item.quantity,
                                checked: item.checked !== false
                            }
                        }
                    } else {
                        console.warn(`Skipping invalid cart item for ID ${itemId}:`, item)
                    }
                })
            } catch {
                cleanedCartItems = {}
            }
        }

        if (Object.keys(cleanedCartItems).length > 0) {
            cartItems.value = cleanedCartItems
        } else {
            cartItems.value = {}
            if (!rawCart) {
                const allItems = [...items.value]

                Object.keys(additional_items.value).forEach(key => {
                    allItems.push(...additional_items.value[+key])
                })

                allItems.forEach(item => {
                    if (item.id && item.id > 0) {
                        cartItems.value[item.id as number] = { quantity: 0, checked: true }
                    }
                })
            }
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

        if (sessionStorage.getItem('selectedGhostHandlesID')) {
            selectedGhostHandlesID.value = JSON.parse(sessionStorage.getItem('selectedGhostHandlesID') as string)
        } else { selectedGhostHandlesID.value = [] }

        if (sessionStorage.getItem('selectedGhostGlassesID')) {
            selectedGhostGlassesID.value = JSON.parse(sessionStorage.getItem('selectedGhostGlassesID') as string)
        } else { selectedGhostGlassesID.value = [] }

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
        const id = item?.id as number | undefined
        if (id != null && id in manualOverrides.value) {
            return parseQuantity(manualOverrides.value[id])
        }
        return cartItems.value[id as number]?.quantity || 0
    }

    const L1_L3_multiplier = (vendor_code: 'L1' | 'L3' | 'L4.1', doors: number, type: string): number => {
        const multipliers: Record<string, Record<string, Record<number, number>>> = {
            left: {
                L1:     { 2: 0, 3: 1, 4: 0, 5: 1, 6: 0, 7: 1, 8: 0, 9: 1, 10: 0, 11: 1, 12: 0 },
                L3:     { 2: 1, 3: 0, 4: 0, 5: 1, 6: 1, 7: 0, 8: 0, 9: 1, 10: 1, 11: 0, 12: 0 },
                "L4.1": { 2: 0, 3: 0, 4: 1, 5: 0, 6: 1, 7: 1, 8: 2, 9: 1, 10: 2, 11: 2, 12: 3 },
            },
            right: {
                L1:     { 2: 0, 3: 1, 4: 0, 5: 1, 6: 0, 7: 1, 8: 0, 9: 1, 10: 0, 11: 1, 12: 0 },
                L3:     { 2: 1, 3: 0, 4: 0, 5: 1, 6: 1, 7: 0, 8: 0, 9: 1, 10: 1, 11: 0, 12: 0 },
                "L4.1": { 2: 0, 3: 0, 4: 1, 5: 0, 6: 1, 7: 1, 8: 2, 9: 1, 10: 2, 11: 2, 12: 3 },
            },
            center: {
                L1:     { 4: 0, 6: 1, 8: 0, 10: 1, 12: 0, 14: 1, 16: 0, 18: 1, 20: 0 },
                L3:     { 4: 1, 6: 0, 8: 2, 10: 1, 12: 1, 14: 0, 16: 0, 18: 1, 20: 1 },
                "L4.1": { 4: 0, 6: 0, 8: 1, 10: 0, 12: 1, 14: 1, 16: 2, 18: 1, 20: 2 },
            },
            'inner-left': {
                L1:     { 3: 0, 4: 1, 5: 0, 6: 1, 7: 0, 8: 1, 9: 0, 10: 1, 11: 0 },
                L3:     { 3: 1, 4: 0, 5: 0, 6: 1, 7: 1, 8: 0, 9: 0, 10: 1, 11: 1 },
                "L4.1": { 3: 0, 4: 0, 5: 1, 6: 0, 7: 1, 8: 1, 9: 2, 10: 1, 11: 2 },
            },
            'inner-right': {
                L1:     { 3: 0, 4: 1, 5: 0, 6: 1, 7: 0, 8: 1, 9: 0, 10: 1, 11: 0 },
                L3:     { 3: 1, 4: 0, 5: 0, 6: 1, 7: 1, 8: 0, 9: 0, 10: 1, 11: 1 },
                "L4.1": { 3: 0, 4: 0, 5: 1, 6: 0, 7: 1, 8: 1, 9: 2, 10: 1, 11: 2 },
            },
        }

        return multipliers[type]?.[vendor_code]?.[doors] ?? 0
    }

    const L1_to_L4_1_factor = (width: number): number => {
        let qnt = 0

        while (width > 0) {
            if (width >= 7000) {
                width -= 7000
                qnt += 7
            }
            else if (width >= 6001 && width < 7000) {
                width -= width
                qnt += 7
            }
            else if (width >= 6000) {
                width -= 6000
                qnt += 6
            }
            else if (width >= 3501 && width < 6000) {
                width -= width
                qnt += 6
            }
            else if (width >= 3500) {
                width -= 3500
                qnt += 3.5
            }
            else if (width >= 3001 && width < 3500) {
                width -= width
                qnt += 3.5
            }
            else {
                width -= 3000
                qnt += 3
            }
        }

        return qnt
    }

    //for L1, L3, L4.1
    const isWeirdVendorCodeActive = (doors: number, type: string, vendor_code: string): boolean => {
        const map: Record<number, Record<string, string[]>> = {
            2: { left: ["L3"], right: ["L3"], center: [], 'inner-left': [], 'inner-right': [] },
            3: { left: ["L1"], right: ["L1"], center: [], 'inner-left': ["L3"], 'inner-right': ["L3"] },
            4: { left: ["L4.1"], right: ["L4.1"], center: ["L3"], 'inner-left': ["L1"], 'inner-right': ["L1"] },
            5: { left: ["L3", "L1"], right: ["L3", "L1"], center: [], 'inner-left': ["L4.1"], 'inner-right': ["L4.1"] },
            6: { left: ["L3", "L4.1"], right: ["L3", "L4.1"], center: ["L1"], 'inner-left': ["L3", "L1"], 'inner-right': ["L3", "L1"] },
            7: { left: ["L1", "L4.1"], right: ["L1", "L4.1"], center: [], 'inner-left': ["L3", "L4.1"], 'inner-right': ["L3", "L4.1"] },
            8: { left: ["L4.1", "L4.1"], right: ["L4.1", "L4.1"], center: ["L4.1"], 'inner-left': ["L1", "L4.1"], 'inner-right': ["L1", "L4.1"] },
            9: { left: ["L3", "L1", "L4.1"], right: ["L3", "L1", "L4.1"], center: [], 'inner-left': ["L4.1", "L4.1"], 'inner-right': ["L4.1", "L4.1"] },
            10: { left: ["L3", "L4.1", "L4.1"], right: ["L3", "L4.1", "L4.1"], center: ["L3", "L1"], 'inner-left': ["L3", "L1", "L4.1"], 'inner-right': ["L3", "L1", "L4.1"] },
            11: { left: ["L1", "L4.1", "L4.1"], right: ["L1", "L4.1", "L4.1"], center: [], 'inner-left': ["L3", "L4.1", "L4.1"], 'inner-right': ["L3", "L4.1", "L4.1"] },
            12: { left: ["L4.1", "L4.1", "L4.1"], right: ["L4.1", "L4.1", "L4.1"], center: ["L3", "L4.1"], 'inner-left': [], 'inner-right': [] },
            14: { left: [], right: [], center: ["L1", "L4.1"], 'inner-left': [], 'inner-right': [] },
            16: { left: [], right: [], center: ["L4.1", "L4.1"], 'inner-left': [], 'inner-right': [] },
            18: { left: [], right: [], center: ["L3", "L1", "L4.1"], 'inner-left': [], 'inner-right': [] },
            20: { left: [], right: [], center: ["L3", "L4.1", "L4.1"], 'inner-left': [], 'inner-right': [] },
        }
    
        return map[doors]?.[type]?.includes(vendor_code) ?? false
    }

    const calculateQuantity = (item: Item): number => {
        const { vendor_code } = item
        const openings = openingsStore.openings

        const quantityMap: { [key: string]: () => number } = {
            L1: () => {
                return openings.reduce((acc, { width, type, doors }) => {
                    if (!isWeirdVendorCodeActive(doors, type, 'L1')) return acc
                    return acc + Math.ceil(width / 1000 / 3) * 3 * L1_L3_multiplier("L1", doors, type)
                }, 0)
            },
            L2: () => getItemQuantity('L1'),
            L3: () => {
                return openings.reduce((acc, { width, type, doors }) => {
                    if (!isWeirdVendorCodeActive(doors, type, 'L3')) return acc
                    return acc + L1_to_L4_1_factor(width) * L1_L3_multiplier("L3", doors, type)
                }, 0)
            },
            L4: () => getItemQuantity('L3'),
            'L4.1': () => {
                return openings.reduce((acc, { width, type, doors }) => {
                    if (!isWeirdVendorCodeActive(doors, type, 'L4.1')) return acc
                    return acc + L1_to_L4_1_factor(width) * L1_L3_multiplier("L4.1", doors, type)
                }, 0)
            },
            'L4.2': () => getItemQuantity('L4.1'),
            L5: () => Math.floor((openings.reduce((acc, { width, type }) => {
                if (type === 'triangle' || type === 'blind-glazing') return acc
                return acc + Math.floor((width - 1800) / 1000 + 3)
            }, 0))),
            L6: () => {
                return openings.reduce((acc, { type }) => ['triangle', 'blind-glazing'].includes(type) ? acc : acc + 6, 0)
            },
            L8: () => openings.reduce((acc, { type, doors }) => {
                if (LEFT_RIGHT.includes(type)) return acc + (doors * 2 - 2)
                if (CENTER_TYPE.includes(type)) return acc + (doors * 2 - 4)
                if (INNER_TYPES.includes(type)) return acc + 2
                return acc
            }, 0),
            L9: () => openings.reduce((acc, { type }) => acc + ([...CENTER_TYPE, ...INNER_TYPES].includes(type) ? 1 : 0), 0),
            L12: () => getItemQuantity('L2') * 6 + getItemQuantity('L4') * 4 + (getItemQuantity('L4.2') !== 0 ? getItemQuantity('L4.2') : getItemQuantity('L4.1')) * 8,
            L13: () => getItemQuantity('L9') * 6 + getItemQuantity('L8') * 3 + getItemQuantity('L5') * 2,
            L14: () => getItemQuantity('L6') * 2 + getItemQuantity('L6.1') * 2,
            L15: () => openings.reduce((acc, { type, doors }) => acc + (type === 'right' ? doors - 1 : 0)
                + (CENTER_TYPE.includes(type) ? doors / 2 - 1 : 0)
                + (type === 'inner-right' ? doors - 2 : 0), 0),
            L16: () => openings.reduce((acc, { type, doors }) => acc + (CENTER_TYPE.includes(type) ? doors / 2 : 0)
                + (type === 'left' ? 1 : 0)
                + (type === 'right' ? doors : 0)
                + (type === 'inner-left' ? 1 : 0)
                + (type === 'inner-right' ? doors - 1 : 0), 0),
            L17: () => openings.reduce((acc, { type }) => acc + ([...CENTER_TYPE, ...INNER_TYPES].includes(type) ? 1 : 0), 0),
            L18: () => openings.reduce((acc, { type, doors }) => acc + (type === 'left' ? doors - 1 : 0)
                + (CENTER_TYPE.includes(type) ? doors / 2 - 1 : 0)
                + (type === 'inner-left' ? doors - 2 : 0), 0),
            L19: () => openings.reduce((acc, { type, doors }) => acc + (type === 'left' ? doors : 0)
                + (type === 'right' ? 1 : 0)
                + (type === 'center' ? doors / 2 : 0)
                + (type === 'inner-left' ? doors - 1 : 0)
                + (type === 'inner-right' ? 1 : 0), 0),
            L20: () => openings.reduce((acc, { type }) => acc + (type === 'center' ? 1 : 0)
                + (INNER_TYPES.includes(type) ? 1 : 0), 0),
            L21: () => openings.reduce((acc, { type, doors }) => acc + (type === 'center' ? doors - 2 : 0)
                + (LEFT_RIGHT.includes(type) ? doors - 1 : 0)
                + (INNER_TYPES.includes(type) ? doors - 2 : 0), 0),
            L22: () => openings.reduce((acc, { type, doors }) => acc + (type === 'center' ? doors - 4 : 0)
                + (LEFT_RIGHT.includes(type) ? doors - 2 : 0)
                + (INNER_TYPES.includes(type) ? doors - 3 : 0), 0),
            L26: () => openings.reduce((acc, { doors, type }) => acc + (type !== 'triangle' && type !== 'blind-glazing' ? doors * 2 : 0), 0),
            L501: () => openings.reduce((acc, { type }) => acc + (!['triangle', 'blind-glazing'].includes(type) ? (getItemQuantity('L15') + getItemQuantity('L16') + getItemQuantity('L17') + getItemQuantity('L18') + getItemQuantity('L19') + getItemQuantity('L20')) * 3 + 2 : 0), 0),
            L502: () => openings.reduce((acc, { type }) => acc + (!['triangle', 'blind-glazing'].includes(type) ? (getItemQuantity('L1') / 3 * 8 + 2) + (getItemQuantity('L3') / 3 * 4 + 2) + (getItemQuantity('L4.1') / 3 * 4 + 2) : 0), 0),
            L503: () => openings.reduce((acc, { type}) => acc + (!['triangle', 'blind-glazing'].includes(type) ? getItemQuantity('L26') * 2 + 2 + getItemQuantity('L21') + 2 : 0), 0)
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

    const updateGhostHandlesQuantity = (ghostHandlesID: number[]) => {
        // Store existing quantities before deletion
        const existingQuantities: { [key: number]: number } = {}
        ghost_handles.value.forEach(ghostHandle => {
            if (ghostHandle.id && cartItems.value[ghostHandle.id as number]) {
                existingQuantities[ghostHandle.id as number] = cartItems.value[ghostHandle.id as number].quantity
            }
        })

        // Remove all ghost handle items from cart
        ghost_handles.value.forEach(ghostHandle => {
            if (ghostHandle.id) {
                delete cartItems.value[ghostHandle.id as number]
            }
        })

        ghostHandlesID.forEach(ghostHandleID => {
            // Skip invalid ghost handle IDs
            if (!ghostHandleID || ghostHandleID <= 0) return

            // Ghost handles preserve existing quantity or default to 1, and are never checked (don't count towards price)
            const quantity = existingQuantities[ghostHandleID] || 1
            cartItems.value[ghostHandleID] = { quantity, checked: false }
        })
    }

    const updateGhostGlassesQuantity = (ghostGlassesID: number[]) => {
        // Store existing quantities before deletion
        const existingQuantities: { [key: number]: number } = {}
        
        // Track which glasses are currently ghost glasses
        const currentGhostGlasses = new Set(selectedGhostGlassesID.value)
        
        // Remove only items that were ghost glasses but are no longer selected
        glasses.value.forEach(glass => {
            if (glass.id && currentGhostGlasses.has(glass.id) && !ghostGlassesID.includes(glass.id)) {
                // Store existing quantity before deletion
                if (cartItems.value[glass.id as number]) {
                    existingQuantities[glass.id as number] = cartItems.value[glass.id as number].quantity
                }
                delete cartItems.value[glass.id as number]
            }
        })

        // Calculate glass quantity based on openings (same as regular glass)
        const glassQuantity = parseQuantity((openingsStore.openings.reduce((acc, { width, height }) => {
            return acc + width * height
        }, 0) / 1000000))

        ghostGlassesID.forEach(glassID => {
            // Skip invalid glass IDs or if it's the selected regular glass
            if (!glassID || glassID <= 0 || glassID === selectedGlassID.value) return

            // Ghost glasses use calculated quantity, and are never checked (don't count towards price)
            const quantity = glassQuantity > 0 ? glassQuantity : (existingQuantities[glassID] || 1)
            cartItems.value[glassID] = { quantity, checked: false }
        })
    }

    const calculate = () => {
        items.value.forEach(item => {
            const id = item.id as number
            const existingItem = cartItems.value[id]
            const checked = existingItem?.checked !== false
            const quantity =
                id in manualOverrides.value
                    ? parseQuantity(manualOverrides.value[id])
                    : parseQuantity(calculateQuantity(item))
            cartItems.value[id] = { quantity, checked }
        })

        updateGlassQuantity(selectedGlassID.value)
        updateServicesQuantity(selectedServicesID.value)
        updateGhostHandlesQuantity(selectedGhostHandlesID.value)
        updateGhostGlassesQuantity(selectedGhostGlassesID.value)
    }

    const setManualQuantity = (itemId: number, quantity: number) => {
        const q = parseQuantity(quantity)
        manualOverrides.value[itemId] = q
        persistManualOverrides()
        if (cartItems.value[itemId]) {
            cartItems.value[itemId].quantity = q
        } else {
            cartItems.value[itemId] = { quantity: q, checked: true }
        }
        calculate()
    }

    const clearManualOverride = (itemId: number) => {
        delete manualOverrides.value[itemId]
        persistManualOverrides()
        calculate()
    }

    const clearAllManualOverrides = () => {
        manualOverrides.value = {}
        sessionStorage.removeItem('manualOverrides')
    }

    const removeManualOverrideOnly = (itemId: number) => {
        delete manualOverrides.value[itemId]
        persistManualOverrides()
    }

    const itemPrice = (item_id: number): number => {
        const item = getItemInfo(item_id)

        if (!item) return 0;

        // Get the price directly from the selected factor (no multiplication needed)
        const factor = selectedFactor.value.toLowerCase()
        let price = 0.0

        switch (factor) {
            case 'p1':
                price = (item as any).p1 ?? 1.0
                break
            case 'p2':
                price = (item as any).p2 ?? 1.0
                break
            case 'p3':
                price = (item as any).p3 ?? 1.0
                break
            case 'p4':
                price = (item as any).pr ?? 1.0
                break
            case 'pz':
            default:
                price = (item as any).pz ?? 1.0
                break
        }

        return price;
    }

    const total_price = computed(() => {
        let totalPriceWithoutDiscount = 0, totalPriceWithDiscount = 0
        // const allItems = [...items.value, ...additional_items.value, ...glasses.value, ...services.value]
        const allItems = [...items.value, ...glasses.value, ...services.value, ...ghost_handles.value]

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
        const allItems = [...items.value, ...glasses.value, ...services.value, ...ghost_handles.value]

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
        // Check if this is a ghost handle or ghost glass - they should never be checked
        const isGhostHandle = ghost_handles.value.some(handle => handle.id === itemId)
        const isGhostGlass = selectedGhostGlassesID.value.includes(itemId)
        
        if (isGhostHandle || isGhostGlass) {
            if (cartItems.value[itemId]) {
                cartItems.value[itemId].checked = false
            }
            return
        }

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

    const addGhostHandle = (handleId: number) => {
        if (!selectedGhostHandlesID.value.includes(handleId)) {
            selectedGhostHandlesID.value.push(handleId)
        }
    }

    const removeGhostHandle = (handleId: number) => {
        selectedGhostHandlesID.value = selectedGhostHandlesID.value.filter(id => id !== handleId)
    }

    const addGhostGlass = (glassId: number) => {
        // Don't add if it's the selected regular glass
        if (glassId !== selectedGlassID.value && !selectedGhostGlassesID.value.includes(glassId)) {
            selectedGhostGlassesID.value.push(glassId)
        }
    }

    const removeGhostGlass = (glassId: number) => {
        selectedGhostGlassesID.value = selectedGhostGlassesID.value.filter(id => id !== glassId)
    }

    return {
        items,
        glasses,
        services,
        selectedGlassID,
        selectedServicesID,
        updateServicesQuantity,
        additional_items,
        ghost_handles,
        cartItems,
        manualOverrides,
        total_price,
        calculate,
        initiateCartItems,
        setManualQuantity,
        clearManualOverride,
        clearAllManualOverrides,
        removeManualOverrideOnly,
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
        selectedGhostHandlesID,
        addGhostHandle,
        removeGhostHandle,
        updateGhostHandlesQuantity,
        selectedGhostGlassesID,
        addGhostGlass,
        removeGhostGlass,
        updateGhostGlassesQuantity,
    }
})