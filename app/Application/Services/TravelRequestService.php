<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\DTO\Travel\CreateTravelRequestDTO;
use App\Domain\Contracts\Repositories\TravelRequestRepositoryInterface;
use App\Domain\Entities\TravelRequest;
use App\Domain\Enums\TravelRequestStatusEnum;
use App\Domain\Exceptions\TravelRequestException;
use App\Infra\Messaging\MessageBusPublisher;
use Illuminate\Auth\Access\AuthorizationException;
use App\Models\User as UserModel;

final class TravelRequestService
{
    public function __construct(
        private TravelRequestRepositoryInterface $repository,
        private ?MessageBusPublisher $publisher = null
    ) {}

    public function create(CreateTravelRequestDTO $dto): TravelRequest
    {
        try {
            $travel = TravelRequest::fromArray([
                'destination' => $dto->destination,
                'start_date' => $dto->startDate,
                'end_date' => $dto->endDate,
                'user_id' => $dto->userId,
                'status' => TravelRequestStatusEnum::SOLICITADO->value,
            ]);

            return $this->repository->save($travel);
        } catch (\Throwable $e) {
            throw new TravelRequestException('Could not create travel request: ' . $e->getMessage());
        }
    }

    public function find(int $id): ?TravelRequest
    {
        return $this->repository->find($id);
    }

    public function findForUser(int $id, int $userId): ?TravelRequest
    {
        $travelRequest = $this->repository->find($id);

        if (! $travelRequest || $travelRequest->userId() !== $userId) {
            return null;
        }

        return $travelRequest;
    }

    public function all(array $filters = []): array
    {
        return $this->repository->all($filters);
    }

    public function updateStatus(int $id, TravelRequestStatusEnum $status, UserModel $actor): TravelRequest
    {
        $this->assertAdmin($actor);

        $travelRequest = $this->repository->find($id);

        if (! $travelRequest) {
            throw new TravelRequestException('Travel request not found');
        }

        $this->assertNotRequester($travelRequest, $actor);

        if ($status === TravelRequestStatusEnum::CANCELADO) {
            $this->assertCancelable($travelRequest);
        }

        $data = $travelRequest->toArray();
        $data['status'] = $status->value;

        $updated = TravelRequest::fromArray($data);

        $saved = $this->repository->save($updated);

        if ($this->publisher !== null && $this->shouldNotify($status)) {
            $this->publisher->publishNotification($this->notificationPayload($saved, $status));
        }

        return $saved;
    }

    private function assertCancelable(TravelRequest $travelRequest): void
    {
        if ($travelRequest->status() === TravelRequestStatusEnum::APROVADO) {
            throw new TravelRequestException('Cannot cancel an approved travel request');
        }
    }

    private function assertAdmin(UserModel $actor): void
    {
        if (! $actor->is_admin) {
            throw new AuthorizationException('Only administrators can update travel request status');
        }
    }

    private function assertNotRequester(TravelRequest $travelRequest, UserModel $actor): void
    {
        if ($travelRequest->userId() !== null && $travelRequest->userId() === $actor->id) {
            throw new AuthorizationException('The requester cannot update their own travel request status');
        }
    }

    private function shouldNotify(TravelRequestStatusEnum $status): bool
    {
        return in_array($status, [
            TravelRequestStatusEnum::APROVADO,
            TravelRequestStatusEnum::CANCELADO,
        ], true);
    }

    private function notificationPayload(TravelRequest $travelRequest, TravelRequestStatusEnum $status): array
    {
        $payload = $travelRequest->toArray();
        $payload['message'] = match ($status) {
            TravelRequestStatusEnum::APROVADO => 'Seu pedido de viagem foi aprovado',
            TravelRequestStatusEnum::CANCELADO => 'Seu pedido de viagem foi cancelado',
            default => 'Seu pedido de viagem foi atualizado',
        };

        if ($travelRequest->userId()) {
            $user = UserModel::find($travelRequest->userId());
            if ($user) {
                $payload['user_email'] = $user->email;
            }
        }

        return $payload;
    }
}
