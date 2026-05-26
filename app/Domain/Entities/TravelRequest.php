<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use App\Domain\Enums\TravelRequestStatusEnum;
use App\Domain\VO\Destination;
use App\Domain\VO\TravelDate;

final class TravelRequest
{
    public function __construct(
        private ?int $id,
        private Destination $destination,
        private TravelDate $startDate,
        private ?TravelDate $endDate,
        private TravelRequestStatusEnum $status = TravelRequestStatusEnum::SOLICITADO,
        private ?int $userId = null
    ) {}

    public function id(): ?int
    {
        return $this->id;
    }

    public function destination(): Destination
    {
        return $this->destination;
    }

    public function startDate(): TravelDate
    {
        return $this->startDate;
    }

    public function endDate(): ?TravelDate
    {
        return $this->endDate;
    }

    public function status(): TravelRequestStatusEnum
    {
        return $this->status;
    }

    public function userId(): ?int
    {
        return $this->userId;
    }

    public static function fromArray(array $data): self
    {
        $status = isset($data['status'])
            ? TravelRequestStatusEnum::from($data['status'])
            : TravelRequestStatusEnum::SOLICITADO;

        return new self(
            $data['id'] ?? null,
            new Destination($data['destination']),
            new TravelDate($data['start_date']),
            isset($data['end_date']) ? new TravelDate($data['end_date']) : null,
            $status,
            $data['user_id'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'destination' => (string) $this->destination,
            'start_date' => (string) $this->startDate,
            'end_date' => $this->endDate ? (string) $this->endDate : null,
            'status' => $this->status->value,
            'user_id' => $this->userId,
        ];
    }
}
