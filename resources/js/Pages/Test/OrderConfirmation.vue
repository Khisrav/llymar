<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import AuthenticatedHeaderLayout from '../../Layouts/AuthenticatedHeaderLayout.vue';
import { ArrowLeftIcon, TruckIcon, MapPinIcon, UserIcon, PhoneIcon, BanknoteIcon, EyeIcon, ShoppingCartIcon } from 'lucide-vue-next';
import Button from '../../Components/ui/button/Button.vue';
import Card from '../../Components/ui/card/Card.vue';
import CardHeader from '../../Components/ui/card/CardHeader.vue';
import CardContent from '../../Components/ui/card/CardContent.vue';
import { ref, computed } from 'vue';
import Tabs from '../../Components/ui/tabs/Tabs.vue';
import TabsContent from '../../Components/ui/tabs/TabsContent.vue';
import TabsList from '../../Components/ui/tabs/TabsList.vue';
import TabsTrigger from '../../Components/ui/tabs/TabsTrigger.vue';
import CardTitle from '../../Components/ui/card/CardTitle.vue';
import { Select } from '../../Components/ui/select';
import SelectTrigger from '../../Components/ui/select/SelectTrigger.vue';
import SelectValue from '../../Components/ui/select/SelectValue.vue';
import SelectContent from '../../Components/ui/select/SelectContent.vue';
import SelectItem from '../../Components/ui/select/SelectItem.vue';
import Label from '../../Components/ui/label/Label.vue';
import Input from '../../Components/ui/input/Input.vue';
import { vMaska } from 'maska/vue';
import { Textarea } from '../../Components/ui/textarea';
import { Checkbox } from '../../Components/ui/checkbox';
import { useItemsStore } from '../../Stores/itemsStore';
import { currencyFormatter } from '../../Utils/currencyFormatter';
import CartItem from '../../Components/Cart/CartItem.vue';

const itemsStore = useItemsStore()
const showCartItems = ref(false)

// Get props
const { user_role, logistics_companies, user, dealers, user_companies, items, additional_items, glasses, services, categories, user_default_factor } = usePage().props as any

// Initialize itemsStore with backend data
itemsStore.items = items || []
itemsStore.additional_items = additional_items || {}
itemsStore.glasses = glasses || []
itemsStore.services = services || []
itemsStore.user = user || {}
itemsStore.categories = categories || []

// Initialize user's default factor
itemsStore.initializeUserFactor(user_default_factor || 'pz')

// Initialize cart items from session storage
itemsStore.initiateCartItems()

// Find main company if exists
const mainCompany = user_companies?.find((company: any) => company.is_main)

// Form state
const orderForm = ref({
	// Delivery forms
	logistics_company_id: '',
	delivery_address: '',
	fullname: itemsStore.user?.name || '',
	phone: itemsStore.user?.phone || '',
	
	// G1 - Current account
	current_account: '',
	
	// G3 - Dealer
	dealer_id: '',
	
	// G4 - Company
	company_id: mainCompany?.id?.toString() || '',
	
	// Comment and consent
	comment: '',
	consent: false,
})

const checkout = () => {
	if (!isCheckoutValid.value) {
		console.warn('Cannot checkout: form is not valid')
		return
	}
	
	// TODO: Implement backend checkout logic
	console.log('Checkout data:', {
		form: orderForm.value,
		cartItems: itemsStore.cartItems,
		totalAmount: Object.keys(itemsStore.cartItems).reduce((total, itemID) => {
			const item = itemsStore.getItemInfo(parseInt(itemID))
			const cartItem = itemsStore.cartItems[parseInt(itemID)]
			return total + (item && cartItem ? item.retail_price * cartItem.quantity : 0)
		}, 0)
	})
	
	// Show success message (temporary)
	alert('Заказ успешно оформлен! (Backend integration pending)')
}

