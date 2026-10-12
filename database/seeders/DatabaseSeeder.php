<?php

namespace Database\Seeders;

use App\Models\Coach;
use App\Models\CoachAssignmentRequest;
use App\Models\CoachClientRecord;
use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\ScheduleEvent;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (app()->isProduction()) {
            throw new \RuntimeException('The development seeder is disabled in production because it creates demonstration accounts with known passwords.');
        }

        $this->call([
            CoachSeeder::class,
            MembershipPlanSeeder::class,
        ]);

        User::whereIn('email', [
            'coach1@rushfitness.test',
            'coach2@rushfitness.test',
            'coach3@rushfitness.test',
            'coach4@rushfitness.test',
            'member1@rushfitness.test',
            'member2@rushfitness.test',
            'member3@rushfitness.test',
            'member4@rushfitness.test',
            'member5@rushfitness.test',
        ])->delete();

        $admin = User::updateOrCreate(['email' => 'admin@rushfitness.test'], [
            'name' => 'Rush Fitness Admin',
            'password' => 'password',
            'role' => 'admin',
            'preferred_channel' => 'private_message',
        ]);

        $coaches = [
            ['name' => 'Elmer Permacio', 'email' => 'coachelmer@gmail.com', 'phone' => '09171234567'],
            ['name' => 'Jay Verdadero', 'email' => 'coachjay@gmail.com', 'phone' => '09181234567'],
        ];

        foreach ($coaches as $coach) {
            $user = User::updateOrCreate(['email' => $coach['email']], [
                'name' => $coach['name'],
                'password' => 'password',
                'role' => 'coach',
                'phone' => $coach['phone'],
                'preferred_channel' => 'private_message',
            ]);

            Coach::where('name', $coach['name'])->update([
                'user_id' => $user->id,
                'email' => $coach['email'],
            ]);
        }

        $members = [
            ['code' => 'RFG-1001', 'first_name' => 'Shawn Harry', 'last_name' => 'Yuson', 'email' => 'shawnharry@gmail.com', 'phone' => '09170000001', 'coach' => 'Elmer Permacio', 'goal' => 'Build muscle', 'plan' => 'Monthly', 'token' => '00000000-0000-4000-8000-000000001001'],
            ['code' => 'RFG-1002', 'first_name' => 'Qian Jedmarr', 'last_name' => 'Forro', 'email' => 'qianjedmarr@gmail.com', 'phone' => '09170000002', 'coach' => 'Jay Verdadero', 'goal' => 'Martial Arts', 'plan' => 'Quarterly', 'token' => '00000000-0000-4000-8000-000000001002'],
            ['code' => 'RFG-1003', 'first_name' => 'Jose Joenard', 'last_name' => 'Loja', 'email' => 'josejoenard@gmail.com', 'phone' => '09170000003', 'coach' => 'Jay Verdadero', 'goal' => 'Kick Boxing', 'plan' => 'Annual', 'token' => '00000000-0000-4000-8000-000000001003'],
        ];

        foreach ($members as $index => $member) {
            $user = User::updateOrCreate(['email' => $member['email']], [
                'name' => $member['first_name'].' '.$member['last_name'],
                'password' => 'password',
                'role' => 'user',
                'phone' => $member['phone'],
                'preferred_channel' => 'private_message',
            ]);

            $memberProfile = Member::updateOrCreate(['member_code' => $member['code']], [
                'user_id' => $user->id,
                'first_name' => $member['first_name'],
                'last_name' => $member['last_name'],
                'phone' => $member['phone'],
                'email' => $member['email'],
                'preferred_channel' => 'private_message',
                'fitness_goal' => $member['goal'],
                'availability_days' => ['Monday', 'Wednesday', 'Friday'],
                'availability_starts_at' => '17:00',
                'availability_ends_at' => '19:00',
                'coach_id' => Coach::where('name', $member['coach'])->value('id'),
            ]);

            $plan = MembershipPlan::where('name', $member['plan'])->firstOrFail();
            CoachAssignmentRequest::updateOrCreate([
                'member_id' => $memberProfile->id,
                'coach_id' => $memberProfile->coach_id,
            ], [
                'status' => 'accepted',
                'reviewed_at' => now()->subDays(5),
            ]);
            $subscription = Subscription::updateOrCreate(['access_token' => $member['token']], [
                'member_id' => $memberProfile->id,
                'membership_plan_id' => $plan->id,
                'starts_at' => today()->subDays(5),
                'expires_at' => today()->addDays($plan->duration_days - 6),
                'status' => 'active',
                'paid_at' => now()->subDays(5),
                'qr_issued_at' => now()->subDays(5),
                'reminder_sent_at' => null,
            ]);

            $transaction = Transaction::updateOrCreate(['reference' => 'SEED-'.$member['code']], [
                'member_id' => $memberProfile->id,
                'subscription_id' => $subscription->id,
                'membership_plan_id' => $plan->id,
                'amount' => $plan->price,
                'payment_method' => 'gcash',
                'status' => 'paid',
                'paid_at' => now()->subDays(5),
            ]);
            $transaction->gcashPayment()->updateOrCreate([], [
                'reference' => 'SEED-'.$member['code'],
                'receipt_path' => 'receipts/seed-'.$member['code'].'.pdf',
                'verification_status' => 'verified',
                'verified_at' => now()->subDays(5),
                'verification_notes' => 'Seeded verified payment.',
            ]);

            ScheduleEvent::updateOrCreate([
                'member_id' => $memberProfile->id,
                'title' => 'Personal training session',
            ], [
                'type' => 'session',
                'starts_at' => now()->addDays($index + 1)->setTime(9 + $index, 0),
                'ends_at' => now()->addDays($index + 1)->setTime(10 + $index, 0),
                'coach_id' => $memberProfile->coach_id,
                'created_by' => $admin->id,
                'notes' => 'Seeded development session.',
            ]);

            CoachClientRecord::updateOrCreate([
                'member_id' => $memberProfile->id,
                'title' => 'Monthly progress review',
            ], [
                'coach_id' => $memberProfile->coach_id,
                'notes' => 'Reviewed current goals, training consistency, and the next milestone for this member.',
                'recorded_at' => today()->subDays($index),
            ]);
        }
    }
}
