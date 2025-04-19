<?php

namespace App\Filament\Widgets;

use App\Models\Application;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ApplicationStatsOverview extends BaseWidget
{
    protected static ?int $sort = 0;
    
    protected function getStats(): array
    {
        return [
            Stat::make('Total Applications', Application::count())
                ->description('All applications in the system')
                ->color('primary')
                ->chart([7, 2, 10, 3, 15, 4, 17]),
                
            Stat::make('Pending Applications', Application::whereIn('status', [
                    Application::STATUS_PENDING_MANAGER,
                    Application::STATUS_PENDING_DEPT_HEAD,
                    Application::STATUS_PENDING_HRD,
                ])->count())
                ->description('Applications waiting for approval')
                ->color('warning')
                ->chart([3, 5, 7, 4, 8, 2, 6]),
                
            Stat::make('Approved Applications', Application::where('status', Application::STATUS_APPROVED)->count())
                ->description('Applications fully approved')
                ->color('success')
                ->chart([2, 3, 5, 8, 4, 6, 5]),
                
            Stat::make('Rejected Applications', Application::where('status', Application::STATUS_REJECTED)->count())
                ->description('Applications that were rejected')
                ->color('danger')
                ->chart([1, 2, 0, 3, 1, 4, 2]),
                
            Stat::make('Registered Users', User::count())
                ->description('Total users in the system')
                ->color('info'),
        ];
    }
} 