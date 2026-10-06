<?php

namespace App\Models;

use Database\Factories\AvailabilitySlotFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AvailabilitySlot extends Model
{
    /** @use HasFactory<AvailabilitySlotFactory> */
    use HasFactory;

    protected $fillable = ['coach_id', 'day_of_week', 'starts_at', 'ends_at', 'is_available'];

    protected function casts(): array
    {
        return ['is_available' => 'boolean'];
    }

    public function coach(): BelongsTo
    {
        return $this->belongsTo(Coach::class);
    }
}
