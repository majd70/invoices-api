<?php

namespace App\Repositories\Contracts;

use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CustomerRepositoryInterface
{
    /**
     * Paginate customers, optionally filtered by a search term.
     */
    public function paginate(?string $search = null, int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): Customer;

    public function create(array $data): Customer;

    public function update(Customer $customer, array $data): Customer;

    public function delete(Customer $customer): void;
}
