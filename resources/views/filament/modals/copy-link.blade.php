<div x-data="{ copied: false }">
    <div class="space-y-4">
        <div class="text-sm text-gray-600 dark:text-gray-400">
            Скопируйте эту ссылку и отправьте новому дилеру для регистрации:
        </div>
        
        <div class="flex gap-2">
            <input 
                type="text" 
                readonly 
                value="{{ $url }}" 
                class="flex-1 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-primary-500 focus:ring-primary-500"
                x-ref="urlInput"
            />
            
            <button 
                type="button"
                @click="
                    $refs.urlInput.select();
                    navigator.clipboard.writeText('{{ $url }}');
                    copied = true;
                    setTimeout(() => copied = false, 2000);
                "
                class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors duration-200 flex items-center gap-2 whitespace-nowrap"
            >
                <svg x-show="!copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                </svg>
                <svg x-show="copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span x-text="copied ? 'Скопировано!' : 'Копировать'"></span>
            </button>
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
</style>

