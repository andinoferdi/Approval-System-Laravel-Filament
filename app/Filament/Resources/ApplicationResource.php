<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApplicationResource\Pages;
use App\Filament\Resources\ApplicationResource\RelationManagers;
use App\Models\Application;
use App\Models\Approval;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ApplicationResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    
    protected static ?string $navigationGroup = 'Application Management';
    
    protected static ?string $modelLabel = 'Pengajuan';
    
    protected static ?string $pluralModelLabel = 'Pengajuan';
    
    protected static ?string $navigationLabel = 'Pengajuan';

    public static function form(Form $form): Form
    {
        /** @var User $user */
        $user = Auth::user();
        $isEmployee = $user->hasRole('employee');
        $isCreate = $form->getOperation() === 'create';
        $record = $form->getRecord();

        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pegawai')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Pegawai')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->required()
                            ->default(function () use ($isEmployee) {
                                return $isEmployee ? Auth::id() : null;
                            })
                            ->disabled($isEmployee)
                            ->hiddenOn('edit')
                            ->visible(fn() => $isCreate),
                            
                        Forms\Components\Placeholder::make('user_name_display')
                            ->label('Nama Pegawai')
                            ->content(fn (Application $record) => $record?->user?->name ?? '-')
                            ->visible(fn() => !$isCreate),
                            
                        Forms\Components\Placeholder::make('user_position_display')
                            ->label('Posisi')
                            ->content(fn (Application $record) => $record?->user?->position ?? '-')
                            ->visible(fn() => !$isCreate),
                            
                        Forms\Components\Placeholder::make('user_department_display')
                            ->label('Divisi/Departemen')
                            ->content(fn (Application $record) => $record?->user?->department ?? '-')
                            ->visible(fn() => !$isCreate),
                    ])->columns(2),
                
                Forms\Components\Section::make('Informasi Kegiatan')
                    ->schema([
                        Forms\Components\TextInput::make('event_name')
                            ->label('Nama Kegiatan')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\DateTimePicker::make('start_date')
                            ->label('Tanggal Mulai')
                            ->required(),
                        Forms\Components\DateTimePicker::make('end_date')
                            ->label('Tanggal Selesai')
                            ->required(),
                        Forms\Components\FileUpload::make('document_path')
                            ->label('Dokumen Pendukung')
                            ->disk('public')
                            ->directory('documents')
                            ->visibility('public')
                            ->previewable(true)
                            ->multiple(false)
                            ->maxSize(5120)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'application/pdf']),
                    ])->columns(2),
                
                Forms\Components\Hidden::make('status')
                    ->default(Application::STATUS_PENDING_MANAGER)
                    ->visible($isEmployee && $isCreate),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama Pegawai')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.position')
                    ->label('Posisi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.department')
                    ->label('Divisi/Departemen')
                    ->searchable(),
                Tables\Columns\TextColumn::make('event_name')
                    ->label('Kegiatan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Tanggal Mulai')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Tanggal Selesai')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
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
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(function (Application $record) {
                        /** @var User $user */
                        $user = Auth::user();
                        // Only employee can edit their own applications
                        return $user->hasRole('employee') && $record->user_id === $user->id;
                    }),
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
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(function () {
                            /** @var User $user */
                            $user = Auth::user();
                            return $user->hasRole('administrator');
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ApprovalsRelationManager::make(),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListApplications::route('/'),
            'create' => Pages\CreateApplication::route('/create'),
            'view' => Pages\ViewApplication::route('/{record}'),
            'edit' => Pages\EditApplication::route('/{record}/edit'),
        ];
    }
    
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        /** @var User $user */
        $user = Auth::user();
        
        // Administrator can see all applications
        if ($user->hasRole('administrator')) {
            return $query;
        }
        
        // Employees can only see their own applications
        if ($user->hasRole('employee')) {
            return $query->where('user_id', $user->id);
        }
        
        // Direct managers can see all applications
        if ($user->hasRole('direct_manager')) {
            return $query;
        }
        
        // Department heads can see applications from their department
        if ($user->hasRole('dept_head')) {
            return $query->whereHas('user', function ($subquery) use ($user) {
                $subquery->where('department', $user->department);
            });
        }
        
        // HRD and Directors can see all applications
        if ($user->hasRole('hrd') || $user->hasRole('director')) {
            return $query;
        }
        
        return $query;
    }
}
