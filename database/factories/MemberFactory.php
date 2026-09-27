<?php

namespace Database\Factories;

use App\Models\Coach;
use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Member>
 */
class MemberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'member_code' => 'RFG-'.Str::upper(Str::random(8)),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'birth_date' => fake()->dateTimeBetween('-60 years', '-16 years'),
            'gender' => fake()->randomElement(['Female', 'Male', 'Prefer not to say']),
            'phone' => fake()->numerify('09#########'),
            'email' => fake()->unique()->safeEmail(),
            'address' => fake()->address(),
            'preferred_channel' => fake()->randomElement(['sms', 'whatsapp']),
            'coach_id' => Coach::factory(),
        ];
    }
}
