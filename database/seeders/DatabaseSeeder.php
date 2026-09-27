<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(['email' => 'admin@rushfitness.test'], [
            'name' => 'Rush Fitness Admin',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $this->call([
            CoachSeeder::class,
            MembershipPlanSeeder::class,
        ]);
    }
}
