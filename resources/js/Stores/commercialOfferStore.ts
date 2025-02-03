import { defineStore } from "pinia"
import { CommercialOffer } from "../lib/types"
import { ref } from "vue"

export const useCommercialOfferStore = defineStore('commercialOffer', () => {
    const commercialOffer = ref<CommercialOffer>({
        customer: {
            name: '',
            phone: '',
            address: '',
            comment: '',
        },
        manufacturer: {
            manufacturer: '',    
            company: '',
            phone: ''
        },
    })
    
    return {
        commercialOffer
    }
})