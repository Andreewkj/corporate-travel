<?php

declare(strict_types=1);

namespace App\Application\DTO\User;

final class CreateUserDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password
    ) {}
}
