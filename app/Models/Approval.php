<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Approval extends Model
{
    use HasFactory;
    
    /**
     * Atribut yang dapat diisi
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'pengajuan_id',
        'approver_id',
        'level',
        'status',
        'comments',
        'decided_at',
    ];
    
    /**
     * Atribut yang harus dikonversi
     *
     * @var array<string, string>
     */
    protected $casts = [
        'decided_at' => 'datetime',
    ];
    
    /**
     * Mendapatkan pengajuan yang memiliki persetujuan
     */
    public function pengajuan(): BelongsTo
    {
        return $this->belongsTo(Pengajuan::class);
    }
    
    /**
     * Mendapatkan user yang memberikan persetujuan
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
