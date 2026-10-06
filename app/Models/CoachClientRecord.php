<?php

namespace App\Models;

use Database\Factories\CoachClientRecordFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoachClientRecord extends Model
{
    /** @use HasFactory<CoachClientRecordFactory> */
    use HasFactory;

    protected $fillable = ['coach_id', 'member_id', 'title', 'notes', 'recorded_at'];

    protected function casts(): array
    {
        return ['recorded_at' => 'date'];
    }

    public function coach(): BelongsTo
    {
        return $this->belongsTo(Coach::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
