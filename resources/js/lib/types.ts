export interface OpeningType {
    left: string;
    right: string;
    center: string;
    'inner-left': string;
    'inner-right': string;
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
    name?: string;
    doors: number;
    width: number;
    height: number;
    type: keyof OpeningType;
}

export interface Item {
    id?: number;
    vendor_code?: string;
    name: string;
    img?: string;
    retail_price: number;
    purchase_price: number;
    unit: string;
    category_id: number;
    created_at?: string | null;
    updated_at?: string | null;
}

export interface CartItem {
    vendor_code: string;
    quantity: number;
}
