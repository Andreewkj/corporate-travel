<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Domain\VO\Destination;

final class DestinationTest extends TestCase
{
    public function test_can_create_destination(): void
    {
        $vo = new Destination('São Paulo');
        $this->assertSame('São Paulo', $vo->value());
    }

    public function test_empty_destination_throws_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Destination('');
    }
}
