<?php

declare(strict_types=1);

namespace App\Application\DTO\Travel;

final class CreateTravelRequestDTO
{
    public function __construct(
        public string $requesterName,
        public string $destination,
        public string $startDate,
        public string $endDate
    ) {}
}
