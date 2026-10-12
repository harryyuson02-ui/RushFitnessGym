<?php

namespace Tests\Feature;

use App\Models\Coach;
use App\Models\CoachAssignmentRequest;
use App\Models\CoachClientRecord;
use App\Models\Member;
use App\Models\ScheduleEvent;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_the_development_accounts_and_linked_profiles(): void
    {
        $this->seed();

        $this->assertSame(1, User::where('role', 'admin')->count());
        $this->assertSame(2, User::where('role', 'coach')->count());
        $this->assertSame(3, User::where('role', 'user')->count());
        $this->assertSame(2, Coach::whereNotNull('user_id')->count());
        $this->assertSame(3, Member::whereNotNull('user_id')->count());
        $this->assertSame(3, Subscription::active()->count());
        $this->assertSame(3, Transaction::where('status', 'paid')->count());
        $this->assertSame(3, ScheduleEvent::where('type', 'session')->count());
        $this->assertSame(3, CoachClientRecord::count());
        $this->assertSame(3, CoachAssignmentRequest::where('status', 'accepted')->count());
        $this->assertDatabaseHas('coaches', ['name' => 'Elmer Permacio', 'specialty' => 'Gym Coach']);
        $this->assertDatabaseHas('coaches', ['name' => 'Jay Verdadero', 'specialty' => 'Martial Arts Coach']);

        Member::query()->with(['currentSubscription.plan'])->each(function (Member $member): void {
            $this->assertNotNull($member->currentSubscription);
            $this->assertSame('active', $member->currentSubscription->status);
            $this->assertNotNull($member->currentSubscription->plan);
        });

        User::query()->each(function (User $user): void {
            $this->assertTrue(Hash::check('password', $user->password));
        });
    }

    public function test_reseeding_does_not_duplicate_development_accounts(): void
    {
        $this->seed();
        $this->seed();

        $this->assertSame(6, User::count());
        $this->assertSame(2, Coach::count());
        $this->assertSame(3, Member::count());
        $this->assertSame(3, Subscription::count());
        $this->assertSame(3, Transaction::count());
        $this->assertSame(3, ScheduleEvent::count());
        $this->assertSame(3, CoachClientRecord::count());
        $this->assertSame(3, CoachAssignmentRequest::count());
    }
}
