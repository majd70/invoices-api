<?php

namespace App\Repositories;

use App\Models\Invoice;
use App\Repositories\Contracts\InvoiceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class InvoiceRepository implements InvoiceRepositoryInterface
{
    public function paginate(?int $customerId = null, int $perPage = 15): LengthAwarePaginator
    {
        return Invoice::query()
            ->with('customer')
            ->when($customerId, fn ($query) => $query->where('customer_id', $customerId))
            ->latest()
            ->paginate($perPage);
    }

    public function findOrFail(int $id): Invoice
    {
        return Invoice::query()->with('customer')->findOrFail($id);
    }

    public function create(array $data): Invoice
    {
        return Invoice::create($data)->load('customer');
    }

    public function update(Invoice $invoice, array $data): Invoice
    {
        $invoice->update($data);

        return $invoice->refresh()->load('customer');
    }

    public function delete(Invoice $invoice): void
    {
        $invoice->delete();
    }
}
