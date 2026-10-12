<?php

namespace Database\Seeders;

use App\Models\Coach;
use Illuminate\Database\Seeder;

class CoachSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Coach::whereIn('name', [
            'Alex Rivera',
            'Mika Santos',
            'Jordan Cruz',
            'Taylor Reyes',
            'Coach Elmer Permacio',
            'Coach Jay Verdadero',
        ])->delete();

        collect([
            ['name' => 'Elmer Permacio', 'email' => 'coachelmer@gmail.com', 'phone' => '09171234567', 'specialty' => 'Gym Coach', 'schedule' => 'Mon-Wed-Fri · 7 AM-4 PM'],
            ['name' => 'Jay Verdadero', 'email' => 'coachjay@gmail.com', 'phone' => '09181234567', 'specialty' => 'Martial Arts Coach', 'schedule' => 'Tue-Thu-Sat · 9 AM-6 PM'],
        ])->each(fn (array $coach) => Coach::updateOrCreate(['email' => $coach['email']], [...$coach, 'is_available' => true]));
    }
}
