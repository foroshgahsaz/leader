<?php

namespace App\Providers;

use App\Contracts\Repositories\BuyerRepositoryInterface;
use App\Contracts\Repositories\BuyerSearchRepositoryInterface;
use App\Contracts\Repositories\CompanyRepositoryInterface;
use App\Contracts\Repositories\DealRepositoryInterface;
use App\Contracts\Repositories\GlobalBuyerRepositoryInterface;
use App\Contracts\Repositories\LeadListRepositoryInterface;
use App\Contracts\Repositories\SavedSearchRepositoryInterface;
use App\Repositories\Eloquent\BuyerRepository;
use App\Repositories\Eloquent\BuyerSearchRepository;
use App\Repositories\Eloquent\CompanyRepository;
use App\Repositories\Eloquent\DealRepository;
use App\Repositories\Eloquent\GlobalBuyerRepository;
use App\Repositories\Eloquent\LeadListRepository;
use App\Repositories\Eloquent\SavedSearchRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        BuyerRepositoryInterface::class => BuyerRepository::class,
        GlobalBuyerRepositoryInterface::class => GlobalBuyerRepository::class,
        BuyerSearchRepositoryInterface::class => BuyerSearchRepository::class,
        SavedSearchRepositoryInterface::class => SavedSearchRepository::class,
        LeadListRepositoryInterface::class => LeadListRepository::class,
        CompanyRepositoryInterface::class => CompanyRepository::class,
        DealRepositoryInterface::class => DealRepository::class,
    ];

    public function register(): void
    {
        foreach ($this->bindings as $abstract => $concrete) {
            $this->app->bind($abstract, $concrete);
        }
    }
}
