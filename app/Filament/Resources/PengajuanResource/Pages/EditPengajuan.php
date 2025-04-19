<?php

namespace App\Filament\Resources\PengajuanResource\Pages;

use App\Filament\Resources\PengajuanResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use App\Models\Pengajuan;

class EditPengajuan extends EditRecord
{
    protected static string $resource = PengajuanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
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
        ];
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
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
    
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Prevent changing the status field through the form
        unset($data['status']);
        
        return $data;
    }

    protected function canEdit(): bool
    {
        $application = $this->getRecord();
        /** @var User $user */
        $user = Auth::user();
        
        // Only employee can edit their own applications if status is pending_manager
        return $user && 
               $user->hasRole('pegawai') && 
               $application->user_id === $user->id &&
               $application->status === Pengajuan::STATUS_PENDING_MANAGER;
    }
}
