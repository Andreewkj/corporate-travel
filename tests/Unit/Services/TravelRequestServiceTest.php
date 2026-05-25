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
        $dto = new CreateTravelRequestDTO('Ana', 'Recife', '2026-06-01', '2026-06-05');

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

    public function test_cannot_cancel_after_approved(): void
    {
        $mockRepo = $this->createMock(TravelRequestRepositoryInterface::class);

        $mockRepo->method('find')
            ->willReturn(TravelRequest::fromArray([
                'id' => 1,
                'requester_name' => 'Ana',
                'destination' => 'Recife',
                'start_date' => '2026-06-01',
                'end_date' => '2026-06-05',
                'status' => 'aprovado',
            ]));

        $mockRepo->expects($this->never())->method('save');

        $service = new TravelRequestService($mockRepo);

        $this->expectException(\App\Domain\Exceptions\TravelRequestException::class);

        $service->updateStatus(1, \App\Domain\Enums\TravelRequestStatusEnum::CANCELADO);
    }

    public function test_cancel_sets_status_to_cancelado(): void
    {
        $mockRepo = $this->createMock(TravelRequestRepositoryInterface::class);

        $mockRepo->method('find')
            ->willReturn(TravelRequest::fromArray([
                'id' => 2,
                'requester_name' => 'Bruno',
                'destination' => 'Olinda',
                'start_date' => '2026-07-01',
                'end_date' => '2026-07-05',
                'status' => 'solicitado',
            ]));

        $mockRepo->expects($this->once())
            ->method('save')
            ->willReturn(TravelRequest::fromArray([
                'id' => 2,
                'requester_name' => 'Bruno',
                'destination' => 'Olinda',
                'start_date' => '2026-07-01',
                'end_date' => '2026-07-05',
                'status' => 'cancelado',
            ]));

        $service = new TravelRequestService($mockRepo);

        $updated = $service->updateStatus(2, \App\Domain\Enums\TravelRequestStatusEnum::CANCELADO);

        $this->assertSame('cancelado', $updated->toArray()['status']);
    }
}
