<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengajuanResource\Pages;
use App\Filament\Resources\PengajuanResource\RelationManagers;
use App\Models\Pengajuan;
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

class PengajuanResource extends Resource
{
    protected static ?string $model = Pengajuan::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    
    protected static ?string $navigationGroup = 'Manajemen Pengajuan';
    
    protected static ?string $modelLabel = 'Pengajuan';
    
    protected static ?string $pluralModelLabel = 'Pengajuan';
    
    protected static ?string $navigationLabel = 'Pengajuan';

    public static function form(Form $form): Form
    {
        /** @var User $user */
        $user = Auth::user();
        $isEmployee = $user->hasRole('pegawai');
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
                            ->default(Auth::id())
                            ->disabled($isEmployee)
                            ->hiddenOn('edit')
                            ->visible(fn() => $isCreate),
                            
                        Forms\Components\Placeholder::make('user_name_display')
                            ->label('Nama Pegawai')
                            ->content(fn (Pengajuan $record) => $record?->user?->name ?? '-')
                            ->visible(fn() => !$isCreate),
                            
                        Forms\Components\Placeholder::make('user_position_display')
                            ->label('Posisi')
                            ->content(fn (Pengajuan $record) => $record?->user?->position ?? '-')
                            ->visible(fn() => !$isCreate),
                            
                        Forms\Components\Placeholder::make('user_department_display')
                            ->label('Divisi/Departemen')
                            ->content(fn (Pengajuan $record) => $record?->user?->department ?? '-')
                            ->visible(fn() => !$isCreate),
                    ])->columns(2),
                
                Forms\Components\Section::make('Informasi Kegiatan')
                    ->schema([
                        Forms\Components\TextInput::make('kegiatan')
                            ->label('Nama Kegiatan')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\DateTimePicker::make('jadwal_mulai')
                            ->label('jadwal Mulai')
                            ->required(),
                        Forms\Components\DateTimePicker::make('jadwal_akhir')
                            ->label('jadwal Selesai')
                            ->required(),
                        FileUpload::make('dokumen_pendukung')
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
                    ->default(Pengajuan::STATUS_PENDING_MANAGER)
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
                Tables\Columns\TextColumn::make('kegiatan')
                    ->label('Kegiatan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('jadwal_mulai')
                    ->label('jadwal Mulai')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('jadwal_akhir')
                    ->label('jadwal Selesai')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
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
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(function (Pengajuan $record) {
                        /** @var User $user */
                        $user = Auth::user();
                        // Only employee can edit their own applications if status is pending_manager
                        return $user->hasRole('pegawai') && 
                               $record->user_id === $user->id && 
                               $record->status === Pengajuan::STATUS_PENDING_MANAGER;
                    }),
                Tables\Actions\DeleteAction::make()
                    ->visible(function (Pengajuan $record) {
                        /** @var User $user */
                        $user = Auth::user();
                        // Employee can delete their own applications if status is pending_manager or rejected
                        return $user->hasRole('pegawai') && 
                               $record->user_id === $user->id &&
                               ($record->status === Pengajuan::STATUS_PENDING_MANAGER || 
                                $record->status === Pengajuan::STATUS_DITOLAK);
                    }),
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
            'index' => Pages\ListPengajuan::route('/'),
            'create' => Pages\CreatePengajuan::route('/create'),
            'view' => Pages\ViewPengajuan::route('/{record}'),
            'edit' => Pages\EditPengajuan::route('/{record}/edit'),
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
        if ($user->hasRole('pegawai')) {
            return $query->where('user_id', $user->id);
        }
        
        // Manager Atasans can see all applications
        if ($user->hasRole('manager')) {
            return $query;
        }
        
        // Department heads can see applications from their department
        if ($user->hasRole('kepala_departemen')) {
            return $query->whereHas('user', function ($subquery) use ($user) {
                $subquery->where('department', $user->department);
            });
        }
        
        // HRD and Directors can see all applications
        if ($user->hasRole('hrd') || $user->hasRole('direktur')) {
            return $query;
        }
        
        return $query;
    }
}
