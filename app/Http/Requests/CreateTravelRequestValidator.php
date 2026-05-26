<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Application\DTO\Travel\CreateTravelRequestDTO;
use App\Domain\Contracts\CreateTravelRequestValidateInterface;
readonly class CreateTravelRequestValidator implements CreateTravelRequestValidateInterface
{
    public function validate(array $data): CreateTravelRequestDTO
    {
        if (empty($data['destination'])) {
            throw new \InvalidArgumentException('Destination is required');
        }

        if (empty($data['start_date'])) {
            throw new \InvalidArgumentException('Start date is required');
        }

        if (empty($data['end_date'])) {
            throw new \InvalidArgumentException('End date is required');
        }

        return new CreateTravelRequestDTO(
            $data['destination'],
            $data['start_date'],
            $data['end_date'],
            $data['user_id'] ?? null
        );
    }
}
