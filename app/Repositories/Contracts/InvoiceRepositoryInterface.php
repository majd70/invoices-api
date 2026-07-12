<?php

namespace App\Repositories\Contracts;

use App\Models\Invoice;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface InvoiceRepositoryInterface
{
    /**
     * Paginate invoices, optionally scoped to a single customer.
     */
    public function paginate(?int $customerId = null, int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): Invoice;

    public function create(array $data): Invoice;

    public function update(Invoice $invoice, array $data): Invoice;

    public function delete(Invoice $invoice): void;
}
