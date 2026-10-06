<?php

namespace App\Models;

use Database\Factories\CoachAvailabilityChangeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoachAvailabilityChange extends Model
{
    /** @use HasFactory<CoachAvailabilityChangeFactory> */
    use HasFactory;

    protected $fillable = [
        'coach_id', 'submitted_by', 'slots', 'is_available', 'status', 'reviewed_by', 'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'slots' => 'array',
            'is_available' => 'boolean',
            'reviewed_at' => 'datetime',
        ];
    }

    public function coach(): BelongsTo
    {
        return $this->belongsTo(Coach::class);
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
