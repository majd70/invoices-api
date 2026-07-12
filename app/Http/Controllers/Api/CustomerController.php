<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Services\CustomerService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function __construct(
        private readonly CustomerService $customers,
    ) {}

    /**
     * Display a paginated, searchable list of customers.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->integer('per_page', 15);
        $customers = $this->customers->list(
            search: $request->query('search'),
            perPage: $perPage > 0 ? $perPage : 15,
        );

        return ApiResponse::success(
            data: CustomerResource::collection($customers->items()),
            message: 'Customers retrieved successfully',
            meta: [
                'current_page' => $customers->currentPage(),
                'per_page' => $customers->perPage(),
                'total' => $customers->total(),
                'last_page' => $customers->lastPage(),
            ],
        );
    }

    /**
     * Store a newly created customer.
     */
    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $customer = $this->customers->create($request->validated());

        return ApiResponse::success(
            data: new CustomerResource($customer),
            message: 'Customer created successfully',
            status: 201,
        );
    }

    /**
     * Display a single customer.
     */
    public function show(int $customer): JsonResponse
    {
        $model = $this->customers->find($customer);

        return ApiResponse::success(
            data: new CustomerResource($model),
            message: 'Customer retrieved successfully',
        );
    }

    /**
     * Update an existing customer.
     */
    public function update(UpdateCustomerRequest $request, int $customer): JsonResponse
    {
        $model = $this->customers->update($customer, $request->validated());

        return ApiResponse::success(
            data: new CustomerResource($model),
            message: 'Customer updated successfully',
        );
    }

    /**
     * Remove a customer.
     */
    public function destroy(int $customer): JsonResponse
    {
        $this->customers->delete($customer);

        return ApiResponse::success(message: 'Customer deleted successfully');
    }
}
