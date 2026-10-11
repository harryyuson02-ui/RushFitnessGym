<?php

namespace App\Models;

use Database\Factories\CoachFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coach extends Model
{
    /** @use HasFactory<CoachFactory> */
    use HasFactory;

    public const SPECIALTIES = ['Strength', 'Conditioning', 'Strength & Conditioning', 'Boxing', 'Boxing & Functional Fitness', 'Mobility', 'Weight Loss', 'Weight Loss & Mobility', 'Bodybuilding', 'Yoga'];

    protected $fillable = ['user_id', 'name', 'email', 'phone', 'specialty', 'schedule', 'is_available'];

    protected function casts(): array
    {
        return ['is_available' => 'boolean'];
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function availabilitySlots(): HasMany
    {
        return $this->hasMany(AvailabilitySlot::class);
    }

    public function availabilityChanges(): HasMany
    {
        return $this->hasMany(CoachAvailabilityChange::class);
    }

    public function scheduleEvents(): HasMany
    {
        return $this->hasMany(ScheduleEvent::class);
    }

    public function clientRecords(): HasMany
    {
        return $this->hasMany(CoachClientRecord::class);
    }

    public function assignmentRequests(): HasMany
    {
        return $this->hasMany(CoachAssignmentRequest::class);
    }

    /**
     * @param  array<int, array{day_of_week: int, starts_at: string, ends_at: string}>  $slots
     */
    public static function scheduleSummary(array $slots): string
    {
        $dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

        return collect($slots)
            ->sortBy('day_of_week')
            ->map(fn (array $slot): string => sprintf(
                '%s %s–%s',
                $dayNames[$slot['day_of_week']],
                date('g:i A', strtotime($slot['starts_at'])),
                date('g:i A', strtotime($slot['ends_at'])),
            ))
            ->implode(' · ') ?: 'No availability selected';
    }
}