const permissionGroups = ref({
    'G1': [
        'Super-Admin',
        'Operator'
    ],
    'G2': [
        'Dealer',
        'Manager',
        'Dealer Ch',
        'Dealer R'
    ],
    'G3': [
        'Super-Admin',
        'Operator',
        'ROP'
    ],
    'G4': [
        'Super-Admin',
        'Operator',
        'ROP',
        'Dealer',
        'Manager',
        'Dealer Ch',
        'Dealer R'
    ]
})

const roleGroups = ref({
    'Super-Admin': ['G1', 'G2'],
    'Operator': ['G1', 'G3', 'G4'],
    'ROP': ['G3', 'G4'],
    'Dealer': ['G2', 'G4'],
    'Manager': ['G2', 'G4'],
    'Dealer Ch': ['G2', 'G4'],
    'Dealer R': ['G2', 'G4']
})

const deliveryOptionTabGroups = ref({
    'G1': [
        { value: 'montazh', title: 'Монтаж' },
        { value: 'dostavka', title: 'Доставка' },
    ],
    'G2': [],
    'G3': [],
    'G4': [
        { value: 'montazh', title: 'Монтаж' },
        { value: 'dostavka', title: 'Доставка' },
    ],
})

const is_in_group = (group_id: 'G1' | 'G2' | 'G3' | 'G4') => permissionGroups.value[group_id].includes(user_role)

// Get all tabs accessible to the current user based on their role groups
const availableTabs = computed(() => {
    const userGroups = roleGroups.value[user_role as keyof typeof roleGroups.value] || []
    const tabs: Array<{ value: string, title: string }> = []
    
    userGroups.forEach((groupId: string) => {
        const groupTabs = deliveryOptionTabGroups.value[groupId as keyof typeof deliveryOptionTabGroups.value] || []
        tabs.push(...groupTabs)
    })
    
    return tabs
})

// Get default tab (first available tab)
const defaultTab = computed(() => {
    return availableTabs.value[0]?.value || 'montazh'
})

// Compute if checkout is valid
const isCheckoutValid = computed(() => {
    return orderForm.value.consent && 
           orderForm.value.fullname.trim() !== '' && 
           orderForm.value.phone.trim() !== '' &&
           Object.keys(itemsStore.cartItems).length > 0
})
</script>

