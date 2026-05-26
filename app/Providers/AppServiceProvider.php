<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Contracts\Repositories\TravelRequestRepositoryInterface;
use App\Infra\Repositories\TravelRequestRepository;
use App\Domain\Contracts\CreateTravelRequestValidateInterface;
use App\Http\Requests\CreateTravelRequestValidator;
use App\Domain\Contracts\LoggerInterface;
use App\Infra\Adapters\LaravelLoggerAdapter;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TravelRequestRepositoryInterface::class, TravelRequestRepository::class);
        $this->app->bind(CreateTravelRequestValidateInterface::class, CreateTravelRequestValidator::class);
        $this->app->bind(LoggerInterface::class, LaravelLoggerAdapter::class);
    }

    public function boot(): void
    {
    }
}
