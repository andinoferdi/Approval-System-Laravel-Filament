<?php

namespace App\Filament\Resources\ApplicationResource\Pages;

use App\Filament\Resources\ApplicationResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListApplications extends ListRecords
{
    protected static string $resource = ApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(function () {
                    /** @var User $user */
                    $user = Auth::user();
                    // Only employees and administrators can create applications
                    return $user && ($user->hasRole('employee') || $user->hasRole('administrator'));
                }),
        ];
    }
}