<template>
	<Head title="Оформление заказа" />
	<AuthenticatedHeaderLayout />
    <div class="container p-0 md:p-4 mb-8">
        <div class="p-4 md:p-8 md:mt-8 md:border space-y-4 rounded-2xl bg-background">
            <div class="flex items-center gap-4 mb-6">
				<Link href="/app/calculator"><Button size="icon" variant="outline"><ArrowLeftIcon /></Button></Link>
				<h2 class="text-3xl font-semibold">Оформление заказа</h2>
			</div>
            <Tabs :default-value="defaultTab">
                <TabsList>
                    <TabsTrigger
                        v-for="tab in availableTabs"
                        :key="tab.value"
                        :value="tab.value"
                    >
                        {{ tab.title }}
                    </TabsTrigger>
                </TabsList>

                <TabsContent value="montazh">
                    <Card class="">
                        <CardHeader class="p-4 pb-0">
                            <CardTitle class="text-base md:text-lg flex items-center gap-2">
                                <TruckIcon class="h-5 w-5 text-primary" />
                                Доставка с монтажом
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="p-4 space-y-4">
                            <!-- address input field -->
                            <div class="space-y-2">
                                <Label for="delivery_address_montazh" class="text-sm font-medium flex items-center gap-2">
                                    <MapPinIcon class="h-4 w-4" />
                                    Адрес доставки\монтажа <span class="text-destructive">*</span>
                                </Label>
                                <Input 
                                    id="delivery_address_montazh"
                                    v-model="orderForm.delivery_address" 
                                    type="text" 
                                    required
                                    class="transition-all duration-200 focus:ring-2 focus:ring-primary/20"
                                    placeholder="Введите адрес доставки"
                                />
                            </div>
                            
                            <!-- Contact Information -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label for="fullname_montazh" class="text-sm font-medium flex items-center gap-2">
                                        <UserIcon class="h-4 w-4" />
                                        Ф.И.О. <span class="text-destructive">*</span>
                                    </Label>
                                    <Input 
                                        id="fullname_montazh"
                                        v-model="orderForm.fullname" 
                                        type="text" 
                                        required 
                                        class="transition-all duration-200 focus:ring-2 focus:ring-primary/20"
                                        placeholder="Введите ваше полное имя"
                                    />
                                </div>

                                <div class="space-y-2">
                                    <Label for="phone_montazh" class="text-sm font-medium flex items-center gap-2">
                                        <PhoneIcon class="h-4 w-4" />
                                        Номер телефона <span class="text-destructive">*</span>
                                    </Label>
                                    <Input 
                                        id="phone_montazh"
                                        v-model="orderForm.phone" 
                                        type="tel" 
                                        v-maska="'+7 (###) ### ##-##'" 
                                        required 
                                        class="transition-all duration-200 focus:ring-2 focus:ring-primary/20"
                                        placeholder="+7 (___) ___ __-__"
                                    />
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>

                <TabsContent value="dostavka">
                    <Card class="">
                        <CardHeader class="p-4 pb-0">
                            <CardTitle class="text-base md:text-lg flex items-center gap-2">
                                <TruckIcon class="h-5 w-5 text-primary" />
                                Доставка
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="p-4 space-y-4">
                            <!-- address input field -->
                            <div class="space-y-2">
                                <Label for="delivery_address_dostavka" class="text-sm font-medium flex items-center gap-2">
                                    <MapPinIcon class="h-4 w-4" />
                                    Адрес доставки <span class="text-destructive">*</span>
                                </Label>
                                <Input 
                                    id="delivery_address_dostavka"
                                    v-model="orderForm.delivery_address" 
                                    type="text" 
                                    required
                                    class="transition-all duration-200 focus:ring-2 focus:ring-primary/20"
                                    placeholder="Введите адрес доставки"
                                />
                            </div>
                            
                            <!-- Contact Information -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label for="fullname_dostavka" class="text-sm font-medium flex items-center gap-2">
                                        <UserIcon class="h-4 w-4" />
                                        Ф.И.О. <span class="text-destructive">*</span>
                                    </Label>
                                    <Input 
                                        id="fullname_dostavka"
                                        v-model="orderForm.fullname" 
                                        type="text" 
                                        required 
                                        class="transition-all duration-200 focus:ring-2 focus:ring-primary/20"
                                        placeholder="Введите ваше полное имя"
                                    />
                                </div>

                                <div class="space-y-2">
                                    <Label for="phone_dostavka" class="text-sm font-medium flex items-center gap-2">
                                        <PhoneIcon class="h-4 w-4" />
                                        Номер телефона <span class="text-destructive">*</span>
                                    </Label>
                                    <Input 
                                        id="phone_dostavka"
                                        v-model="orderForm.phone" 
                                        type="tel" 
                                        v-maska="'+7 (###) ### ##-##'" 
                                        required 
                                        class="transition-all duration-200 focus:ring-2 focus:ring-primary/20"
                                        placeholder="+7 (___) ___ __-__"
                                    />
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>

                <TabsContent value="tk">
                    <Card>
                        <CardHeader class="p-4 pb-0">
                            <CardTitle class="text-base md:text-lg flex items-center gap-2">
                                <TruckIcon class="h-5 w-5 text-primary" />
                                Транспортная компания
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="p-4 space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                                <div class="space-y-2">
                                    <Label for="logistics_company" class="text-sm font-medium flex items-center gap-2">
                                        <TruckIcon class="h-4 w-4" />
                                        Транспортная компания <span class="text-destructive">*</span>
                                    </Label>
                                    <Select v-model="orderForm.logistics_company_id" required>
                                        <SelectTrigger id="logistics_company">
                                            <SelectValue placeholder="Выберите транспортную компанию" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem 
                                                v-for="company in logistics_companies" 
                                                :key="company.id" 
                                                :value="company.id.toString()"
                                            >
                                                {{ company.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div class="space-y-2">
                                    <Label for="delivery_address_tk" class="text-sm font-medium flex items-center gap-2">
                                        <MapPinIcon class="h-4 w-4" />
                                        Адрес терминала\доставки <span class="text-destructive">*</span>
                                    </Label>
                                    <Input 
                                        id="delivery_address_tk"
                                        v-model="orderForm.delivery_address" 
                                        type="text" 
                                        required
                                        class="transition-all duration-200 focus:ring-2 focus:ring-primary/20"
                                        placeholder="Введите адрес доставки"
                                    />
                                </div>
                            </div>

                            <!-- Contact Information -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                                <div class="space-y-2">
                                    <Label for="fullname_tk" class="text-sm font-medium flex items-center gap-2">
                                        <UserIcon class="h-4 w-4" />
                                        Ф.И.О. <span class="text-destructive">*</span>
                                    </Label>
                                    <Input 
                                        id="fullname_tk"
                                        v-model="orderForm.fullname" 
                                        type="text" 
                                        required 
                                        class="transition-all duration-200 focus:ring-2 focus:ring-primary/20"
                                        placeholder="Введите ваше полное имя"
                                    />
                                </div>

                                <div class="space-y-2">
                                    <Label for="phone_tk" class="text-sm font-medium flex items-center gap-2">
                                        <PhoneIcon class="h-4 w-4" />
                                        Номер телефона <span class="text-destructive">*</span>
                                    </Label>
                                    <Input 
                                        id="phone_tk"
                                        v-model="orderForm.phone" 
                                        type="tel" 
                                        v-maska="'+7 (###) ### ##-##'" 
                                        required 
                                        class="transition-all duration-200 focus:ring-2 focus:ring-primary/20"
                                        placeholder="+7 (___) ___ __-__"
                                    />
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>

                <TabsContent value="samovivoz">
                    <Card>
                        <CardHeader class="p-4 pb-0">
                            <CardTitle class="text-base md:text-lg flex items-center gap-2">
                                <MapPinIcon class="h-5 w-5 text-primary" />
                                Самовывоз
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="p-4 space-y-4">
                            <div class="bg-muted/50 p-4 rounded-lg space-y-3">
                                <p class="text-sm font-medium">По готовности вы можете забрать заказ в нашем офисе</p>
                                <div class="flex items-start gap-2 text-sm">
                                    <MapPinIcon class="h-4 w-4 mt-0.5 text-primary flex-shrink-0" />
                                    <span>г. Москва, ул. Пушкинская, д. 1</span>
                                </div>
                                <div class="flex items-center gap-2 text-sm">
                                    <PhoneIcon class="h-4 w-4 text-primary flex-shrink-0" />
                                    <span>+7 (999) 999-99-99</span>
                                </div>
                            </div>
                            
                            <!-- Contact Information -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label for="fullname_samovivoz" class="text-sm font-medium flex items-center gap-2">
                                        <UserIcon class="h-4 w-4" />
                                        Ф.И.О. <span class="text-destructive">*</span>
                                    </Label>
                                    <Input 
                                        id="fullname_samovivoz"
                                        v-model="orderForm.fullname" 
                                        type="text" 
                                        required 
                                        class="transition-all duration-200 focus:ring-2 focus:ring-primary/20"
                                        placeholder="Введите ваше полное имя"
                                    />
                                </div>

                                <div class="space-y-2">
                                    <Label for="phone_samovivoz" class="text-sm font-medium flex items-center gap-2">
                                        <PhoneIcon class="h-4 w-4" />
                                        Номер телефона <span class="text-destructive">*</span>
                                    </Label>
                                    <Input 
                                        id="phone_samovivoz"
                                        v-model="orderForm.phone" 
                                        type="tel" 
                                        v-maska="'+7 (###) ### ##-##'" 
                                        required 
                                        class="transition-all duration-200 focus:ring-2 focus:ring-primary/20"
                                        placeholder="+7 (___) ___ __-__"
                                    />
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>
            </Tabs>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- G1 - Billing Information -->
                <Card v-if="is_in_group('G1')">
                    <CardHeader class="p-4 pb-0">
                        <CardTitle class="text-base md:text-lg flex items-center gap-2">
                            <BanknoteIcon class="h-5 w-5 text-primary" />
                            Платёжная информация
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="p-4">
                        <div class="space-y-2">
                            <!-- <Label for="current_account" class="text-sm font-medium flex items-center gap-2">
                                <BanknoteIcon class="h-4 w-4" />
                                Расчетный счет для договора
                            </Label> -->
                            <Input 
                                id="current_account"
                                v-model="orderForm.current_account"
                                type="text" 
                                class="transition-all duration-200 focus:ring-2 focus:ring-primary/20"
                                placeholder="Введите расчетный счет для договора"
                            />
                        </div>
                    </CardContent>
                </Card>

                <!-- G3 - Dealer Selection -->
                <Card v-if="is_in_group('G3')">
                    <CardHeader class="p-4 pb-0">
                        <CardTitle class="text-base md:text-lg flex items-center gap-2">
                            <UserIcon class="h-5 w-5 text-primary" />
                            Выбор дилера
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="p-4">
                        <div class="space-y-2">
                            <!-- <Label for="dealer" class="text-sm font-medium">
                                Дилер
                            </Label> -->
                            <Select v-model="orderForm.dealer_id">
                                <SelectTrigger id="dealer">
                                    <SelectValue placeholder="Выберите дилера"/>
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="dealer in dealers" :key="dealer.id" :value="dealer.id.toString()">
                                        {{ dealer.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </CardContent>
                </Card>

                 <!-- G4 - Company Selection -->
                 <Card v-if="is_in_group('G4')">
                     <CardHeader class="p-4 pb-0">
                         <CardTitle class="text-base md:text-lg flex items-center gap-2">
                             <TruckIcon class="h-5 w-5 text-primary" />
                             Выбор компании
                         </CardTitle>
                     </CardHeader>
                     <CardContent class="p-4 space-y-4">
                         <div v-if="user_companies && user_companies.length > 0" class="space-y-2">
                             <!-- <Label for="company" class="text-sm font-medium">
                                 Компания
                             </Label> -->
                             <Select v-model="orderForm.company_id">
                                 <SelectTrigger id="company">
                                     <SelectValue placeholder="Выберите компанию"/>
                                 </SelectTrigger>
                                 <SelectContent>
                                     <SelectItem v-for="company in user_companies" :key="company.id" :value="company.id.toString()">
                                         {{ company.short_name || company.full_name }}
                                         <span v-if="company.is_main" class="ml-2 text-xs text-primary">(Основная)</span>
                                     </SelectItem>
                                 </SelectContent>
                             </Select>
                         </div>
                         <div v-else class="text-center py-4 space-y-4">
                             <div class="text-sm text-muted-foreground pb-2">
                                 У вас пока нет своих компаний
                             </div>
                             <Link href="/app/companies">
                                 <Button variant="outline" size="sm" class="gap-2">
                                     <TruckIcon class="h-4 w-4" />
                                     Создать компанию
                                 </Button>
                             </Link>
                         </div>
                        <div class="flex items-start gap-3">
                            <Checkbox 
                                id="terms" 
                                v-model:checked="orderForm.consent"
                                class="mt-0.5"
                            />
                            <Label for="terms" class="text-sm font-normal leading-relaxed cursor-pointer">
                                Я согласен с условиями обработки персональных данных и даю согласие на их обработку
                            </Label>
                        </div>
                     </CardContent>
                 </Card>

                 <!-- Комментарий, согласие на обработку персональных данных -->
                <Card>
                    <!-- <CardHeader class="p-4 pb-0">
                        <CardTitle class="text-base md:text-lg">Дополнительная информация</CardTitle>
                    </CardHeader> -->
                    <CardContent class="p-4 space-y-4">
                        <div class="space-y-2">
                            <Label for="comment" class="text-sm font-medium">Комментарий к заказу</Label>
                            <Textarea 
                                id="comment"
                                v-model="orderForm.comment"
                                placeholder="Введите комментарий или особые пожелания к заказу" 
                                :rows="3" 
                                class="transition-all duration-200 focus:ring-2 focus:ring-primary/20 resize-none" 
                            />
                        </div>
                        <!-- Action Buttons -->
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-3">
                            <Button 
                                variant="outline" 
                                size="lg"
                                @click="showCartItems = !showCartItems"
                                class="gap-2 w-full sm:w-auto"
                            >
                                <EyeIcon class="h-4 w-4" />
                                {{ showCartItems ? 'Скрыть товары' : 'Показать товары' }}
                            </Button>
                            <Button 
                                variant="default" 
                                size="lg"
                                @click="checkout"
                                :disabled="!isCheckoutValid"
                                class="gap-2 w-full sm:w-auto"
                            >
                                <ShoppingCartIcon class="h-4 w-4" />
                                Оформить заказ
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Validation message -->
            <div v-if="!isCheckoutValid && Object.keys(itemsStore.cartItems).length === 0" class="text-sm text-destructive text-center p-3 rounded-lg bg-destructive/10 border border-destructive/20">
                ⚠️ Корзина пуста. Добавьте товары для оформления заказа.
            </div>
            <div v-else-if="!isCheckoutValid" class="text-sm text-muted-foreground text-center">
                Заполните все обязательные поля и дайте согласие на обработку данных
            </div>

            <!-- Order Summary and Cart Items -->
            <Card v-if="showCartItems" class="transition-all duration-300 ease-in-out">
                <CardHeader class="p-4 pb-0">
                    <CardTitle class="text-base md:text-lg flex items-center gap-2">
                        <ShoppingCartIcon class="h-5 w-5 text-primary" />
                        Перечень товаров
                        <span class="text-sm font-normal text-muted-foreground ml-auto">
                            ({{ Object.keys(itemsStore.cartItems).length }} {{ Object.keys(itemsStore.cartItems).length === 1 ? 'товар' : 'товаров' }})
                        </span>
                    </CardTitle>
                </CardHeader>
                <CardContent class="p-4">
                    <!-- Empty State -->
                    <div v-if="Object.keys(itemsStore.cartItems).length === 0" class="flex flex-col items-center justify-center py-12 text-center">
                        <ShoppingCartIcon class="h-16 w-16 text-muted-foreground/30 mb-4" />
                        <p class="text-lg font-medium text-muted-foreground mb-2">Корзина пуста</p>
                        <p class="text-sm text-muted-foreground">Добавьте товары в корзину для оформления заказа</p>
                        <Link href="/app/calculator">
                            <Button variant="outline" class="mt-4">
                                <ArrowLeftIcon class="h-4 w-4 mr-2" />
                                Вернуться к калькулятору
                            </Button>
                        </Link>
                    </div>
                    
                    <!-- Cart Items -->
                    <div v-else class="space-y-3">
                        <div 
                            v-for="itemID in Object.keys(itemsStore.cartItems)" 
                            :key="itemID"
                            class="flex items-center border-b pb-4"
                        >
                            <CartItem 
                                v-if="itemsStore.getItemInfo(parseInt(itemID))"
                                :item="itemsStore.getItemInfo(parseInt(itemID))!" 
                                :disabled="true"
                            />
                            <div v-else class="text-sm text-muted-foreground">
                                Неизвестная деталь (ID: {{ itemID }})
                            </div>
                        </div>
                        
                        <!-- Total -->
                        <div class="flex items-center justify-between p-4 rounded-lg bg-primary/10 border-2 border-primary/20 mt-4">
                            <div class="font-semibold text-base">Итого:</div>
                            <div class="font-bold text-xl text-primary">
                                {{ currencyFormatter(itemsStore.total_price.with_discount) }}
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>