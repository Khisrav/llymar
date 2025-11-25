/**
 * Formats a quantity number to display with maximum 2 decimal places
 * Removes trailing zeros and unnecessary decimal points
 * @param quantity - The quantity number to format
 * @returns Formatted quantity string
 */
export function quantityFormatter(quantity: number, decimalPlaces?: number): string {
    if (quantity === null || quantity === undefined || isNaN(quantity)) {
        return '0';
    }
    
    // Round to 2 decimal places
    const rounded = Math.round(quantity * 100) / 100;
    
    // Convert to string and remove trailing zeros
    return rounded.toLocaleString('en-US', {
        minimumFractionDigits: 0,
        maximumFractionDigits: decimalPlaces ?? 2,
        useGrouping: false
    });
}

/**
 * Parses a quantity string and ensures it respects the 2 decimal places limit
 * @param value - The string value to parse
 * @returns Parsed and limited number
 */
export function parseQuantity(value: string | number): number {
    if (typeof value === 'number') {
        return Math.round(value * 100) / 100;
    }
    
    const parsed = parseFloat(value);
    if (isNaN(parsed)) {
        return 0;
    }
    
    return Math.round(parsed * 100) / 100;
} 