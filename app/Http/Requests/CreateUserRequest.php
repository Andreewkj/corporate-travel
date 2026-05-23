<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Application\DTO\User\CreateUserDTO;
use InvalidArgumentException;

final class CreateUserRequest
{
    public function validate(array $data): CreateUserDTO
    {
        if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
            throw new InvalidArgumentException('Name, email and password are required');
        }

        return new CreateUserDTO($data['name'], $data['email'], $data['password']);
    }
}
