<?php

namespace App\Filament\Resources\ApplicationResource\Pages;

use App\Filament\Resources\ApplicationResource;
use App\Models\Application;
use App\Models\Approval;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewApplication extends ViewRecord
{
    protected static string $resource = ApplicationResource::class;
    
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
                    // Only employee can edit their own applications
                    return $user && $user->hasRole('employee') && $application->user_id === $user->id;
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
                    if ($user->hasRole('direct_manager') && $application->status === Application::STATUS_PENDING_MANAGER) {
                        return true;
                    }
                    
                    if ($user->hasRole('dept_head') && $application->status === Application::STATUS_PENDING_DEPT_HEAD) {
                        return true;
                    }
                    
                    if (($user->hasRole('hrd') || $user->hasRole('director')) && 
                        $application->status === Application::STATUS_PENDING_HRD) {
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
                        'application_id' => $application->id,
                        'approver_id' => $user->id,
                        'level' => $currentLevel,
                        'status' => 'approved',
                        'decided_at' => now(),
                        'comments' => $data['comments'] ?? null,
                    ]);
                    
                    // Update application status
                    if ($currentLevel === 1) {
                        $application->status = Application::STATUS_PENDING_DEPT_HEAD;
                    } elseif ($currentLevel === 2) {
                        $application->status = Application::STATUS_PENDING_HRD;
                    } elseif ($currentLevel === 3) {
                        $application->status = Application::STATUS_APPROVED;
                    }
                    
                    $application->save();
                    
                    Notification::make()
                        ->success()
                        ->title('Pengajuan disetujui')
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
                    if ($user->hasRole('direct_manager') && $application->status === Application::STATUS_PENDING_MANAGER) {
                        return true;
                    }
                    
                    if ($user->hasRole('dept_head') && $application->status === Application::STATUS_PENDING_DEPT_HEAD) {
                        return true;
                    }
                    
                    if (($user->hasRole('hrd') || $user->hasRole('director')) && 
                        $application->status === Application::STATUS_PENDING_HRD) {
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
                        'application_id' => $application->id,
                        'approver_id' => $user->id,
                        'level' => $currentLevel,
                        'status' => 'rejected',
                        'decided_at' => now(),
                        'comments' => $data['comments'],
                    ]);
                    
                    // Update application status to rejected
                    $application->status = Application::STATUS_REJECTED;
                    $application->save();
                    
                    Notification::make()
                        ->success()
                        ->title('Pengajuan ditolak')
                        ->send();
                }),
        ];
    }
} 