<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\DeploymentMailTest;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class DeploymentReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_mail_test_command_sends_a_delivery_check(): void
    {
        Notification::fake([DeploymentMailTest::class]);

        $this->artisan('app:mail-test', ['email' => 'owner@example.com'])
            ->assertSuccessful();

        Notification::assertSentOnDemand(
            DeploymentMailTest::class,
            fn (DeploymentMailTest $notification, array $channels, object $notifiable): bool => $channels === ['mail']
                && $notifiable->routes['mail'] === 'owner@example.com',
        );
    }

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
                'mail.mailers.smtp.scheme' => 'smtp',
                'mail.mailers.smtp.host' => 'smtp.gmail.com',
                'mail.mailers.smtp.username' => 'gym@example.com',
                'mail.mailers.smtp.password' => 'gmail-app-password',
                'mail.from.address' => 'gym@example.com',
                'session.secure' => true,
            ]);

            $this->artisan('app:deployment-check')->assertSuccessful();
        } finally {
            $this->app['env'] = $environment;
            config($configuration);
        }
    }

    public function test_deployment_check_rejects_incomplete_smtp_configuration(): void
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
                'mail.default' => 'smtp',
                'mail.mailers.smtp.scheme' => 'tls',
                'mail.mailers.smtp.host' => 'smtp.gmail.com',
                'mail.mailers.smtp.username' => null,
                'mail.mailers.smtp.password' => null,
                'mail.from.address' => 'noreply@example.com',
                'session.secure' => true,
            ]);

            $this->artisan('app:deployment-check')
                ->expectsOutputToContain('SMTP scheme is supported')
                ->expectsOutputToContain('SMTP credentials are configured')
                ->assertFailed();
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
