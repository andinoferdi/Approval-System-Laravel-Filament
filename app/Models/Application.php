<?php

namespace App\Models;

use App\Filament\Resources\ApplicationResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Application extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'event_name',
        'start_date',
        'end_date',
        'status',
        'document_path',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'status' => 'integer',
    ];
    
    /**
     * Status constants
     */
    const STATUS_PENDING_MANAGER = 1;
    const STATUS_PENDING_DEPT_HEAD = 2;
    const STATUS_PENDING_HRD = 3;
    const STATUS_APPROVED = 4;
    const STATUS_REJECTED = 5;
    
    /**
     * Get the user that owns the application.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the approvals for the application.
     */
    public function approvals(): HasMany
    {
        return $this->hasMany(Approval::class);
    }
    
    /**
     * Get the current approval level.
     */
    public function getCurrentLevel(): int
    {
        $maxApprovedLevel = $this->approvals()
            ->where('status', 'approved')
            ->max('level') ?? 0;
            
        return $maxApprovedLevel + 1;
    }
    
    /**
     * Get the status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING_MANAGER => 'Pending Manager Approval',
            self::STATUS_PENDING_DEPT_HEAD => 'Pending Department Head Approval',
            self::STATUS_PENDING_HRD => 'Pending HRD/Director Approval',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            default => 'Unknown',
        };
    }
}
