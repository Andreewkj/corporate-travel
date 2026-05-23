<?php

declare(strict_types=1);

namespace App\Http\Requests;

use InvalidArgumentException;

final class LoginUserRequest
{
    public function validate(array $data): array
    {
        if (empty($data['email']) || empty($data['password'])) {
            throw new InvalidArgumentException('Email and password are required');
        }

        return [
            'email' => $data['email'],
            'password' => $data['password'],
        ];
    }
}
