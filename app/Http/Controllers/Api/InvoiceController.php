<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Invoice\StoreInvoiceRequest;
use App\Http\Requests\Invoice\UpdateInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Services\CustomerService;
use App\Services\InvoiceService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __construct(
        private readonly InvoiceService $invoices,
        private readonly CustomerService $customers,
    ) {}

    /**
     * Display a paginated list of invoices (optionally filtered by customer).
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->integer('per_page', 15);
        $invoices = $this->invoices->list(
            customerId: $request->has('customer_id') ? (int) $request->integer('customer_id') : null,
            perPage: $perPage > 0 ? $perPage : 15,
        );

        return $this->paginated($invoices, 'Invoices retrieved successfully');
    }

    /**
     * Display the invoices belonging to a specific customer.
     */
    public function forCustomer(Request $request, int $customer): JsonResponse
    {
        // Ensure the customer exists (throws 404 otherwise).
        $this->customers->find($customer);

        $perPage = (int) $request->integer('per_page', 15);
        $invoices = $this->invoices->list(
            customerId: $customer,
            perPage: $perPage > 0 ? $perPage : 15,
        );

        return $this->paginated($invoices, 'Customer invoices retrieved successfully');
    }

    /**
     * Store a newly created invoice.
     */
    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        $invoice = $this->invoices->create($request->validated());

        return ApiResponse::success(
            data: new InvoiceResource($invoice),
            message: 'Invoice created successfully',
            status: 201,
        );
    }

    /**
     * Display a single invoice.
     */
    public function show(int $invoice): JsonResponse
    {
        $model = $this->invoices->find($invoice);

        return ApiResponse::success(
            data: new InvoiceResource($model),
            message: 'Invoice retrieved successfully',
        );
    }

    /**
     * Update an existing invoice.
     */
    public function update(UpdateInvoiceRequest $request, int $invoice): JsonResponse
    {
        $model = $this->invoices->update($invoice, $request->validated());

        return ApiResponse::success(
            data: new InvoiceResource($model),
            message: 'Invoice updated successfully',
        );
    }

    /**
     * Remove an invoice.
     */
    public function destroy(int $invoice): JsonResponse
    {
        $this->invoices->delete($invoice);

        return ApiResponse::success(message: 'Invoice deleted successfully');
    }

    /**
     * Wrap a paginator in the standard envelope with pagination meta.
     */
    private function paginated(\Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator, string $message): JsonResponse
    {
        return ApiResponse::success(
            data: InvoiceResource::collection($paginator->items()),
            message: $message,
            meta: [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        );
    }
}
