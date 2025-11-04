<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Info Card -->
        {{-- <div class="bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-700 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <x-filament::icon
                    icon="heroicon-o-information-circle"
                    class="h-5 w-5 text-primary-600 dark:text-primary-400 mt-0.5"
                />
                <div class="flex-1">
                    <h3 class="font-semibold text-sm text-primary-900 dark:text-primary-100">
                        Матрица управления разрешениями
                    </h3>
                    <p class="text-sm text-primary-700 dark:text-primary-300 mt-1">
                        Используйте таблицу ниже для быстрого управления разрешениями ролей. 
                        Нажмите на чекбокс, чтобы предоставить или отозвать разрешение для роли.
                    </p>
                </div>
            </div>
        </div> --}}

        <!-- Matrix Table -->
        <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="sticky left-0 z-20 bg-gray-50 dark:bg-gray-900/50 px-4 py-3 text-left font-semibold text-gray-900 dark:text-gray-100 border-r border-gray-200 dark:border-gray-700">
                            Разрешение / Роль
                        </th>
                        @foreach($roles as $role)
                            <th class="px-4 py-3 text-center font-semibold text-gray-900 dark:text-gray-100 border-r border-gray-200 dark:border-gray-700 last:border-r-0 min-w-[150px]">
                                <div class="flex flex-col items-center gap-2">
                                    <span class="block">{{ $role['display_name'] }}</span>
                                    <button 
                                        wire:click="toggleAllPermissionsForRole({{ $role['id'] }})"
                                        type="button"
                                        class="text-xs text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 font-medium"
                                        title="Включить/выключить все разрешения для этой роли"
                                    >
                                        Все вкл/выкл
                                    </button>
                                </div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($groupedPermissions as $group => $permissions)
                        <!-- Group Header -->
                        {{-- <tr class="bg-gray-100 dark:bg-gray-900/30">
                            <td colspan="{{ count($roles) + 1 }}" class="px-4 py-2 font-semibold text-gray-700 dark:text-gray-300 uppercase text-xs tracking-wider">
                                {{ ucfirst($group) }}
                            </td>
                        </tr> --}}
                        
                        <!-- Permissions in this group -->
                        @foreach($permissions as $permission)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="sticky left-0 z-10 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50 px-4 py-3 text-gray-900 dark:text-gray-100 font-medium border-r border-gray-200 dark:border-gray-700">
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="flex flex-col">
                                            <span class="text-sm">{{ $permission['display_name'] }}</span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $permission['name'] }}</span>
                                        </div>
                                        <button 
                                            wire:click="toggleAllRolesForPermission({{ $permission['id'] }})"
                                            type="button"
                                            class="text-xs text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 font-medium whitespace-nowrap"
                                            title="Предоставить/отозвать это разрешение для всех ролей"
                                        >
                                            Все
                                        </button>
                                    </div>
                                </td>
                                @foreach($roles as $role)
                                    <td class="px-4 py-3 text-center border-r border-gray-200 dark:border-gray-700 last:border-r-0">
                                        <div class="flex items-center justify-center">
                                            <input 
                                                type="checkbox"
                                                wire:click="togglePermission({{ $role['id'] }}, {{ $permission['id'] }})"
                                                @if($rolePermissions[$role['id']][$permission['id']] ?? false) checked @endif
                                                class="h-5 w-5 rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:checked:bg-primary-600 dark:checked:border-primary-600 cursor-pointer transition-colors"
                                            >
                                        </div>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>

        @if(empty($roles) || empty($permissions))
            <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-8 text-center">
                <x-filament::icon
                    icon="heroicon-o-exclamation-triangle"
                    class="h-12 w-12 text-gray-400 dark:text-gray-600 mx-auto mb-3"
                />
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">
                    Нет данных для отображения
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    @if(empty($roles))
                        Сначала создайте роли в системе.
                    @elseif(empty($permissions))
                        Сначала создайте разрешения в системе.
                    @endif
                </p>
            </div>
        @endif

        <!-- Pagination -->
        @if($totalPages > 1)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <!-- Pagination Info -->
                    <div class="text-sm text-gray-700 dark:text-gray-300">
                        Показаны разрешения 
                        <span class="font-semibold">{{ (($currentPage - 1) * $perPage) + 1 }}</span>
                        -
                        <span class="font-semibold">{{ min($currentPage * $perPage, $totalPermissions) }}</span>
                        из
                        <span class="font-semibold">{{ $totalPermissions }}</span>
                    </div>

                    <!-- Pagination Controls -->
                    <div class="flex items-center gap-2">
                        <!-- Previous Button -->
                        <button 
                            wire:click="previousPage"
                            {{ $currentPage <= 1 ? 'disabled' : '' }}
                            class="px-3 py-2 text-sm font-medium rounded-lg border transition-colors {{ $currentPage <= 1 ? 'border-gray-200 dark:border-gray-700 text-gray-400 dark:text-gray-600 cursor-not-allowed' : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-400 dark:hover:border-gray-500' }}"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>

                        <!-- Page Numbers -->
                        <div class="flex items-center gap-1">
                            @php
                                $startPage = max(1, $currentPage - 2);
                                $endPage = min($totalPages, $currentPage + 2);
                                
                                // Adjust if we're near the start or end
                                if ($endPage - $startPage < 4) {
                                    if ($currentPage < 3) {
                                        $endPage = min($totalPages, 5);
                                    } else {
                                        $startPage = max(1, $totalPages - 4);
                                    }
                                }
                            @endphp

                            @if($startPage > 1)
                                <button 
                                    wire:click="goToPage(1)"
                                    class="px-3 py-2 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 
                                           text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 
                                           hover:border-gray-400 dark:hover:border-gray-500 transition-colors"
                                >
                                    1
                                </button>
                                @if($startPage > 2)
                                    <span class="px-2 text-gray-500 dark:text-gray-400">...</span>
                                @endif
                            @endif

                            @for($i = $startPage; $i <= $endPage; $i++)
                                <button 
                                    wire:click="goToPage({{ $i }})"
                                    class="px-3 py-2 text-sm font-medium rounded-lg border transition-colors {{ $i == $currentPage ? 'border-primary-500 dark:border-primary-500 bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300' : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-400 dark:hover:border-gray-500' }}"
                                >
                                    {{ $i }}
                                </button>
                            @endfor

                            @if($endPage < $totalPages)
                                @if($endPage < $totalPages - 1)
                                    <span class="px-2 text-gray-500 dark:text-gray-400">...</span>
                                @endif
                                <button 
                                    wire:click="goToPage({{ $totalPages }})"
                                    class="px-3 py-2 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 
                                           text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 
                                           hover:border-gray-400 dark:hover:border-gray-500 transition-colors"
                                >
                                    {{ $totalPages }}
                                </button>
                            @endif
                        </div>

                        <!-- Next Button -->
                        <button 
                            wire:click="nextPage"
                            {{ $currentPage >= $totalPages ? 'disabled' : '' }}
                            class="px-3 py-2 text-sm font-medium rounded-lg border transition-colors {{ $currentPage >= $totalPages ? 'border-gray-200 dark:border-gray-700 text-gray-400 dark:text-gray-600 cursor-not-allowed' : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-400 dark:hover:border-gray-500' }}"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Legend -->
        <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4">
            <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">Подсказки:</h4>
            <ul class="space-y-1 text-sm text-gray-600 dark:text-gray-400">
                <li class="flex items-start gap-2">
                    <span class="text-primary-600 dark:text-primary-400">•</span>
                    <span>Нажмите на чекбокс, чтобы предоставить или отозвать разрешение для конкретной роли</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-primary-600 dark:text-primary-400">•</span>
                    <span>Используйте кнопку "Все вкл/выкл" в заголовке столбца, чтобы включить/выключить все разрешения для роли</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-primary-600 dark:text-primary-400">•</span>
                    <span>Используйте кнопку "Все" рядом с разрешением, чтобы предоставить/отозвать его для всех ролей</span>
                </li>
            </ul>
        </div>
    </div>
</x-filament-panels::page>

