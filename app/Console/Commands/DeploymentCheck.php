<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:deployment-check')]
#[Description('Validate critical Rush Fitness Gym production configuration')]
class DeploymentCheck extends Command
{
    public function handle(): int
    {
        $checks = [
            'Application environment is production' => app()->environment('production'),
            'Debug mode is disabled' => config('app.debug') === false,
            'Application URL uses HTTPS' => str_starts_with((string) config('app.url'), 'https://'),
            'Application encryption key is configured' => filled(config('app.key')),
            'Session cookies require HTTPS' => config('session.secure') === true,
            'A real mail transport is configured' => ! in_array(config('mail.default'), ['array', 'log'], true),
            'A production database driver is configured' => in_array(config('database.default'), ['mysql', 'mariadb', 'pgsql'], true),
            'Database is not using the root account' => config('database.connections.'.config('database.default').'.username') !== 'root',
        ];

        $failed = false;
        foreach ($checks as $label => $passed) {
            $passed ? $this->components->info($label) : $this->components->error($label);
            $failed = $failed || ! $passed;
        }

        if (config('filesystems.default') === 'local') {
            $this->components->warn('Local uploads require a persistent server disk and an external backup. Use S3 on ephemeral hosting.');
        }

        if ($failed) {
            $this->error('Production configuration is not ready. Correct the failed checks before deployment.');

            return self::FAILURE;
        }

        $this->info('Critical production configuration checks passed.');

        return self::SUCCESS;
    }
}
