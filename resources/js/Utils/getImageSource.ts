export const getImageSource = (image: string): string => import.meta.env.VITE_APP_URL + `/storage${image}`