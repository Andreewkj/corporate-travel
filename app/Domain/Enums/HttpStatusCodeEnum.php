<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum HttpStatusCodeEnum: int
{
    case OK = 200;
    case CREATED = 201;
    case UNPROCESSABLE_ENTITY = 422;
    case INTERNAL_SERVER_ERROR = 500;
}
