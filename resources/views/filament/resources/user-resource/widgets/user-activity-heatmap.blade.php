<x-filament-widgets::widget>
    <x-filament::section :heading="__('Activity') . ' — ' . $monthLabel">
        <div class="space-y-3">
            <div class="grid w-fit grid-cols-7 gap-1">
                @for ($i = 0; $i < $startOffset; $i++)
                    <div class="h-8 w-8 rounded-sm" style="background-color: #e5e7eb;"></div>
                @endfor

                @foreach ($days as $day)
                    @php
                        $count = $day['count'];
                        [$bg, $color] = match (true) {
                            $count === 0 => ['#e5e7eb', '#111827'],
                            $count >= 1 && $count <= 2 => ['#86efac', '#111827'],
                            $count >= 3 && $count <= 5 => ['#22c55e', '#ffffff'],
                            default => ['#15803d', '#ffffff'],
                        };
                    @endphp

                    <div
                        class="flex h-8 w-8 items-center justify-center rounded-sm text-xs font-medium"
                        style="background-color: {{ $bg }}; color: {{ $color }};"
                        title="{{ $day['date']->format('Y-m-d') }} — {{ $count }} {{ trans_choice('visit|visits', $count) }}"
                    >
                        {{ $day['date']->day }}
                    </div>
                @endforeach
            </div>

            <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                <span>{{ __('Total') }}: <strong>{{ $totalVisits }}</strong> {{ trans_choice('visit|visits', $totalVisits) }}</span>
                <span class="inline-flex items-center gap-1"><span class="h-3 w-3 rounded-sm" style="background-color: #e5e7eb;"></span> 0</span>
                <span class="inline-flex items-center gap-1"><span class="h-3 w-3 rounded-sm" style="background-color: #86efac;"></span> 1–2</span>
                <span class="inline-flex items-center gap-1"><span class="h-3 w-3 rounded-sm" style="background-color: #22c55e;"></span> 3–5</span>
                <span class="inline-flex items-center gap-1"><span class="h-3 w-3 rounded-sm" style="background-color: #15803d;"></span> 6+</span>
            </div>

            @if ($totalVisits === 0)
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ __('No activity recorded for :user this month. Activity is tracked per user after they visit the site.', ['user' => $userName]) }}
                </p>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
