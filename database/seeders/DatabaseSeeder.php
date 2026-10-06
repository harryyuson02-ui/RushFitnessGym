<?php

namespace Database\Seeders;

use App\Models\Coach;
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
        $this->call([
            CoachSeeder::class,
            MembershipPlanSeeder::class,
        ]);

        User::updateOrCreate(['email' => 'admin@rushfitness.test'], [
            'name' => 'Rush Fitness Admin',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'preferred_channel' => 'private_message',
        ]);

        $coachUser = User::updateOrCreate(['email' => 'coach@rushfitness.test'], [
            'name' => 'Alex Rivera',
            'password' => Hash::make('password'),
            'role' => 'coach',
            'phone' => '09171234567',
            'preferred_channel' => 'private_message',
        ]);

        User::updateOrCreate(['email' => 'member@rushfitness.test'], [
            'name' => 'Rush Fitness Member',
            'password' => Hash::make('password'),
            'role' => 'user',
            'phone' => '09170000001',
            'preferred_channel' => 'private_message',
        ]);

        Coach::where('name', 'Alex Rivera')->update(['user_id' => $coachUser->id]);
    }
}
