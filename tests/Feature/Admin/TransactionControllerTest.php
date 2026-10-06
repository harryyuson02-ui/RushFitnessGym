<?php

namespace Tests\Feature\Admin;

use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
            ->create(['reference' => 'GCASH-TRACK-7788']);
        $otherMember = Member::factory()->create([
            'first_name' => 'Hidden',
            'last_name' => 'Record',
            'member_code' => 'RFG-OTHER-999',
        ]);
        Transaction::factory()->for($otherMember)->create(['reference' => 'UNRELATED-001']);

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
            'reference' => 'PENDING-REFERENCE',
            'status' => 'pending',
        ]);
        Transaction::factory()->for($member)->create([
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
}
