<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 100, 5000);
        $tax = round($subtotal * 0.15, 2);
        $discount = fake()->randomFloat(2, 0, $subtotal * 0.1);

        return [
            'customer_id' => Customer::factory(),
            'invoice_number' => 'INV-'.fake()->unique()->numerify('######'),
            'invoice_date' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'subtotal' => $subtotal,
            'tax' => $tax,
            'discount' => $discount,
            'total' => $subtotal + $tax - $discount,
        ];
    }
}
