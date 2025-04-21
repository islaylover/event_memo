<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Repositories\EventRepositoryInterface;
use App\Domain\Repositories\AlertIntervalRepositoryInterface;
use App\Domain\Repositories\TagRepositoryInterface;
use App\Infrastructure\Repositories\EloquentEventRepository;
use App\Infrastructure\Repositories\EloquentAlertIntervalRepository;
use App\Infrastructure\Repositories\EloquentTagRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(EventRepositoryInterface::class, EloquentEventRepository::class);
        $this->app->bind(AlertIntervalRepositoryInterface::class, EloquentAlertIntervalRepository::class);
        $this->app->bind(TagRepositoryInterface::class, EloquentTagRepository::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
