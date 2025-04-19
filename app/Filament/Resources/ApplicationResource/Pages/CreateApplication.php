<?php

namespace App\Filament\Resources\ApplicationResource\Pages;

use App\Filament\Resources\ApplicationResource;
use App\Models\Application;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class CreateApplication extends CreateRecord
{
    protected static string $resource = ApplicationResource::class;
    
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
        
        // If the user is an employee, force the user_id to be the current user
        if ($user && $user->hasRole('employee')) {
            $data['user_id'] = $user->id;
        }
        
        // Always set initial status to pending manager approval
        $data['status'] = Application::STATUS_PENDING_MANAGER;
        
        return $data;
    }
    
    protected function afterCreate(): void
    {
        // Menampilkan notifikasi berhasil
        Notification::make()
            ->title('Application created successfully')
            ->success()
            ->send();
    }
}
