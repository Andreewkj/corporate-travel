<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum TravelRequestStatusEnum: string
{
    case SOLICITADO = 'solicitado';
    case APROVADO = 'aprovado';
    case CANCELADO = 'cancelado';
}
