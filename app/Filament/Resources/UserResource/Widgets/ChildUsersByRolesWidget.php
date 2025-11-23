<?php

namespace App\Filament\Resources\UserResource\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChildUsersByRolesWidget extends BaseWidget
{
    public ?User $record = null;

    protected function getStats(): array
    {
        // Get the user record being viewed
        $user = $this->record;
        
        // If no record is set, don't show any stats
        if (!$user) {
            return [];
        }
        
        // Check if current authenticated user can view this data
        /** @var \App\Models\User|null $currentUser */
        $currentUser = Auth::user();
        if (!$currentUser) {
            return [];
        }
        
        // Super admins can see all data, others can only see their own or their children's data
        if (!$currentUser->hasRole('Super-Admin')) {
            $canView = $currentUser->id === $user->id || 
                      $currentUser->id === $user->parent_id ||
                      $user->parent_id === $currentUser->id;
            
            if (!$canView) {
                return [];
            }
        }

        // Get child users query for the specific user being viewed
        $childUsersQuery = User::where('parent_id', $user->id);
        $totalChildren = $childUsersQuery->count();

        // If no children, show a simple message
        if ($totalChildren === 0) {
            return [
                Stat::make('Подчиненных пользователей', '0')
                    ->description('У вас пока нет подчиненных')
                    ->descriptionIcon('heroicon-m-user-group')
                    ->color('gray'),
            ];
        }

        // Get child users count by role for the specific user being viewed
        $roleStats = DB::table('users')
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('model_has_roles.model_type', User::class)
            ->where('users.parent_id', $user->id)
            ->select('roles.name', DB::raw('count(*) as count'))
            ->groupBy('roles.name')
            ->pluck('count', 'name')
            ->toArray();

        // Calculate specific role counts
        $managersCount = $roleStats['Manager'] ?? 0;
        $ropCount = $roleStats['ROP'] ?? 0;
        
        // Combine all dealer types
        $dealersCount = ($roleStats['Dealer'] ?? 0) + 
                       ($roleStats['Dealer-Ch'] ?? 0) + 
                       ($roleStats['Dealer-R'] ?? 0);
        
        $workmenCount = $roleStats['Workman'] ?? 0;
        $operatorsCount = $roleStats['Operator'] ?? 0;

        // Build stats array
        $stats = [];

        // Total children stat
        $stats[] = Stat::make('Всего подчиненных', number_format($totalChildren))
            ->description($this->getTotalDescription($totalChildren))
            ->descriptionIcon('heroicon-m-user-group')
            ->color('primary')
            ->chart($this->getMonthlyChildRegistrationData($user->id));

        // Managers stat (combining Manager and ROP)
        if ($managersCount > 0 || $ropCount > 0) {
            $totalManagers = $managersCount + $ropCount;
            $description = [];
            if ($managersCount > 0) {
                $description[] = "Менеджеры: {$managersCount}";
            }
            if ($ropCount > 0) {
                $description[] = "РОП: {$ropCount}";
            }
            
            $stats[] = Stat::make('Менеджеры', number_format($totalManagers))
                ->description(implode(' | ', $description))
                ->descriptionIcon('heroicon-m-user-circle')
                ->color('success')
                ->chart($this->getRoleChartData($user->id, ['Manager', 'ROP']));
        }

        // Dealers stat (all dealer types combined)
        if ($dealersCount > 0) {
            $dealerDetails = [];
            if (isset($roleStats['Dealer'])) {
                $dealerDetails[] = "Базовые: {$roleStats['Dealer']}";
            }
            if (isset($roleStats['Dealer-Ch'])) {
                $dealerDetails[] = "Дилер Ч: {$roleStats['Dealer-Ch']}";
            }
            if (isset($roleStats['Dealer-R'])) {
                $dealerDetails[] = "Дилер Р: {$roleStats['Dealer-R']}";
            }
            
            $stats[] = Stat::make('Дилеры', number_format($dealersCount))
                ->description(implode(' | ', $dealerDetails))
                ->descriptionIcon('heroicon-m-building-office')
                ->color('info')
                ->chart($this->getRoleChartData($user->id, ['Dealer', 'Dealer-Ch', 'Dealer-R']));
        }

        // Workmen stat
        if ($workmenCount > 0) {
            $stats[] = Stat::make('Рабочие', number_format($workmenCount))
                ->description('Исполнители заказов')
                ->descriptionIcon('heroicon-m-wrench-screwdriver')
                ->color('warning')
                ->chart($this->getRoleChartData($user->id, ['Workman']));
        }

        // Operators stat (if any)
        if ($operatorsCount > 0) {
            $stats[] = Stat::make('Операторы', number_format($operatorsCount))
                ->description('Системные операторы')
                ->descriptionIcon('heroicon-m-shield-check')
                ->color('danger')
                ->chart($this->getRoleChartData($user->id, ['Operator']));
        }

        return $stats;
    }

    /**
     * Get description for total children count
     */
    private function getTotalDescription(int $total): string
    {
        if ($total === 1) {
            return '1 пользователь';
        } elseif ($total >= 2 && $total <= 4) {
            return "{$total} пользователя";
        } else {
            return "{$total} пользователей";
        }
    }

    /**
     * Get monthly child user registration data for chart (last 6 months)
     */
    private function getMonthlyChildRegistrationData(int $parentId): array
    {
        $data = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $count = User::where('parent_id', $parentId)
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
            
            $data[] = $count;
        }
        
        return $data;
    }

    /**
     * Get chart data for specific roles (last 6 months)
     */
    private function getRoleChartData(int $parentId, array $roleNames): array
    {
        $data = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $count = User::where('parent_id', $parentId)
                ->whereHas('roles', function ($query) use ($roleNames) {
                    $query->whereIn('name', $roleNames);
                })
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
            
            $data[] = $count;
        }
        
        return $data;
    }
}

