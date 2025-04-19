<?php

namespace App\Filament\Resources\PengajuanResource\Pages;

use App\Filament\Resources\PengajuanResource;
use App\Models\Pengajuan;
use App\Models\Approval;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewPengajuan extends ViewRecord
{
    protected static string $resource = PengajuanResource::class;
    
    public function getHeading(): string
    {
        /** @var Pengajuan $pengajuan */
        $pengajuan = $this->getRecord();
        
        return 'Pengajuan #' . $pengajuan->id;
    }
    
    protected function mutateFormData(array $data): array
    {
        $application = $this->getRecord();
        
        // Add user information to the form
        $data['user_name'] = $application->user->name ?? '';
        $data['user_position'] = $application->user->position ?? '';
        $data['user_department'] = $application->user->department ?? '';
        
        return $data;
    }
    
    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(function () {
                    $application = $this->getRecord();
                    /** @var User $user */
                    $user = Auth::user();
                    // Only employee can edit their own applications if status is pending_manager
                    return $user && 
                           $user->hasRole('pegawai') && 
                           $application->user_id === $user->id && 
                           $application->status === Pengajuan::STATUS_PENDING_MANAGER;
                }),
                
            Action::make('delete')
                ->label('Hapus')
                ->color('danger')
                ->icon('heroicon-o-trash')
                ->action(fn () => $this->record->delete())
                ->requiresConfirmation()
                ->visible(function () {
                    $application = $this->getRecord();
                    /** @var User $user */
                    $user = Auth::user();
                    
                    // Employee can delete their own applications if status is pending_manager or rejected
                    return $user && 
                           $user->hasRole('pegawai') && 
                           $application->user_id === $user->id &&
                           ($application->status === Pengajuan::STATUS_PENDING_MANAGER || 
                            $application->status === Pengajuan::STATUS_DITOLAK);
                }),
                
            Action::make('approve')
                ->label('Setujui')
                ->color('success')
                ->icon('heroicon-o-check')
                ->visible(function () {
                    $application = $this->getRecord();
                    /** @var User $user */
                    $user = Auth::user();
                    
                    if (!$user) {
                        return false;
                    }
                    
                    // Check if the user can approve based on role and application status
                    if ($user->hasRole('manager') && $application->status === Pengajuan::STATUS_PENDING_MANAGER) {
                        return true;
                    }
                    
                    if ($user->hasRole('kepala_departemen') && $application->status === Pengajuan::STATUS_PENDING_KADEP) {
                        return true;
                    }
                    
                    if (($user->hasRole('hrd') || $user->hasRole('direktur')) && 
                        $application->status === Pengajuan::STATUS_PENDING_HRD) {
                        return true;
                    }
                    
                    return false;
                })
                ->form([
                    Forms\Components\Textarea::make('comments')
                        ->label('Catatan (Opsional)')
                        ->maxLength(255),
                ])
                ->action(function (array $data) {
                    $application = $this->getRecord();
                    /** @var User $user */
                    $user = Auth::user();
                    $currentLevel = $application->getCurrentLevel();
                    
                    // Create approval record
                    Approval::create([
                        'pengajuan_id' => $application->id,
                        'approver_id' => $user->id,
                        'level' => $currentLevel,
                        'status' => 'disetujui',
                        'decided_at' => now(),
                        'comments' => $data['comments'] ?? null,
                    ]);
                    
                    // Update application status
                    if ($currentLevel === 1) {
                        $application->status = Pengajuan::STATUS_PENDING_KADEP;
                    } elseif ($currentLevel === 2) {
                        $application->status = Pengajuan::STATUS_PENDING_HRD;
                    } elseif ($currentLevel === 3) {
                        $application->status = Pengajuan::STATUS_DISETUJUI;
                    }
                    
                    $application->save();
                    
                    Notification::make()
                        ->success()
                        ->title('Pengajuan Disetujui')
                        ->send();
                }),
                
            Action::make('reject')
                ->label('Tolak')
                ->color('danger')
                ->icon('heroicon-o-x-mark')
                ->visible(function () {
                    $application = $this->getRecord();
                    /** @var User $user */
                    $user = Auth::user();
                    
                    if (!$user) {
                        return false;
                    }
                    
                    // Check if the user can reject based on role and application status
                    if ($user->hasRole('manager') && $application->status === Pengajuan::STATUS_PENDING_MANAGER) {
                        return true;
                    }
                    
                    if ($user->hasRole('kepala_departemen') && $application->status === Pengajuan::STATUS_PENDING_KADEP) {
                        return true;
                    }
                    
                    if (($user->hasRole('hrd') || $user->hasRole('direktur')) && 
                        $application->status === Pengajuan::STATUS_PENDING_HRD) {
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
                ->action(function (array $data) {
                    $application = $this->getRecord();
                    /** @var User $user */
                    $user = Auth::user();
                    $currentLevel = $application->getCurrentLevel();
                    
                    // Create rejection record
                    Approval::create([
                        'pengajuan_id' => $application->id,
                        'approver_id' => $user->id,
                        'level' => $currentLevel,
                        'status' => 'ditolak',
                        'decided_at' => now(),
                        'comments' => $data['comments'],
                    ]);
                    
                    $application->status = Pengajuan::STATUS_DITOLAK;
                    $application->save();
                    
                    Notification::make()
                        ->success()
                        ->title('Pengajuan Ditolak')
                        ->send();
                }),
        ];
    }
} 