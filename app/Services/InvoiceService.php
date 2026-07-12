<?php

namespace App\Services;

use App\Models\Invoice;
use App\Repositories\Contracts\InvoiceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class InvoiceService
{
    public function __construct(
        private readonly InvoiceRepositoryInterface $invoices,
    ) {}

    /**
     * List invoices, optionally scoped to a single customer.
     */
    public function list(?int $customerId = null, int $perPage = 15): LengthAwarePaginator
    {
        return $this->invoices->paginate($customerId, $perPage);
    }

    public function find(int $id): Invoice
    {
        return $this->invoices->findOrFail($id);
    }

    public function create(array $data): Invoice
    {
        // total is derived on the model, but we keep the computed value
        // explicit here as well so the service is the single source of truth.
        $data['total'] = $this->calculateTotal($data);

        return $this->invoices->create($data);
    }

    public function update(int $id, array $data): Invoice
    {
        $invoice = $this->invoices->findOrFail($id);

        $data['total'] = $this->calculateTotal(array_merge($invoice->toArray(), $data));

        return $this->invoices->update($invoice, $data);
    }

    public function delete(int $id): void
    {
        $invoice = $this->invoices->findOrFail($id);

        $this->invoices->delete($invoice);
    }

    /**
     * total = subtotal + tax - discount.
     */
    private function calculateTotal(array $data): float
    {
        return round(
            (float) ($data['subtotal'] ?? 0)
            + (float) ($data['tax'] ?? 0)
            - (float) ($data['discount'] ?? 0),
            2
        );
    }
}
