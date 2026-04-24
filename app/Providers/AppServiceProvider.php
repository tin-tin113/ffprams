<?php

namespace App\Providers;

use App\Models\Agency;
use App\Models\Allocation;
use App\Models\Barangay;
use App\Models\Beneficiary;
use App\Models\DirectAssistance;
use App\Models\DistributionEvent;
use App\Models\ProgramName;
use App\Models\ResourceType;
use App\Observers\GeoMapCacheObserver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Services\NotificationService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // Keep geo-map cache fresh by bumping cache version on relevant data changes.
        Agency::observe(GeoMapCacheObserver::class);
        Beneficiary::observe(GeoMapCacheObserver::class);
        ProgramName::observe(GeoMapCacheObserver::class);
        DistributionEvent::observe(GeoMapCacheObserver::class);
        Allocation::observe(GeoMapCacheObserver::class);
        DirectAssistance::observe(GeoMapCacheObserver::class);
        Barangay::observe(GeoMapCacheObserver::class);
        ResourceType::observe(GeoMapCacheObserver::class);

        // Share notifications globally across all views
        View::composer('*', function ($view) {
            $notificationService = app(NotificationService::class);
            $view->with('globalAlerts', $notificationService->getAttentionRequiredEvents());
        });
    }
}
