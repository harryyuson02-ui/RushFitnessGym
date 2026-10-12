<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PortalShortcutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->seed();
    }

    public function test_login_as_jay_preserves_jay_across_coach_pages(): void
    {
        $jay = User::where('email', 'coachjay@gmail.com')->firstOrFail();

        $this->post('/login', [
            'email' => 'coachjay@gmail.com',
            'password' => 'password',
        ])->assertRedirect(route('coach.dashboard'));

        $this->get(route('coach.dashboard'))
            ->assertOk()
            ->assertSee('Welcome, Jay Verdadero')
            ->assertDontSee('Elmer Permacio');
        $this->get(route('coach.clients.index'))->assertOk();

        $this->assertAuthenticatedAs($jay);
    }

    #[DataProvider('portalProvider')]
    public function test_authenticated_portal_shortcut_redirects_to_its_dashboard(
        string $path,
        string $email,
        string $dashboardRoute,
    ): void {
        $this->actingAs(User::where('email', $email)->firstOrFail())
            ->get($path)
            ->assertRedirect(route($dashboardRoute));
    }

    /** @return array<string, array{string, string, string}> */
    public static function portalProvider(): array
    {
        return [
            'admin' => ['/admin', 'admin@rushfitness.test', 'admin.dashboard'],
            'coach' => ['/coach', 'coachelmer@gmail.com', 'coach.dashboard'],
            'member' => ['/member', 'shawnharry@gmail.com', 'member.dashboard'],
        ];
    }

    #[DataProvider('guestPortalProvider')]
    public function test_portal_shortcut_requires_login(string $path): void
    {
        $this->get($path)->assertRedirect(route('login'));
    }

    /** @return array<string, array{string}> */
    public static function guestPortalProvider(): array
    {
        return [
            'admin' => ['/admin'],
            'coach' => ['/coach'],
            'member' => ['/member'],
        ];
    }

    public function test_old_preview_url_is_not_available(): void
    {
        $this->get('/preview/admin')->assertNotFound();
    }

    public function test_member_routes_use_the_member_url_prefix(): void
    {
        $this->assertSame('/member/dashboard', route('member.dashboard', absolute: false));
        $this->assertSame('/member/records', route('member.records.index', absolute: false));
        $this->assertSame('/member/transactions', route('member.transactions.index', absolute: false));
        $this->assertSame('/member/plans', route('member.plans.index', absolute: false));
    }

    public function test_member_plan_cards_use_the_same_images_as_the_admin_catalog(): void
    {
        $member = User::where('email', 'shawnharry@gmail.com')->firstOrFail();

        $this->actingAs($member)->get(route('member.plans.index'))
            ->assertOk()
            ->assertSee('images/plans/monthly.jpg')
            ->assertSee('images/plans/quarterly.jpg')
            ->assertSee('images/plans/annual.jpg');
    }

    public function test_seeded_coach_dashboard_and_client_directory_use_the_same_active_clients(): void
    {
        $jay = User::where('email', 'coachjay@gmail.com')->firstOrFail();

        $this->actingAs($jay)->get(route('coach.dashboard'))
            ->assertOk()
            ->assertSeeText('2');

        $this->actingAs($jay)->get(route('coach.clients.index'))
            ->assertOk()
            ->assertSeeText('Qian Jedmarr Forro')
            ->assertSeeText('Jose Joenard Loja');
    }
}
