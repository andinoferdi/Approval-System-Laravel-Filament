<?php

namespace App\Filament\Resources\PengajuanResource\Pages;

use App\Filament\Resources\PengajuanResource;
use App\Models\Pengajuan;
use App\Models\Approval;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Infolists\Components;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ViewPengajuan extends ViewRecord
{
    protected static string $resource = PengajuanResource::class;
    
    public function getHeading(): string
    {
        /** @var Pengajuan $pengajuan */
        $pengajuan = $this->getRecord();
        
        return 'Pengajuan #' . $pengajuan->id;
    }
    
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Components\Section::make('Informasi Pengajuan')
                    ->schema([
                        Components\TextEntry::make('user.name')
                            ->label('Nama Pegawai'),
                        Components\TextEntry::make('user.position')
                            ->label('Jabatan'),
                        Components\TextEntry::make('user.department')
                            ->label('Departemen'),
                        Components\TextEntry::make('kegiatan')
                            ->label('Nama Kegiatan'),
                        Components\TextEntry::make('jadwal_mulai')
                            ->label('Jadwal Mulai')
                            ->dateTime(),
                        Components\TextEntry::make('jadwal_akhir')
                            ->label('Jadwal Selesai')
                            ->dateTime(),
                        Components\TextEntry::make('status')
                            ->label('Status')
                            ->formatStateUsing(fn (Pengajuan $record) => $record->getStatusLabelAttribute()),
                    ])
                    ->columns(2),
                    
                Components\Section::make('Dokumen Pendukung')
                    ->schema([
                        Components\TextEntry::make('dokumen_pendukung')
                            ->label('Dokumen')
                            ->formatStateUsing(function ($state, Pengajuan $record) {
                                if (empty($state)) {
                                    return 'Tidak ada dokumen';
                                }
                                
                                $url = asset('storage/' . $record->dokumen_pendukung);
                                $extension = pathinfo($record->dokumen_pendukung, PATHINFO_EXTENSION);
                                $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                
                                if ($isImage) {
                                    return "
                                    <div>
                                        <img src='{$url}' alt='Dokumen Pendukung' class='max-w-lg rounded shadow-sm mb-2' />
                                        <div>
                                            <a href='{$url}' target='_blank' class='text-primary-600 hover:text-primary-500'>
                                                Lihat Gambar Asli
                                            </a>
                                        </div>
                                    </div>";
                                } else {
                                    return "<a href='{$url}' target='_blank' class='inline-flex items-center justify-center gap-1.5 font-medium rounded-lg bg-primary-600 text-white px-3 py-1 text-sm'>
                                        <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='currentColor' class='w-5 h-5'>
                                            <path d='M10.75 2.75a.75.75 0 00-1.5 0v8.614L6.295 8.235a.75.75 0 10-1.09 1.03l4.25 4.5a.75.75 0 001.09 0l4.25-4.5a.75.75 0 00-1.09-1.03l-2.955 3.129V2.75z' />
                                            <path d='M3.5 12.75a.75.75 0 00-1.5 0v2.5A2.75 2.75 0 004.75 18h10.5A2.75 2.75 0 0018 15.25v-2.5a.75.75 0 00-1.5 0v2.5c0 .69-.56 1.25-1.25 1.25H4.75c-.69 0-1.25-.56-1.25-1.25v-2.5z' />
                                        </svg>
                                        Download Dokumen
                                    </a>";
                                }
                            })
                            ->html(),
                    ]),
            ]);
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