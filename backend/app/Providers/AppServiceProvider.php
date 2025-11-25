<?php

namespace App\Providers;

use App\Interfaces\Event\EventRepositoryInterface;
use App\Repositories\Event\EventRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            EventRepositoryInterface::class,
            EventRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}
