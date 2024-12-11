export const doorsSelectLimiter = (type: string) => {
    switch (type) {
        case 'left':
        case 'right':
            return { min: 2, max: 10, step: 1 };
        case 'center':
            return { min: 4, max: 12, step: 2 };
        case 'inner-left':
        case 'inner-right':
            return { min: 3, max: 3, step: 1 };
        default:
            return { min: 1, max: Infinity, step: 1 }; // Default values
    }
};