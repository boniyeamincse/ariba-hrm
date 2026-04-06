<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tenant>
 */
class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subdomain = $this->faker->slug();
        return [
            'name' => $this->faker->company(),
            'subdomain' => $subdomain,
            'database_name' => 'hms_' . str_replace('-', '_', $subdomain),
            'status' => 'active',
            'metadata' => [],
        ];
    }
}
