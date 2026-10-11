<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

#[Signature('app:create-admin {email? : Administrator email address} {--name=Rush Fitness Admin : Administrator display name}')]
#[Description('Securely create a production administrator account')]
class CreateAdminUser extends Command
{
    public function handle(): int
    {
        $email = mb_strtolower(trim((string) ($this->argument('email') ?: $this->ask('Admin email address'))));
        $name = trim((string) $this->option('name'));
        $password = (string) $this->secret('Password');
        $confirmation = (string) $this->secret('Confirm password');

        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $confirmation,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(12)->letters()->mixedCase()->numbers()->symbols()],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => 'admin',
            'preferred_channel' => 'private_message',
        ]);

        $this->info("Administrator {$email} created successfully.");

        return self::SUCCESS;
    }
}
