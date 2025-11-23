<script setup>
import { ref, reactive, watch } from 'vue';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from './ui/dialog';
import { Button } from './ui/button';
import { Input } from './ui/input';
import { Label } from './ui/label';
import { vMaska } from 'maska/vue';

// Props
const props = defineProps({
    isOpen: {
        type: Boolean,
        default: false
    }
});

// Emits
const emit = defineEmits(['update:isOpen']);

// Form data
const form = reactive({
    name: '',
    phone: '',
    city: '',
    message: '',
    privacy: false
});

// Form state
const isSubmitting = ref(false);
const errors = ref({});
const submitError = ref('');
const submitSuccess = ref(false);

// Handle dialog open/close
const handleOpenChange = (open) => {
    emit('update:isOpen', open);
    if (!open) {
        resetForm();
    }
};

const closeDialog = () => {
    emit('update:isOpen', false);
};

// Reset form
const resetForm = () => {
    Object.assign(form, {
        name: '',
        phone: '',
        city: '',
        message: '',
        privacy: false
    });
    errors.value = {};
    submitError.value = '';
    submitSuccess.value = false;
};

// Validate form
const validateForm = () => {
    const newErrors = {};
    
    if (!form.name.trim()) {
        newErrors.name = 'Имя обязательно для заполнения';
    }
    
    if (!form.phone.trim()) {
        newErrors.phone = 'Телефон обязателен для заполнения';
    } else if (!/^[\+]?[0-9\s\(\)\-]{10,}$/.test(form.phone.trim())) {
        newErrors.phone = 'Введите корректный номер телефона';
    }
    
    if (!form.city.trim()) {
        newErrors.city = 'Город обязателен для заполнения';
    }
    
    if (!form.privacy) {
        newErrors.privacy = 'Необходимо согласие с политикой конфиденциальности';
    }
    
    errors.value = newErrors;
    return Object.keys(newErrors).length === 0;
};

// Submit form
const submitForm = async () => {
    if (!validateForm()) {
        return;
    }
    
    isSubmitting.value = true;
    submitError.value = '';
    submitSuccess.value = false;
    
    try {
        const response = await fetch('/api/consultation-request', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({
                name: form.name.trim(),
                phone: form.phone.trim(),
                city: form.city.trim(),
                message: form.message.trim() || null,
                source: 'Лендинг'
            })
        });
        
        const data = await response.json();
        
        if (response.ok && data.success) {
            submitSuccess.value = true;
            setTimeout(() => {
                closeDialog();
            }, 2000);
        } else {
            submitError.value = data.message || 'Произошла ошибка при отправке заявки. Попробуйте еще раз.';
        }
    } catch (error) {
        console.error('Submission error:', error);
        submitError.value = 'Произошла ошибка при отправке заявки. Проверьте подключение к интернету и попробуйте еще раз.';
    } finally {
        isSubmitting.value = false;
    }
};

// Watch for form changes to clear errors
watch(form, () => {
    if (Object.keys(errors.value).length > 0) {
        errors.value = {};
    }
    if (submitError.value) {
        submitError.value = '';
    }
}, { deep: true });
</script>

<template>
    <Dialog :open="isOpen" @update:open="handleOpenChange">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Получить консультацию</DialogTitle>
                <!-- <DialogDescription>
                    Оставьте свои контактные данные, и наш специалист свяжется с вами в течение 15 минут
                </DialogDescription> -->
            </DialogHeader>
            
            <form @submit.prevent="submitForm" class="space-y-4">
                <div class="grid gap-2">
                    <Label for="name">Имя *</Label>
                    <Input 
                        id="name"
                        v-model="form.name"
                        type="text"
                        placeholder="Ваше имя"
                        required
                        :disabled="isSubmitting"
                    />
                    <div v-if="errors.name" class="text-xs text-red-500">{{ errors.name }}</div>
                </div>
                
                <div class="grid gap-2">
                    <Label for="phone">Телефон *</Label>
                    <Input 
                        id="phone"
                        v-model="form.phone"
                        type="tel"
                        placeholder="+7 (999) 123-45-67"
                        v-maska="'+7 (###) ###-##-##'"
                        required
                        :disabled="isSubmitting"
                    />
                    <div v-if="errors.phone" class="text-xs text-red-500">{{ errors.phone }}</div>
                </div>
                
                <div class="grid gap-2">
                    <Label for="city">Город *</Label>
                    <Input 
                        id="city"
                        v-model="form.city"
                        type="text"
                        placeholder="Ваш город"
                        required
                        :disabled="isSubmitting"
                    />
                    <div v-if="errors.city" class="text-xs text-red-500">{{ errors.city }}</div>
                </div>
                
                <!-- <div class="grid gap-2">
                    <Label for="message">Сообщение</Label>
                    <textarea 
                        id="message"
                        v-model="form.message"
                        rows="3"
                        class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                        placeholder="Расскажите о вашем проекте..."
                        :disabled="isSubmitting"
                    ></textarea>
                </div> -->
                
                <div class="flex items-center space-x-2">
                    <input 
                        id="privacy"
                        v-model="form.privacy"
                        type="checkbox"
                        required
                        :disabled="isSubmitting"
                        class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded"
                    />
                    <Label for="privacy" class="text-sm text-gray-600">
                        Я согласен с <a href="#" class="text-primary hover:underline">политикой конфиденциальности</a> *
                    </Label>
                </div>
                <div v-if="errors.privacy" class="text-xs text-red-500">{{ errors.privacy }}</div>
                
                <div v-if="submitError" class="text-sm text-red-500 bg-red-50 p-3 rounded-md">
                    {{ submitError }}
                </div>
                
                <div v-if="submitSuccess" class="text-sm text-green-600 bg-green-50 p-3 rounded-md">
                    Спасибо! Ваша заявка отправлена. Мы свяжемся с вами в ближайшее время.
                </div>
                
                <div class="flex gap-3 pt-4">
                    <Button 
                        type="submit" 
                        class="flex-1 bg-dark-green hover:bg-dark-green/90"
                        :disabled="isSubmitting"
                    >
                        <span v-if="isSubmitting" class="flex items-center gap-2">
                            <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                            Отправка...
                        </span>
                        <span v-else>Отправить заявку</span>
                    </Button>
                    <!-- <Button 
                        type="button" 
                        variant="outline" 
                        @click="closeDialog"
                        :disabled="isSubmitting"
                    >
                        Отмена
                    </Button> -->
                </div>
            </form>
        </DialogContent>
    </Dialog>
</template>

<style scoped>
/* Custom styles for form elements */
.dark-green {
    background-color: #23322D;
}
</style> 