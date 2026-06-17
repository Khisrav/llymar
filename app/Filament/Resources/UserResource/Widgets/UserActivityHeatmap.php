<?php

namespace App\Filament\Resources\UserResource\Widgets;

use App\Models\User;
use App\Models\UserActivityLog;
use Carbon\Carbon;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Collection;

class UserActivityHeatmap extends Widget
{
    public ?User $record = null;

    protected static string $view = 'filament.resources.user-resource.widgets.user-activity-heatmap';

    protected int | string | array $columnSpan = 2;

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $user = $this->record;

        if (! $user) {
            return [
                'days' => [],
                'startOffset' => 0,
                'monthLabel' => '',
                'userName' => '',
                'totalVisits' => 0,
            ];
        }

        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();
        $daysInMonth = $startDate->daysInMonth;

        /** @var Collection<int, UserActivityLog> $logs */
        $logs = UserActivityLog::query()
            ->where('user_id', $user->id)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->keyBy(fn (UserActivityLog $log) => $log->date->day);

        $days = [];
        $totalVisits = 0;

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = $startDate->copy()->addDays($day - 1);
            $log = $logs->get($day);
            $count = $log ? $log->visits_count : 0;

            $days[] = [
                'date' => $date,
                'count' => $count,
            ];

            $totalVisits += $count;
        }

        return [
            'days' => $days,
            'startOffset' => $startDate->dayOfWeek,
            'monthLabel' => $startDate->translatedFormat('F Y'),
            'userName' => $user->name ?? $user->email ?? ('#' . $user->id),
            'totalVisits' => $totalVisits,
        ];
    }
}
