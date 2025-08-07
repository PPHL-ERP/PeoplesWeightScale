<?php

namespace App\Providers;

use App\Services\AccountsCreditService;
use App\Services\AccountsDebitService;
use App\Services\AddAccountLedgerService;
use App\Services\InvoiceWisePaymentService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\CacheService::class, function ($app) {
            return new \App\Services\CacheService();
        });

        $this->app->singleton(AccountsDebitService::class, function ($app) {
            return new AccountsDebitService();
        });

        $this->app->singleton(AccountsCreditService::class, function ($app) {
            return new AccountsCreditService();
        });

        $this->app->singleton(AddAccountLedgerService::class, function ($app) {
            return new AddAccountLedgerService();
        });

        $this->app->singleton(InvoiceWisePaymentService::class, function ($app) {
            return new InvoiceWisePaymentService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrap();
    }
}
