import { defineStore } from "pinia";
import { ref } from "vue";
import { Contract } from "../lib/types";

export const useContractStore = defineStore('contract', () => {
    const contract = ref<Contract>({
        counterparty_type: 'Физ. лицо',
        company_performer_id: 0,
        template_id: 0,
        number: '',
        date: '',
        company: '',
        department_code: 0,
        index: '',
        factory: '',
        installation_address: '',
        ceo_fullname: '',
        phone_1: '',
        phone_2: '',
        price: 0,
        inn: '',
        kpp: '',
        ogrnip: '',
        email: '',
        legal_address: '',
        ceo_title: '',
    })
    
    const change_counterparty_type = (counterparty_type: 'Физ. лицо' | 'Юр. лицо' | 'ИП') => {
        contract.value.counterparty_type = counterparty_type
    }
    
    return {
        contract
    }
})