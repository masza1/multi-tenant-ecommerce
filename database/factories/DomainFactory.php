<?php

namespace Database\Factories;

use App\Models\Domain;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Domain>
 */
class DomainFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'domain' => $this->faker->unique()->domainName(),
            'type' => 'custom',
            'is_primary' => false,
            'status' => 'active',
            'description' => $this->faker->sentence(),
            'verified_at' => now(),
            'ssl_certificate' => null,
            'redirect_from_domain_id' => null,
        ];
    }

    /**
     * Indicate that the domain is a subdomain.
     */
    public function subdomain(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'subdomain',
            'domain' => $this->faker->unique()->word() . '.localhost',
        ]);
    }

    /**
     * Indicate that the domain is primary.
     */
    public function primary(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_primary' => true,
        ]);
    }

    /**
     * Indicate that the domain is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    /**
     * Indicate that the domain is pending verification.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'verified_at' => null,
        ]);
    }

    /**
     * Indicate that the domain is verified.
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'verified_at' => now(),
        ]);
    }

    /**
     * Indicate that the domain is unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'verified_at' => null,
        ]);
    }

    /**
     * Indicate that the domain has an SSL certificate.
     */
    public function withSSL(): static
    {
        return $this->state(fn (array $attributes) => [
            'ssl_certificate' => '/etc/ssl/certs/domain_' . uniqid() . '.pem',
        ]);
    }
}
