<?php

declare(strict_types=1);

namespace App\Application\DTO\Travel;

final readonly class TravelRequestNotificationDTO
{
    public function __construct(
        public ?int $travelRequestId,
        public string $status,
        public string $message,
        public string $email
    ) {}

    public function toArray(): array
    {
        return [
            'travel_request_id' => $this->travelRequestId,
            'status' => $this->status,
            'message' => $this->message,
            'email' => $this->email,
        ];
    }
}
