<?php

namespace App\Filament\Widgets;

use App\Models\Application;
use App\Models\Approval;
use App\Models\User;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class ApplicationsOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Application::query()
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Employee')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('event_name')
                    ->label('Event')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(function (int $state) {
                        return match ($state) {
                            Application::STATUS_PENDING_MANAGER => 'Menunggu: Persetujuan Manager',
                            Application::STATUS_PENDING_DEPT_HEAD => 'Menunggu: Persetujuan Kepala Dept.',
                            Application::STATUS_PENDING_HRD => 'Menunggu: Persetujuan HRD/Direktur',
                            Application::STATUS_APPROVED => 'Disetujui',
                            Application::STATUS_REJECTED => 'Ditolak',
                            default => 'Status Tidak Diketahui',
                        };
                    })
                    ->color(function (int $state) {
                        return match ($state) {
                            Application::STATUS_PENDING_MANAGER, 
                            Application::STATUS_PENDING_DEPT_HEAD, 
                            Application::STATUS_PENDING_HRD => 'warning',
                            Application::STATUS_APPROVED => 'success',
                            Application::STATUS_REJECTED => 'danger',
                            default => 'gray',
                        };
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Submitted at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                // No filters for the widget
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn (Application $record): string => route('filament.admin.resources.applications.view', $record)),
                Tables\Actions\Action::make('approve')
                    ->label('Setujui')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->visible(function (Application $record) {
                        /** @var User $user */
                        $user = Auth::user();
                        
                        // Check if the user can approve based on role and application status
                        if ($user->hasRole('direct_manager') && $record->status === Application::STATUS_PENDING_MANAGER) {
                            return true;
                        }
                        
                        if ($user->hasRole('dept_head') && $record->status === Application::STATUS_PENDING_DEPT_HEAD) {
                            return true;
                        }
                        
                        if (($user->hasRole('hrd') || $user->hasRole('director')) && 
                            $record->status === Application::STATUS_PENDING_HRD) {
                            return true;
                        }
                        
                        return false;
                    })
                    ->form([
                        Forms\Components\Textarea::make('comments')
                            ->label('Catatan (Opsional)')
                            ->maxLength(255),
                    ])
                    ->action(function (array $data, Application $record) {
                        /** @var User $user */
                        $user = Auth::user();
                        $currentLevel = $record->getCurrentLevel();
                        
                        // Create approval record
                        Approval::create([
                            'application_id' => $record->id,
                            'approver_id' => $user->id,
                            'level' => $currentLevel,
                            'status' => 'approved',
                            'decided_at' => now(),
                            'comments' => $data['comments'] ?? null,
                        ]);
                        
                        // Update application status
                        if ($currentLevel === 1) {
                            $record->status = Application::STATUS_PENDING_DEPT_HEAD;
                        } elseif ($currentLevel === 2) {
                            $record->status = Application::STATUS_PENDING_HRD;
                        } elseif ($currentLevel === 3) {
                            $record->status = Application::STATUS_APPROVED;
                        }
                        
                        $record->save();
                        
                        Notification::make()
                            ->success()
                            ->title('Pengajuan disetujui')
                            ->send();
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->color('danger')
                    ->icon('heroicon-o-x-mark')
                    ->visible(function (Application $record) {
                        /** @var User $user */
                        $user = Auth::user();
                        
                        // Check if the user can reject based on role and application status
                        if ($user->hasRole('direct_manager') && $record->status === Application::STATUS_PENDING_MANAGER) {
                            return true;
                        }
                        
                        if ($user->hasRole('dept_head') && $record->status === Application::STATUS_PENDING_DEPT_HEAD) {
                            return true;
                        }
                        
                        if (($user->hasRole('hrd') || $user->hasRole('director')) && 
                            $record->status === Application::STATUS_PENDING_HRD) {
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
                    ->action(function (array $data, Application $record) {
                        /** @var User $user */
                        $user = Auth::user();
                        $currentLevel = $record->getCurrentLevel();
                        
                        // Create rejection record
                        Approval::create([
                            'application_id' => $record->id,
                            'approver_id' => $user->id,
                            'level' => $currentLevel,
                            'status' => 'rejected',
                            'decided_at' => now(),
                            'comments' => $data['comments'],
                        ]);
                        
                        // Update application status to rejected
                        $record->status = Application::STATUS_REJECTED;
                        $record->save();
                        
                        Notification::make()
                            ->success()
                            ->title('Pengajuan ditolak')
                            ->send();
                    }),
            ])
            ->heading('Recent Applications');
    }
}
