<?php

declare(strict_types=1);

namespace App\Providers;

use App\Infra\Messaging\MessageBusPublisher;
use App\Infra\Messaging\RabbitMQChannelFactory;
use App\Infra\Messaging\RabbitMQConnectionFactory;
use Illuminate\Support\ServiceProvider;

class MessagingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(RabbitMQChannelFactory::class);
        $this->app->singleton(RabbitMQConnectionFactory::class);
        $this->app->singleton(MessageBusPublisher::class);
    }

    public function boot(): void
    {
        //
    }
}
