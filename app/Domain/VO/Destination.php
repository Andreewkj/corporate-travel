<?php
declare(strict_types=1);

namespace App\Domain\VO;

use InvalidArgumentException;

final class Destination
{
    public function __construct(private string $value)
    {
        $value = trim($value);
        if ($value === '') {
            throw new InvalidArgumentException('Destination cannot be empty');
        }
        $this->value = $value;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
