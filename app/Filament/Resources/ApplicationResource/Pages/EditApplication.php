<?php

namespace App\Filament\Resources\ApplicationResource\Pages;

use App\Filament\Resources\ApplicationResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditApplication extends EditRecord
{
    protected static string $resource = ApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(function () {
                    $application = $this->getRecord();
                    /** @var User $user */
                    $user = Auth::user();
                    
                    // Only employee who created the application can delete it
                    return $user && $user->hasRole('employee') && $application->user_id === $user->id;
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
}
