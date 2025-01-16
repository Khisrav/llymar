type DoorType = 'left' | 'right' | 'center' | 'inner-left' | 'inner-right' | 'blind-glazing' | 'triangle';

export const doorsSelectLimiter = (type: string) => {
    const limiters: Record<DoorType, { min: number; max: number; step: number }> = {
        left: { min: 2, max: 8, step: 1 },
        right: { min: 2, max: 8, step: 1 },
        center: { min: 4, max: 10, step: 2 },
        'inner-left': { min: 3, max: 3, step: 1 },
        'inner-right': { min: 3, max: 3, step: 1 },
        'blind-glazing': { min: 0, max: 0, step: 0 },
        'triangle': { min: 0, max: 0, step: 0 },
    };
    
    return limiters[type as DoorType] || { min: 1, max: Infinity, step: 1 };
};