<?php

namespace App\Filament\Widgets;

use App\Models\Pengajuan;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PengajuanStatsOverview extends BaseWidget
{
    protected static ?int $sort = 0;
    
    protected function getStats(): array
    {
        return [
            Stat::make('Total Pengajuan', Pengajuan::count())
                ->description('Semua pengajuan dalam sistem')
                ->color('primary')
                ->chart([7, 2, 10, 3, 15, 4, 17]),
                
            Stat::make('Pengajuan Tertunda', Pengajuan::whereIn('status', [
                    Pengajuan::STATUS_PENDING_MANAGER,
                    Pengajuan::STATUS_PENDING_KADEP,
                    Pengajuan::STATUS_PENDING_HRD,
                ])->count())
                ->description('Pengajuan menunggu persetujuan')
                ->color('warning')
                ->chart([3, 5, 7, 4, 8, 2, 6]),
                
            Stat::make('Pengajuan disetujui', Pengajuan::where('status', Pengajuan::STATUS_DISETUJUI)->count())
                ->description('Pengajuan yang disetujui')
                ->color('success')
                ->chart([2, 3, 5, 8, 4, 6, 5]),
                
            Stat::make('Pengajuan ditolak', Pengajuan::where('status', Pengajuan::STATUS_DITOLAK)->count())
                ->description('Pengajuan yang ditolak')
                ->color('danger')
                ->chart([1, 2, 0, 3, 1, 4, 2]),
                
            Stat::make('Pengguna Terdaftar', User::count())
                ->description('Total pengguna dalam sistem')
                ->color('info'),
        ];
    }
} 