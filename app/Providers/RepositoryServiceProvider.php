<?php

namespace App\Providers;

use App\Repositories\Contracts\CustomerRepositoryInterface;
use App\Repositories\Contracts\InvoiceRepositoryInterface;
use App\Repositories\CustomerRepository;
use App\Repositories\InvoiceRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Repository contract => concrete implementation bindings.
     *
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        CustomerRepositoryInterface::class => CustomerRepository::class,
        InvoiceRepositoryInterface::class => InvoiceRepository::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
