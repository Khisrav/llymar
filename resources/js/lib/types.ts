export interface OpeningType {
    left: string;
    right: string;
    center: string;
    'inner-left': string;
    'inner-right': string;
    'blind-glazing': string;
    triangle: string;
}

export interface OpeningImages {
    left: string;
    right: string;
    center: string;
    'inner-left': string;
    'inner-right': string;
    'blind-glazing': string;
    triangle: string;
}

export interface Opening {
    id?: number;
    name?: string;
    doors: number;
    width: number;
    height: number;
    type: keyof OpeningType;
    
    //parameters
    a?: number;
    b?: number;
    c?: number;
    d?: number;
    e?: number;
    f?: number;
    g?: number;
    i?: number;
}

export interface Item {
    id: number;
    vendor_code?: string;
    name: string;
    img?: string;
    retail_price: number;
    purchase_price: number;
    unit: string;
    category_id: number;
    discount: number;
    created_at?: string | null;
    updated_at?: string | null;
}

export interface CartItem {
    quantity: number;
}

export interface User {
    id: number;
    name: string;
    email: string;
    address?: string;
    phone?: string;
    company?: string;
    discount: number;
    reduction_factor_key?: string;
    wholesale_factor_keey?: string;
    inn?: string;
    kpp?: string;
    bik?: string;
    bank?: string;
    legal_address?: string;
    current_account?: string;
    correspondent_account?: string;
    telegram?: string;
    created_at?: string | null;
    updated_at?: string | null;
}

export interface Order {
    id: number;
    user_id: number;
    status: string;
    comment?: string | null;
    total_price: number;
    customer_name: string;
    customer_phone: string;
    customer_address: string;
    created_at?: string | null;
    update_at?: string | null;
}

export interface Pagination {
    active: boolean;
    label: string;
    url?: string | null;
}

export interface Category {
    id: number;
    name: string;
    created_at: string | null;
    updated_at: string;
    reduction_factors: ReductionFactor[] | null;
}

export interface ReductionFactor {
    key: string;
    value: string | number;
}

export interface WholesaleFactor {
    name: string;
    value: number;
}

export interface CommercialOffer {
    customer: {
        name: string;
        phone: string;
        address: string;
        comment: string;
    },
    manufacturer: {
        title: string;
        // manufacturer_name: string;
        manufacturer: string;
        company: string;
        phone: string;
    }
}