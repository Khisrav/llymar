import { defineStore } from "pinia"
import { CommercialOffer } from "../lib/types"
import { ref } from "vue"

export const useCommercialOfferStore = defineStore('commercialOffer', () => {
    const commercialOfferId = ref<number | null>(null)
    const commercialOffer = ref<CommercialOffer>({
        customer: {
            name: '',
            phone: '',
            address: '',
            comment: '',
        },
        manufacturer: {
            title: 'Информация о производителе',
            manufacturer: '',    
            company: '',
            phone: ''
        },
    })

    // Initialize from session storage on store creation
    const storedId = sessionStorage.getItem('commercialOfferId')
    if (storedId) {
        commercialOfferId.value = parseInt(storedId)
    }

    // Clear commercial offer data and ID
    const clearCommercialOffer = () => {
        commercialOfferId.value = null
        sessionStorage.removeItem('commercialOfferId')
        commercialOffer.value = {
            customer: {
                name: '',
                phone: '',
                address: '',
                comment: '',
            },
            manufacturer: {
                title: 'Информация о производителе',
                manufacturer: '',    
                company: '',
                phone: ''
            },
        }
    }

    // Set commercial offer ID
    const setCommercialOfferId = (id: number | null) => {
        commercialOfferId.value = id
        if (id) {
            sessionStorage.setItem('commercialOfferId', id.toString())
        } else {
            sessionStorage.removeItem('commercialOfferId')
        }
    }
    
    return {
        commercialOffer,
        commercialOfferId,
        clearCommercialOffer,
        setCommercialOfferId
    }
})