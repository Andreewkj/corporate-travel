<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Application\DTO\Travel\CreateTravelRequestDTO;
use App\Domain\Contracts\CreateTravelRequestValidateInterface;
use InvalidArgumentException;

readonly class CreateTravelRequestValidator implements CreateTravelRequestValidateInterface
{
    public function validate(array $data): CreateTravelRequestDTO
    {
        if (empty($data['requester_name'])) {
            throw new InvalidArgumentException('Requester name is required');
        }

        if (empty($data['destination'])) {
            throw new InvalidArgumentException('Destination is required');
        }

        if (empty($data['start_date'])) {
            throw new InvalidArgumentException('Start date is required');
        }

        if (empty($data['end_date'])) {
            throw new InvalidArgumentException('End date is required');
        }

        return new CreateTravelRequestDTO(
            $data['requester_name'],
            $data['destination'],
            $data['start_date'],
            $data['end_date']
        );
    }
}
