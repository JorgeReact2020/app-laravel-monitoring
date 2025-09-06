<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Site>
 */
class SiteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company . ' Website',
            'url' => $this->faker->url(),
            'droplet_id' => $this->faker->numberBetween(100000, 999999),
            'status' => 'active',
            'notification_phone' => $this->faker->phoneNumber(),
            'timeout' => $this->faker->numberBetween(10, 30),
            'check_interval' => $this->faker->randomElement([300, 600, 900, 1800]),
            'last_checked_at' => $this->faker->optional()->dateTimeBetween('-1 day'),
            'last_incident_at' => $this->faker->optional()->dateTimeBetween('-1 week'),
        ];
    }
}
