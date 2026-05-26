<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Domain\Entities\TravelRequest;

final class TravelRequestEntityTest extends TestCase
{
    public function test_from_array_and_to_array_roundtrip(): void
    {
        $data = [
            'id' => 123,
            'destination' => 'Recife',
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-05',
            'status' => 'solicitado',
        ];

        $entity = TravelRequest::fromArray($data);
        $this->assertSame(123, $entity->id());
        $this->assertSame('solicitado', $entity->status()->value);

        $array = $entity->toArray();
        $this->assertSame($data['destination'], $array['destination']);
        $this->assertSame($data['start_date'], $array['start_date']);
        $this->assertSame($data['end_date'], $array['end_date']);
    }
}
