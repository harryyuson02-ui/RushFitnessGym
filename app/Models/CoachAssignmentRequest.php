<?php

namespace App\Models;

use Database\Factories\CoachAssignmentRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoachAssignmentRequest extends Model
{
    /** @use HasFactory<CoachAssignmentRequestFactory> */
    use HasFactory;

    protected $fillable = ['member_id', 'coach_id', 'status', 'reviewed_at'];

    protected function casts(): array
    {
        return ['reviewed_at' => 'datetime'];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function coach(): BelongsTo
    {
        return $this->belongsTo(Coach::class);
    }
}
