<?php

namespace Tests\Feature\Admin;

use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\ApprovalDecision;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TransactionControllerTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('searchTerms')]
    public function test_admin_can_search_transactions(string $search): void
    {
        $admin = User::factory()->create();
        $targetMember = Member::factory()->create([
            'first_name' => 'Marisol',
            'last_name' => 'Santos',
            'member_code' => 'RFG-TARGET-001',
        ]);
        $targetPlan = MembershipPlan::factory()->create(['name' => 'Annual Gold']);
        Transaction::factory()
            ->for($targetMember)
            ->for($targetPlan, 'plan')
            ->create(['payment_method' => 'gcash', 'reference' => 'GCASH-TRACK-7788']);
        $otherMember = Member::factory()->create([
            'first_name' => 'Hidden',
            'last_name' => 'Record',
            'member_code' => 'RFG-OTHER-999',
        ]);
        Transaction::factory()->for($otherMember)->create(['payment_method' => 'gcash', 'reference' => 'UNRELATED-001']);

        $response = $this->actingAs($admin)->get(route('admin.transactions.index', ['search' => $search]));

        $response
            ->assertOk()
            ->assertSeeText('Marisol Santos')
            ->assertDontSeeText('Hidden Record');
    }

    /** @return array<string, array{string}> */
    public static function searchTerms(): array
    {
        return [
            'full member name' => ['Marisol Santos'],
            'member code' => ['RFG-TARGET-001'],
            'payment reference' => ['GCASH-TRACK-7788'],
            'plan name' => ['Annual Gold'],
        ];
    }

    public function test_search_and_status_filters_are_applied_together(): void
    {
        $admin = User::factory()->create();
        $member = Member::factory()->create(['member_code' => 'RFG-FILTER-001']);
        Transaction::factory()->for($member)->create([
            'payment_method' => 'gcash',
            'reference' => 'PENDING-REFERENCE',
            'status' => 'pending',
        ]);
        Transaction::factory()->for($member)->create([
            'payment_method' => 'gcash',
            'reference' => 'PAID-REFERENCE',
            'status' => 'paid',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.transactions.index', [
            'search' => 'RFG-FILTER-001',
            'status' => 'pending',
        ]));

        $response
            ->assertOk()
            ->assertSeeText('PENDING-REFERENCE')
            ->assertDontSeeText('PAID-REFERENCE');
    }

    public function test_search_rejects_more_than_one_hundred_characters(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.transactions.index', [
            'search' => str_repeat('a', 101),
        ]));

        $response->assertInvalid(['search' => 'The search field must not be greater than 100 characters.']);
    }

    public function test_gcash_verification_queue_excludes_cash_payments(): void
    {
        $admin = User::factory()->create();
        Transaction::factory()->create([
            'payment_method' => 'gcash',
            'reference' => 'GCASH-ONLY-100',
            'status' => 'pending',
        ]);
        Transaction::factory()->create([
            'payment_method' => 'cash',
            'reference' => 'CASH-HIDDEN-100',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)->get(route('admin.transactions.index'))
            ->assertOk()
            ->assertSeeText('GCASH-ONLY-100')
            ->assertDontSeeText('CASH-HIDDEN-100')
            ->assertSeeText('This queue contains only GCash submissions.');
    }

    public function test_admin_can_verify_a_pending_gcash_payment(): void
    {
        Notification::fake([ApprovalDecision::class]);
        $admin = User::factory()->create();
        $memberUser = User::factory()->member()->create();
        $member = Member::factory()->for($memberUser)->create();
        $subscription = Subscription::factory()->for($member)->create(['status' => 'pending']);
        $transaction = Transaction::factory()->for($member)->for($subscription)->create([
            'payment_method' => 'gcash',
            'reference' => 'GCASH-VERIFY-100',
            'receipt_path' => 'receipts/gcash-verify-100.pdf',
            'status' => 'pending',
            'paid_at' => null,
        ]);

        $this->actingAs($admin)->get(route('admin.transactions.index'))
            ->assertOk()
            ->assertSeeText('GCash payment verification')
            ->assertSeeText('GCASH-VERIFY-100')
            ->assertSeeText('Verify paid');

        $this->actingAs($admin)->patch(route('admin.transactions.verification.update', $transaction), [
            'decision' => 'verified',
            'verification_notes' => 'Reference and receipt match the expected amount.',
        ])->assertRedirect(route('admin.transactions.index'))->assertSessionHas('success');

        $transaction->refresh();
        $this->assertSame('paid', $transaction->status);
        $this->assertSame('verified', $transaction->gcashPayment->verification_status);
        $this->assertSame($admin->id, $transaction->gcashPayment->verified_by);
        $this->assertNotNull($transaction->gcashPayment->verified_at);
        $this->assertNotNull($transaction->paid_at);
        Notification::assertSentTo($memberUser, ApprovalDecision::class);
    }

    public function test_rejecting_gcash_requires_notes_and_records_the_reviewer(): void
    {
        $admin = User::factory()->create();
        $transaction = Transaction::factory()->create([
            'payment_method' => 'gcash',
            'reference' => 'GCASH-REJECT-100',
            'receipt_path' => 'receipts/gcash-reject-100.pdf',
            'status' => 'pending',
            'paid_at' => null,
        ]);

        $this->actingAs($admin)->patch(route('admin.transactions.verification.update', $transaction), [
            'decision' => 'rejected',
        ])->assertInvalid(['verification_notes']);

        $this->actingAs($admin)->patch(route('admin.transactions.verification.update', $transaction), [
            'decision' => 'rejected',
            'verification_notes' => 'Reference does not match the uploaded receipt.',
        ])->assertRedirect(route('admin.transactions.index'));

        $transaction->refresh();
        $this->assertSame('rejected', $transaction->status);
        $this->assertSame('rejected', $transaction->gcashPayment->verification_status);
        $this->assertSame($admin->id, $transaction->gcashPayment->verified_by);
        $this->assertSame('Reference does not match the uploaded receipt.', $transaction->gcashPayment->verification_notes);
        $this->assertNull($transaction->paid_at);
    }

    public function test_gcash_and_cash_details_are_stored_in_separate_tables(): void
    {
        $gcash = Transaction::factory()->create([
            'payment_method' => 'gcash',
            'reference' => 'GCASH-SEPARATE-100',
            'receipt_path' => 'receipts/gcash-separate.pdf',
            'status' => 'pending',
            'paid_at' => null,
        ]);
        $cash = Transaction::factory()->create(['payment_method' => 'cash']);

        $this->assertDatabaseHas('gcash_payments', [
            'transaction_id' => $gcash->id,
            'reference' => 'GCASH-SEPARATE-100',
            'receipt_path' => 'receipts/gcash-separate.pdf',
            'verification_status' => 'pending',
        ]);
        $this->assertDatabaseHas('cash_payments', ['transaction_id' => $cash->id]);
        $this->assertDatabaseMissing('cash_payments', ['transaction_id' => $gcash->id]);
        $this->assertDatabaseMissing('gcash_payments', ['transaction_id' => $cash->id]);
    }

    public function test_non_admin_cannot_verify_gcash_payment(): void
    {
        $transaction = Transaction::factory()->create([
            'payment_method' => 'gcash',
            'status' => 'pending',
        ]);

        $this->actingAs(User::factory()->member()->create())
            ->patch(route('admin.transactions.verification.update', $transaction), [
                'decision' => 'verified',
            ])
            ->assertForbidden();

        $this->assertSame('pending', $transaction->fresh()->status);
    }
}
