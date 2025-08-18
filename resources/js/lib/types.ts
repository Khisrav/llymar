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
    mp?: number;
    door_handle_item_id?: number;
}

export interface Item {
    id?: number;
    vendor_code?: string;
    name: string;
    img?: string;
    images?: string[];
    description?: string;
    retail_price: number;
    purchase_price: number;
    unit: string;
    category_id: number;
    discount: number;
    created_at?: string | null;
    updated_at?: string | null;
}

export interface ItemProperty {
    id?: number;
    name: string;
    value: string;
    item_id: number;
    created_at?: string | null;
    updated_at?: string | null;
}

export interface CartItem {
    quantity: number;
    checked?: boolean;
}

export interface User {
    id: number;
    name: string;
    email: string;
    address?: string;
    phone?: string;
    company?: string;
    discount: number;
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
    order_number?: string | null;
    ral_code?: string | null;
    created_at?: string | null;
    update_at?: string | null;
    contracts?: Contract[];
}

export interface Contract {
    id: number;
    order_id: number;
    number: string;
    date: string;
    created_at?: string | null;
    updated_at?: string | null;
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
    },
}

export interface CommercialOfferRecord {
    id: number;
    user_id: number;
    customer_name?: string;
    customer_phone?: string;
    customer_address?: string;
    customer_comment?: string;
    manufacturer_name?: string;
    manufacturer_phone?: string;
    total_price: number;
    markup_percentage: number;
    selected_factor?: string;
    openings: Opening[];
    additional_items: Item[];
    glass: Item;
    cart_items: CartItem[] | Record<string, CartItem>;
    services: Item[];
    created_at: string;
    updated_at: string;
}

export interface Contract {
    counterparty_type: 'Физ. лицо' | 'Юр. лицо' | 'ИП';
    counterparty_fullname: string;
    counterparty_phone_1: string;
    counterparty_phone_2?: string;
    counterparty_ceo_title?: string;
    
    company_performer_id: number;
    template_id: number;
    number: string;
    date: string;
    company: string;
    department_code: number;
    index: string;
    factory: string;
    installation_address: string;
    price: number;

    inn?: string;
    kpp?: string;
    ogrnip?: string;
    email?: string;
    legal_address?: string;
    ogrn?: string;
}