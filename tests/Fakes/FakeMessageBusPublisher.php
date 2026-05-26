<?php

declare(strict_types=1);

namespace Tests\Fakes;

use App\Application\DTO\Travel\TravelRequestNotificationDTO;
use App\Infra\Messaging\MessageBusPublisher;

final class FakeMessageBusPublisher extends MessageBusPublisher
{
    public array $publishedPayloads = [];

    public function __construct()
    {
    }

    public function publishNotification(TravelRequestNotificationDTO $payload): void
    {
        $this->publishedPayloads[] = $payload->toArray();
    }
}
