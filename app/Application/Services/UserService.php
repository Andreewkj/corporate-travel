<?php
declare(strict_types=1);

namespace App\Application\Services;

use App\Application\DTO\User\CreateUserDTO;
use App\Models\User;

final class UserService
{
    public function createUser(CreateUserDTO $dto): User
    {
        return User::create([
            'name' => $dto->name,
            'email' => $dto->email,
            'password' => $dto->password,
        ]);
    }
}
