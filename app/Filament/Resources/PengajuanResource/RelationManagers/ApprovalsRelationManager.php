<?php

namespace App\Filament\Resources\PengajuanResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ApprovalsRelationManager extends RelationManager
{
    protected static string $relationship = 'approvals';
    
    protected static ?string $recordTitleAttribute = 'status';
    
    // Set a title for the relation
    protected static ?string $title = 'Riwayat Approval';
    
    // Disable creating approvals through the relation manager
    protected static bool $canCreate = false;
    
    // Disable editing approvals
    protected static bool $canEdit = false;
    
    // Disable deleting approvals
    protected static bool $canDelete = false;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('approver_id')
                    ->relationship('approver', 'name')
                    ->required(),
                Forms\Components\Select::make('level')
                    ->options([
                        1 => 'Manager Atasan',
                        2 => 'Kepala Departemen',
                        3 => 'HRD/Direktur',
                    ])
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options([
                        'menunggu' => 'Menunggu',
                        'disetujui' => 'Disetujui',
                        'ditolak' => 'Ditolak',
                    ])
                    ->required(),
                Forms\Components\Textarea::make('comments')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\DateTimePicker::make('decided_at'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('status')
            ->columns([
                Tables\Columns\TextColumn::make('approver.name')
                    ->label('Approver')
                    ->sortable(),
                Tables\Columns\TextColumn::make('level')
                    ->badge()
                    ->formatStateUsing(fn (int $state): string => match ($state) {
                        1 => 'Manager Atasan',
                        2 => 'Kepala Departemen',
                        3 => 'HRD/Direktur',
                        default => 'Tidak Diketahui',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'menunggu' => 'warning',
                        'disetujui' => 'success',
                        'ditolak' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('decided_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('comments')
                    ->limit(30)
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // No create action as approvals should be generated through the application process
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                // No bulk actions needed
            ]);
    }
}
