<?php

namespace App\Services;

use App\Models\Customer;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CustomerService
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customers,
    ) {}

    public function list(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return $this->customers->paginate($search, $perPage);
    }

    public function find(int $id): Customer
    {
        return $this->customers->findOrFail($id);
    }

    public function create(array $data): Customer
    {
        return $this->customers->create($data);
    }

    public function update(int $id, array $data): Customer
    {
        $customer = $this->customers->findOrFail($id);

        return $this->customers->update($customer, $data);
    }

    public function delete(int $id): void
    {
        $customer = $this->customers->findOrFail($id);

        $this->customers->delete($customer);
    }
}
