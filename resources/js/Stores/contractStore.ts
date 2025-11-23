import { defineStore } from "pinia";
import { ref } from "vue";
import { Contract } from "../lib/types";

export const useContractStore = defineStore('contract', () => {
    const contract = ref<Contract>({
        counterparty_type: 'Физ. лицо',
        counterparty_fullname: '',
        counterparty_phone_1: '',
        counterparty_phone_2: '',
        counterparty_ceo_title: '',
        company_performer_id: 0,
        template_id: 0,
        number: '',
        date: '',
        company: '',
        department_code: 0,
        index: '',
        factory: '',
        installation_address: '',
        price: 0,
        inn: '',
        kpp: '',
        ogrnip: '',
        email: '',
        legal_address: '',
    })
    
    const change_counterparty_type = (counterparty_type: 'Физ. лицо' | 'Юр. лицо' | 'ИП') => {
        contract.value.counterparty_type = counterparty_type
    }
    
    return {
        contract
    }
})