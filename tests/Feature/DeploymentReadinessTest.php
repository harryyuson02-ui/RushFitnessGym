<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DeploymentReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_secure_admin_creation_command_creates_an_administrator(): void
    {
        $this->artisan('app:create-admin', [
            'email' => 'owner@rushfitness.example',
            '--name' => 'Gym Owner',
        ])
            ->expectsQuestion('Password', 'StrongPassword!123')
            ->expectsQuestion('Confirm password', 'StrongPassword!123')
            ->assertSuccessful();

        $admin = User::where('email', 'owner@rushfitness.example')->firstOrFail();
        $this->assertSame('admin', $admin->role);
        $this->assertTrue(Hash::check('StrongPassword!123', $admin->password));
    }

    public function test_deployment_check_accepts_secure_production_configuration(): void
    {
        $environment = $this->app['env'];
        $configuration = config()->all();

        try {
            $this->app['env'] = 'production';
            config([
                'app.debug' => false,
                'app.key' => 'base64:'.base64_encode(random_bytes(32)),
                'app.url' => 'https://app.rushfitness.example',
                'database.default' => 'mysql',
                'database.connections.mysql.username' => 'rush_fitness_app',
                'filesystems.default' => 's3',
                'mail.default' => 'smtp',
                'session.secure' => true,
            ]);

            $this->artisan('app:deployment-check')->assertSuccessful();
        } finally {
            $this->app['env'] = $environment;
            config($configuration);
        }
    }

    public function test_development_seed_data_is_blocked_in_production(): void
    {
        $environment = $this->app['env'];

        try {
            $this->app['env'] = 'production';
            $this->expectException(\RuntimeException::class);
            $this->expectExceptionMessage('development seeder is disabled in production');

            app(DatabaseSeeder::class)->run();
        } finally {
            $this->app['env'] = $environment;
        }
    }
}
