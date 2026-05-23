<?php

namespace App\Domain\VO;

use Carbon\Carbon;
use InvalidArgumentException;

final class TravelDate
{
    private Carbon $date;

    public function __construct(string $value)
    {
        try {
            $this->date = Carbon::parse($value)->startOfDay();
        } catch (\Throwable $e) {
            throw new InvalidArgumentException('Invalid date');
        }
    }

    public function value(): Carbon
    {
        return $this->date;
    }

    public function __toString(): string
    {
        return $this->date->toDateString();
    }
}
