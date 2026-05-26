<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Application\Services\TravelRequestService;
use App\Domain\Contracts\Repositories\TravelRequestRepositoryInterface;
use App\Application\DTO\Travel\CreateTravelRequestDTO;
use App\Domain\Entities\TravelRequest;
use App\Models\User;

final class TravelRequestServiceTest extends TestCase
{
    public function test_create_calls_repository_and_returns_entity(): void
    {
        $dto = new CreateTravelRequestDTO('Recife', '2026-06-01', '2026-06-05');

        $mockRepo = $this->createMock(TravelRequestRepositoryInterface::class);
        $mockRepo->expects($this->once())
            ->method('save')
            ->willReturn(TravelRequest::fromArray([
                'id' => 1,
                'destination' => 'Recife',
                'start_date' => '2026-06-01',
                'end_date' => '2026-06-05',
                'status' => 'solicitado',
            ]));

        $service = new TravelRequestService($mockRepo);
        $created = $service->create($dto);

        $this->assertInstanceOf(TravelRequest::class, $created);
        $this->assertSame('Recife', (string) $created->destination());
    }

    public function test_cannot_cancel_after_approved(): void
    {
        $mockRepo = $this->createMock(TravelRequestRepositoryInterface::class);

        $mockRepo->method('find')
            ->willReturn(TravelRequest::fromArray([
                'id' => 1,
                'destination' => 'Recife',
                'start_date' => '2026-06-01',
                'end_date' => '2026-06-05',
                'status' => 'aprovado',
            ]));

        $mockRepo->expects($this->never())->method('save');

        $service = new TravelRequestService($mockRepo);

        $this->expectException(\App\Domain\Exceptions\TravelRequestException::class);

        $service->updateStatus(1, \App\Domain\Enums\TravelRequestStatusEnum::CANCELADO, $this->user(99, true));
    }

    public function test_cannot_approve_after_canceled(): void
    {
        $mockRepo = $this->createMock(TravelRequestRepositoryInterface::class);

        $mockRepo->method('find')
            ->willReturn(TravelRequest::fromArray([
                'id' => 1,
                'destination' => 'Recife',
                'start_date' => '2026-06-01',
                'end_date' => '2026-06-05',
                'status' => 'cancelado',
            ]));

        $mockRepo->expects($this->never())->method('save');

        $service = new TravelRequestService($mockRepo);

        $this->expectException(\App\Domain\Exceptions\TravelRequestException::class);
        $this->expectExceptionMessage('Cannot approve a canceled travel request');

        $service->updateStatus(1, \App\Domain\Enums\TravelRequestStatusEnum::APROVADO, $this->user(99, true));
    }

    public function test_cannot_update_to_same_status(): void
    {
        $mockRepo = $this->createMock(TravelRequestRepositoryInterface::class);

        $mockRepo->method('find')
            ->willReturn(TravelRequest::fromArray([
                'id' => 1,
                'destination' => 'Recife',
                'start_date' => '2026-06-01',
                'end_date' => '2026-06-05',
                'status' => 'aprovado',
            ]));

        $mockRepo->expects($this->never())->method('save');

        $service = new TravelRequestService($mockRepo);

        $this->expectException(\App\Domain\Exceptions\TravelRequestException::class);
        $this->expectExceptionMessage('Travel request already has this status');

        $service->updateStatus(1, \App\Domain\Enums\TravelRequestStatusEnum::APROVADO, $this->user(99, true));
    }

    public function test_cancel_sets_status_to_cancelado(): void
    {
        $mockRepo = $this->createMock(TravelRequestRepositoryInterface::class);

        $mockRepo->method('find')
            ->willReturn(TravelRequest::fromArray([
                'id' => 2,
                'destination' => 'Olinda',
                'start_date' => '2026-07-01',
                'end_date' => '2026-07-05',
                'status' => 'solicitado',
                'user_id' => 10,
            ]));

        $mockRepo->expects($this->once())
            ->method('save')
            ->willReturn(TravelRequest::fromArray([
                'id' => 2,
                'destination' => 'Olinda',
                'start_date' => '2026-07-01',
                'end_date' => '2026-07-05',
                'status' => 'cancelado',
            ]));

        $service = new TravelRequestService($mockRepo);

        $updated = $service->updateStatus(2, \App\Domain\Enums\TravelRequestStatusEnum::CANCELADO, $this->user(99, true));

        $this->assertSame('cancelado', $updated->toArray()['status']);
    }

    public function test_find_for_user_returns_only_owned_request(): void
    {
        $mockRepo = $this->createMock(TravelRequestRepositoryInterface::class);

        $mockRepo->method('find')
            ->willReturn(TravelRequest::fromArray([
                'id' => 2,
                'destination' => 'Olinda',
                'start_date' => '2026-07-01',
                'end_date' => '2026-07-05',
                'status' => 'solicitado',
                'user_id' => 10,
            ]));

        $service = new TravelRequestService($mockRepo);

        $this->assertInstanceOf(TravelRequest::class, $service->findForUser(2, 10));
        $this->assertNull($service->findForUser(2, 99));
    }

    public function test_non_admin_cannot_update_status(): void
    {
        $mockRepo = $this->createMock(TravelRequestRepositoryInterface::class);

        $mockRepo->method('find')
            ->willReturn(TravelRequest::fromArray([
                'id' => 3,
                'destination' => 'Sao Paulo',
                'start_date' => '2026-08-01',
                'end_date' => '2026-08-05',
                'status' => 'solicitado',
                'user_id' => 10,
            ]));

        $mockRepo->expects($this->never())->method('save');

        $service = new TravelRequestService($mockRepo);

        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);

        $service->updateStatus(3, \App\Domain\Enums\TravelRequestStatusEnum::APROVADO, $this->user(99, false));
    }

    public function test_admin_can_update_own_status(): void
    {
        $mockRepo = $this->createMock(TravelRequestRepositoryInterface::class);

        $mockRepo->method('find')
            ->willReturn(TravelRequest::fromArray([
                'id' => 4,
                'destination' => 'Rio',
                'start_date' => '2026-09-01',
                'end_date' => '2026-09-05',
                'status' => 'solicitado',
                'user_id' => 99,
            ]));

        $mockRepo->expects($this->once())
            ->method('save')
            ->willReturn(TravelRequest::fromArray([
                'id' => 4,
                'destination' => 'Rio',
                'start_date' => '2026-09-01',
                'end_date' => '2026-09-05',
                'status' => 'aprovado',
                'user_id' => 99,
            ]));

        $service = new TravelRequestService($mockRepo);

        $updated = $service->updateStatus(4, \App\Domain\Enums\TravelRequestStatusEnum::APROVADO, $this->user(99, true));

        $this->assertSame('aprovado', $updated->toArray()['status']);
    }

    private function user(int $id, bool $isAdmin): User
    {
        $user = new User();
        $user->id = $id;
        $user->is_admin = $isAdmin;

        return $user;
    }
}
