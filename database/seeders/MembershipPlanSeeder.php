<?php

namespace Database\Seeders;

use App\Models\MembershipPlan;
use Illuminate\Database\Seeder;

class MembershipPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        collect([
            ['name' => 'Monthly', 'duration_days' => 30, 'price' => 1200, 'description' => 'Full gym access for 30 days.'],
            ['name' => 'Quarterly', 'duration_days' => 90, 'price' => 3200, 'description' => 'Three months of full gym access.'],
            ['name' => 'Annual', 'duration_days' => 365, 'price' => 11000, 'description' => 'One year of full gym access.'],
        ])->each(fn (array $plan) => MembershipPlan::updateOrCreate(['name' => $plan['name']], [...$plan, 'is_active' => true]));
    }
}
