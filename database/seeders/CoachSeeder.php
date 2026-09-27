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
        collect([
            ['name' => 'Alex Rivera', 'phone' => '09171234567', 'specialty' => 'Strength & Conditioning', 'schedule' => 'Mon-Wed-Fri · 7 AM-4 PM'],
            ['name' => 'Mika Santos', 'phone' => '09181234567', 'specialty' => 'Weight Loss & Mobility', 'schedule' => 'Tue-Thu-Sat · 9 AM-6 PM'],
            ['name' => 'Jordan Cruz', 'phone' => '09191234567', 'specialty' => 'Boxing & Functional Fitness', 'schedule' => 'Mon-Sat · 1 PM-9 PM'],
        ])->each(fn (array $coach) => Coach::updateOrCreate(['name' => $coach['name']], [...$coach, 'is_available' => true]));
    }
}
