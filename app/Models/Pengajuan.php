<?php

namespace App\Models;

use App\Filament\Resources\PengajuanResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pengajuan extends Model
{
    use HasFactory;
    
    /**
     * Definisi nama tabel
     */
    protected $table = 'pengajuans';
    
    /**
     * Atribut yang dapat diisi
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'kegiatan',
        'jadwal_mulai',
        'jadwal_akhir',
        'status',
        'dokumen_pendukung',
    ];
    
    /**
     * Atribut yang harus dikonversi
     *
     * @var array<string, string>
     */
    protected $casts = [
        'jadwal_mulai' => 'datetime',
        'jadwal_akhir' => 'datetime',
        'status' => 'integer',
    ];
    
    /**
     * Konstanta status
     */
    const STATUS_PENDING_MANAGER = 1;
    const STATUS_PENDING_KADEP = 2;
    const STATUS_PENDING_HRD = 3;
    const STATUS_DISETUJUI = 4;
    const STATUS_DITOLAK = 5;
    
    /**
     * Mendapatkan user yang memiliki pengajuan
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Mendapatkan data persetujuan untuk pengajuan
     */
    public function approvals(): HasMany
    {
        return $this->hasMany(Approval::class, 'pengajuan_id');
    }
    
    /**
     * Mendapatkan level persetujuan saat ini
     */
    public function getCurrentLevel(): int
    {
        $maxDisetujuiLevel = $this->approvals()
            ->where('status', 'disetujui')
            ->max('level') ?? 0;
            
        return $maxDisetujuiLevel + 1;
    }
    
    /**
     * Mendapatkan label status
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING_MANAGER => 'Menunggu Persetujuan Manager',
            self::STATUS_PENDING_KADEP => 'Menunggu Persetujuan Kepala Departemen',
            self::STATUS_PENDING_HRD => 'Menunggu Persetujuan HRD/Direktur',
            self::STATUS_DISETUJUI => 'Disetujui',
            self::STATUS_DITOLAK => 'Ditolak',
            default => 'Status Tidak Diketahui',
        };
    }
}
