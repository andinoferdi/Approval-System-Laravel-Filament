<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Approval extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'application_id',
        'approver_id',
        'level',
        'status',
        'comments',
        'decided_at',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'decided_at' => 'datetime',
    ];
    
    /**
     * Get the application that owns the approval.
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
    
    /**
     * Get the user who made the approval.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
