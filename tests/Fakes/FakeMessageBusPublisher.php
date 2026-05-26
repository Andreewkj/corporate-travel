<?php

declare(strict_types=1);

namespace Tests\Fakes;

use App\Infra\Messaging\MessageBusPublisher;

final class FakeMessageBusPublisher extends MessageBusPublisher
{
    public array $publishedPayloads = [];

    public function __construct()
    {
    }

    public function publishNotification(array $payload): void
    {
        $this->publishedPayloads[] = $payload;
    }
}
