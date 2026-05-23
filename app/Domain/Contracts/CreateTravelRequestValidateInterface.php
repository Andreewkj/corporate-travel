<?php

namespace App\Domain\Contracts;

use App\Application\DTO\Travel\CreateTravelRequestDTO;

interface CreateTravelRequestValidateInterface
{
    public function validate(array $data): CreateTravelRequestDTO;
}
