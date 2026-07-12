<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTotalTest extends TestCase
{
    use RefreshDatabase;

    public function test_total_is_computed_on_save(): void
    {
        $customer = Customer::factory()->create();

        $invoice = Invoice::create([
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-UNIT-1',
            'invoice_date' => '2026-01-15',
            'subtotal' => 1000,
            'tax' => 150,
            'discount' => 50,
        ]);

        // 1000 + 150 - 50 = 1100
        $this->assertEquals(1100, (float) $invoice->total);
    }

    public function test_total_recomputes_when_amounts_change(): void
    {
        $customer = Customer::factory()->create();

        $invoice = Invoice::create([
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-UNIT-2',
            'invoice_date' => '2026-01-15',
            'subtotal' => 500,
            'tax' => 75,
            'discount' => 0,
        ]);

        $this->assertEquals(575, (float) $invoice->total);

        $invoice->update(['discount' => 100]);

        // 500 + 75 - 100 = 475
        $this->assertEquals(475, (float) $invoice->fresh()->total);
    }
}
