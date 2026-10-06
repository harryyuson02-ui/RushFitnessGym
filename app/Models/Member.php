<?php

namespace App\Models;

use Database\Factories\MemberFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Member extends Model
{
    /** @use HasFactory<MemberFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id', 'member_code', 'first_name', 'last_name', 'birth_date', 'gender', 'phone',
        'email', 'address', 'preferred_channel', 'fitness_goal', 'availability_days',
        'availability_starts_at', 'availability_ends_at', 'coach_id',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'availability_days' => 'array',
        ];
    }

    public function coach(): BelongsTo
    {
        return $this->belongsTo(Coach::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function currentSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->ofMany('id', 'max');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function scheduleEvents(): HasMany
    {
        return $this->hasMany(ScheduleEvent::class);
    }

    public function coachRecords(): HasMany
    {
        return $this->hasMany(CoachClientRecord::class);
    }

    #[Scope]
    protected function activeClientOf(Builder $query, Coach $coach): Builder
    {
        return $query
            ->whereBelongsTo($coach)
            ->whereHas('currentSubscription', fn (Builder $subscription): Builder => $subscription->active());
    }

    public function getFullNameAttribute(): string
    {
        return $this->first_name.' '.$this->last_name;
    }
}
