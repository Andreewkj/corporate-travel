<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Application\Services\TravelRequestService;
use App\Domain\Contracts\Repositories\TravelRequestRepositoryInterface;
use App\Application\DTO\Travel\CreateTravelRequestDTO;
use App\Domain\Entities\TravelRequest;

final class TravelRequestServiceTest extends TestCase
{
    public function test_create_calls_repository_and_returns_entity(): void
    {
        $dto = new CreateTravelRequestDTO('Ana','Recife','2026-06-01','2026-06-05');

        $mockRepo = $this->createMock(TravelRequestRepositoryInterface::class);
        $mockRepo->expects($this->once())
            ->method('save')
            ->willReturn(TravelRequest::fromArray([
                'id' => 1,
                'requester_name' => 'Ana',
                'destination' => 'Recife',
                'start_date' => '2026-06-01',
                'end_date' => '2026-06-05',
                'status' => 'solicitado',
            ]));

        $service = new TravelRequestService($mockRepo);
        $created = $service->create($dto);

        $this->assertInstanceOf(TravelRequest::class, $created);
        $this->assertSame('Ana', (string) $created->requesterName());
    }
}
