<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class UsersByRolesWidget extends BaseWidget
{
    /**
     * Check if current user is Super Admin
     */
    protected function isSuperAdmin(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        return $user && $user->hasRole('Super-Admin');
    }

    protected function getStats(): array
    {
        // Only show stats to Super Admin
        if (!$this->isSuperAdmin()) {
            return [];
        }

        // Get total users count
        $totalUsers = User::count();

        // Get users count by role
        $roleStats = DB::table('users')
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('model_has_roles.model_type', User::class)
            ->select('roles.name', DB::raw('count(*) as count'))
            ->groupBy('roles.name')
            ->pluck('count', 'name')
            ->toArray();

        // Get users without roles
        $usersWithoutRoles = User::whereDoesntHave('roles')->count();

        // Get recent user registrations (last 30 days)
        $recentUsers = User::where('created_at', '>=', now()->subDays(30))->count();

        // Get active users (users with orders in last 90 days)
        $activeUsers = User::whereHas('orders', function ($query) {
            $query->where('created_at', '>=', now()->subDays(90));
        })->count();

        // Role-specific statistics
        $superAdmins = $roleStats['Super-Admin'] ?? 0;
        $operators = $roleStats['Operator'] ?? 0;
        $managers = $roleStats['Manager'] ?? 0;
        $agents = $roleStats['Agent'] ?? 0;
        $dealers = $roleStats['Dealer'] ?? 0;
        $workmen = $roleStats['Workman'] ?? 0;

        // Get monthly user registration data for chart
        $monthlyRegistrationData = $this->getMonthlyRegistrationData();

        return [
            Stat::make('Всего пользователей', number_format($totalUsers))
                ->description("Новых за месяц: {$recentUsers}")
                ->descriptionIcon('heroicon-m-users')
                ->color('primary')
                ->chart($monthlyRegistrationData),

            Stat::make('Активные пользователи', number_format($activeUsers))
                ->description($this->getActivityDescription($activeUsers, $totalUsers))
                ->descriptionIcon('heroicon-m-bolt')
                ->color('success'),

            Stat::make('Администраторы', number_format($superAdmins + $operators))
                ->description("Супер-админы: {$superAdmins} | Операторы: {$operators}")
                ->descriptionIcon('heroicon-m-shield-check')
                ->color('danger'),

            Stat::make('Менеджеры и агенты', number_format($managers + $agents))
                ->description("Менеджеры: {$managers} | Агенты: {$agents}")
                ->descriptionIcon('heroicon-m-user-group')
                ->color('warning'),

            Stat::make('Дилеры', number_format($dealers))
                ->description($dealers > 0 ? 'Основные клиенты' : 'Нет дилеров')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('info'),

            Stat::make('Рабочие', number_format($workmen))
                ->description($workmen > 0 ? 'Исполнители заказов' : 'Нет рабочих')
                ->descriptionIcon('heroicon-m-wrench-screwdriver')
                ->color('gray'),
        ];
    }

    /**
     * Get activity description based on active users ratio
     */
    private function getActivityDescription(int $activeUsers, int $totalUsers): string
    {
        if ($totalUsers === 0) {
            return 'Нет пользователей';
        }

        $activityRate = ($activeUsers / $totalUsers) * 100;

        if ($activityRate >= 80) {
            return 'Высокая активность (' . number_format($activityRate, 1) . '%)';
        } elseif ($activityRate >= 50) {
            return 'Средняя активность (' . number_format($activityRate, 1) . '%)';
        } else {
            return 'Низкая активность (' . number_format($activityRate, 1) . '%)';
        }
    }

    /**
     * Get monthly user registration data for chart (last 12 months)
     */
    private function getMonthlyRegistrationData(): array
    {
        $data = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $count = User::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
            
            $data[] = $count;
        }
        
        return $data;
    }
} 