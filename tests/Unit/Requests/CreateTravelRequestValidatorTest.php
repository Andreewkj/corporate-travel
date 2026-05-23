<?php

use PHPUnit\Framework\TestCase;
use App\Http\Requests\CreateTravelRequestValidator;
use InvalidArgumentException;

final class CreateTravelRequestValidatorTest extends TestCase
{
    public function test_validator_accepts_valid_data(): void
    {
        $validator = new CreateTravelRequestValidator();
        $dto = $validator->validate([
            'requester_name' => 'Luiz',
            'destination' => 'Brasília',
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-05',
        ]);

        $this->assertSame('Luiz', $dto->requesterName);
        $this->assertSame('Brasília', $dto->destination);
    }

    public function test_validator_missing_fields_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $validator = new CreateTravelRequestValidator();
        $validator->validate(['requester_name' => '']);
    }
}
