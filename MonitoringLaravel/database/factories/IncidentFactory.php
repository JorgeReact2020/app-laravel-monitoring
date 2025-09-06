<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Incident>
 */
class IncidentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $detectedAt = $this->faker->dateTimeBetween('-1 week');
        
        return [
            'site_id' => \App\Models\Site::factory(),
            'status' => $this->faker->randomElement(['detected', 'verified', 'notification_sent', 'resolved']),
            'error_details' => $this->faker->optional()->sentence(),
            'response_time' => $this->faker->optional()->numberBetween(5000, 30000),
            'status_code' => $this->faker->optional()->randomElement([0, 500, 502, 503, 504]),
            'detected_at' => $detectedAt,
            'verified_at' => $this->faker->optional()->dateTimeBetween($detectedAt, '+10 minutes'),
            'notification_sent_at' => $this->faker->optional()->dateTimeBetween($detectedAt, '+15 minutes'),
            'resolved_at' => $this->faker->optional()->dateTimeBetween($detectedAt, '+2 hours'),
        ];
    }
}
