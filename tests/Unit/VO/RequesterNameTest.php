<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Domain\VO\RequesterName;
use InvalidArgumentException;

final class RequesterNameTest extends TestCase
{
    public function test_can_create_requester_name(): void
    {
        $vo = new RequesterName('João');
        $this->assertSame('João', $vo->value());
        $this->assertSame('João', (string) $vo);
    }

    public function test_empty_name_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new RequesterName('   ');
    }
}
