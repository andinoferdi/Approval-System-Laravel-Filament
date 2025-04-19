<?php

namespace App\Filament\Resources\PengajuanResource\Pages;

use App\Filament\Resources\PengajuanResource;
use App\Models\Pengajuan;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class CreatePengajuan extends CreateRecord
{
    protected static string $resource = PengajuanResource::class;
    
    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            $this->getCancelFormAction(),
        ];
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        /** @var User $user */
        $user = Auth::user();
        
        // Always set the user_id to the current user
        $data['user_id'] = $user->id;
        
        $data['status'] = Pengajuan::STATUS_PENDING_MANAGER;
        
        return $data;
    }
    
    protected function afterCreate(): void
    {
        // Menampilkan notifikasi berhasil
        Notification::make()
            ->title('Pengajuan Berhasil Dibuat')
            ->success()
            ->send();
    }
}
