<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Domain\VO\TravelDate;

final class TravelDateTest extends TestCase
{
    public function test_can_create_travel_date(): void
    {
        $vo = new TravelDate('2026-06-01');
        $this->assertSame('2026-06-01', (string) $vo);
    }

    public function test_invalid_date_throws_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new TravelDate('invalid-date');
    }
}
