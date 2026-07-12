<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Sanctum::actingAs(User::factory()->create());
    }

    public function test_it_can_create_an_invoice_and_computes_the_total(): void
    {
        $customer = Customer::factory()->create();

        $response = $this->postJson('/api/invoices', [
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-1001',
            'invoice_date' => '2026-01-15',
            'subtotal' => 1000,
            'tax' => 150,
            'discount' => 50,
        ]);

        // total = 1000 + 150 - 50 = 1100
        $response->assertStatus(201)
            ->assertJson([
                'status' => true,
                'message' => 'Invoice created successfully',
                'data' => ['invoice_number' => 'INV-1001', 'total' => 1100],
            ]);

        $this->assertDatabaseHas('invoices', [
            'invoice_number' => 'INV-1001',
            'total' => 1100,
        ]);
    }

    public function test_it_defaults_discount_to_zero_in_the_total(): void
    {
        $customer = Customer::factory()->create();

        $response = $this->postJson('/api/invoices', [
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-2002',
            'invoice_date' => '2026-02-20',
            'subtotal' => 500,
            'tax' => 75,
        ]);

        // total = 500 + 75 - 0 = 575
        $response->assertStatus(201)->assertJsonPath('data.total', 575);
    }

    public function test_it_rejects_an_invoice_for_a_missing_customer(): void
    {
        $response = $this->postJson('/api/invoices', [
            'customer_id' => 9999,
            'invoice_number' => 'INV-3003',
            'invoice_date' => '2026-03-10',
            'subtotal' => 200,
            'tax' => 30,
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('customer_id');
    }

    public function test_it_rejects_negative_amounts(): void
    {
        $customer = Customer::factory()->create();

        $response = $this->postJson('/api/invoices', [
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-4004',
            'invoice_date' => '2026-04-05',
            'subtotal' => -100,
            'tax' => -10,
            'discount' => -5,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['subtotal', 'tax', 'discount']);
    }

    public function test_it_rejects_a_duplicate_invoice_number(): void
    {
        $customer = Customer::factory()->create();
        Invoice::factory()->create([
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-DUP',
        ]);

        $response = $this->postJson('/api/invoices', [
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-DUP',
            'invoice_date' => '2026-05-05',
            'subtotal' => 100,
            'tax' => 10,
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('invoice_number');
    }

    public function test_it_lists_invoices_for_a_specific_customer(): void
    {
        $customer = Customer::factory()->create();
        Invoice::factory(3)->create(['customer_id' => $customer->id]);
        Invoice::factory(2)->create(); // other customers

        $response = $this->getJson("/api/customers/{$customer->id}/invoices");

        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonCount(3, 'data');
    }

    public function test_it_updates_and_recomputes_the_total(): void
    {
        $customer = Customer::factory()->create();
        $invoice = Invoice::factory()->create([
            'customer_id' => $customer->id,
            'subtotal' => 1000,
            'tax' => 100,
            'discount' => 0,
        ]);

        $response = $this->putJson("/api/invoices/{$invoice->id}", [
            'discount' => 200,
        ]);

        // total = 1000 + 100 - 200 = 900
        $response->assertOk()->assertJsonPath('data.total', 900);
    }
}
