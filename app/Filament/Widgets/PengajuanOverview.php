<?php

namespace App\Filament\Widgets;

use App\Models\Pengajuan;
use App\Models\Approval;
use App\Models\User;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class PengajuanOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Pengajuan::query()
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pegawai')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('kegiatan')
                    ->label('Kegiatan')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('jadwal_mulai')
                    ->label('Jadwal Mulai')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(function (int $state) {
                        return match ($state) {
                            Pengajuan::STATUS_PENDING_MANAGER => 'Menunggu: Persetujuan Manager',
                            Pengajuan::STATUS_PENDING_KADEP => 'Menunggu: Persetujuan Kepala Dept.',
                            Pengajuan::STATUS_PENDING_HRD => 'Menunggu: Persetujuan HRD/Direktur',
                            Pengajuan::STATUS_DISETUJUI => 'Disetujui',
                            Pengajuan::STATUS_DITOLAK => 'Ditolak',
                            default => 'Status Tidak Diketahui',
                        };
                    })
                    ->color(function (int $state) {
                        return match ($state) {
                            Pengajuan::STATUS_PENDING_MANAGER, 
                            Pengajuan::STATUS_PENDING_KADEP, 
                            Pengajuan::STATUS_PENDING_HRD => 'warning',
                            Pengajuan::STATUS_DISETUJUI => 'success',
                            Pengajuan::STATUS_DITOLAK => 'danger',
                            default => 'gray',
                        };
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Diajukan pada')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                // No filters for the widget
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn (Pengajuan $record): string => route('filament.admin.resources.pengajuans.view', $record)),
                Tables\Actions\Action::make('approve')
                    ->label('Setujui')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->visible(function (Pengajuan $record) {
                        /** @var User $user */
                        $user = Auth::user();
                        
                        // Check if the user can approve based on role and application status
                        if ($user->hasRole('manager') && $record->status === Pengajuan::STATUS_PENDING_MANAGER) {
                            return true;
                        }
                        
                        if ($user->hasRole('kepala_departemen') && $record->status === Pengajuan::STATUS_PENDING_KADEP) {
                            return true;
                        }
                        
                        if (($user->hasRole('hrd') || $user->hasRole('direktur')) && 
                            $record->status === Pengajuan::STATUS_PENDING_HRD) {
                            return true;
                        }
                        
                        return false;
                    })
                    ->form([
                        Forms\Components\Textarea::make('comments')
                            ->label('Catatan (Opsional)')
                            ->maxLength(255),
                    ])
                    ->action(function (array $data, Pengajuan $record) {
                        /** @var User $user */
                        $user = Auth::user();
                        $currentLevel = $record->getCurrentLevel();
                        
                        // Create approval record
                        Approval::create([
                            'pengajuan_id' => $record->id,
                            'approver_id' => $user->id,
                            'level' => $currentLevel,
                            'status' => 'disetujui',
                            'decided_at' => now(),
                            'comments' => $data['comments'] ?? null,
                        ]);
                        
                        // Update application status
                        if ($currentLevel === 1) {
                            $record->status = Pengajuan::STATUS_PENDING_KADEP;
                        } elseif ($currentLevel === 2) {
                            $record->status = Pengajuan::STATUS_PENDING_HRD;
                        } elseif ($currentLevel === 3) {
                            $record->status = Pengajuan::STATUS_DISETUJUI;
                        }
                        
                        $record->save();
                        
                        Notification::make()
                            ->success()
                            ->title('Pengajuan Disetujui')
                            ->send();
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->color('danger')
                    ->icon('heroicon-o-x-mark')
                    ->visible(function (Pengajuan $record) {
                        /** @var User $user */
                        $user = Auth::user();
                        
                        // Check if the user can reject based on role and application status
                        if ($user->hasRole('manager') && $record->status === Pengajuan::STATUS_PENDING_MANAGER) {
                            return true;
                        }
                        
                        if ($user->hasRole('kepala_departemen') && $record->status === Pengajuan::STATUS_PENDING_KADEP) {
                            return true;
                        }
                        
                        if (($user->hasRole('hrd') || $user->hasRole('direktur')) && 
                            $record->status === Pengajuan::STATUS_PENDING_HRD) {
                            return true;
                        }
                        
                        return false;
                    })
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('comments')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->action(function (array $data, Pengajuan $record) {
                        /** @var User $user */
                        $user = Auth::user();
                        $currentLevel = $record->getCurrentLevel();
                        
                        // Create rejection record
                        Approval::create([
                            'pengajuan_id' => $record->id,
                            'approver_id' => $user->id,
                            'level' => $currentLevel,
                            'status' => 'ditolak',
                            'decided_at' => now(),
                            'comments' => $data['comments'],
                        ]);
                        
                        $record->status = Pengajuan::STATUS_DITOLAK;
                        $record->save();
                        
                        Notification::make()
                            ->success()
                            ->title('Pengajuan Ditolak')
                            ->send();
                    }),
            ])
            ->heading('Pengajuan Terbaru');
    }
} 