<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\DTO\Travel\CreateTravelRequestDTO;
use App\Domain\Contracts\Repositories\TravelRequestRepositoryInterface;
use App\Domain\Entities\TravelRequest;
use App\Domain\Enums\TravelRequestStatusEnum;
use App\Domain\Exceptions\TravelRequestException;

final class TravelRequestService
{
    public function __construct(private TravelRequestRepositoryInterface $repository) {}

    /**
     * @throws TravelRequestException
     */
    public function create(CreateTravelRequestDTO $dto): TravelRequest
    {
        try {
            $travel = TravelRequest::fromArray([
                'requester_name' => $dto->requesterName,
                'destination' => $dto->destination,
                'start_date' => $dto->startDate,
                'end_date' => $dto->endDate,
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

    /**
     * @return TravelRequest[]
     */
    public function all(): array
    {
        return $this->repository->all();
    }

    /**
     * @throws TravelRequestException
     */
    public function updateStatus(int $id, TravelRequestStatusEnum $status): TravelRequest
    {
        $travelRequest = $this->repository->find($id);

        if (! $travelRequest) {
            throw new TravelRequestException('Travel request not found');
        }

        // Business rule: do not allow canceling a request that is already approved
        if ($status === TravelRequestStatusEnum::CANCELADO && $travelRequest->status() === TravelRequestStatusEnum::APROVADO) {
            throw new TravelRequestException('Cannot cancel an approved travel request');
        }

        $data = $travelRequest->toArray();
        $data['status'] = $status->value;

        $updated = TravelRequest::fromArray($data);

        return $this->repository->save($updated);
    }

    /**
     * Cancel a travel request if it's not approved yet.
     * Returns the updated entity. If already canceled, returns it unchanged.
     *
     * @throws TravelRequestException
     */
    public function cancelRequest(int $id): TravelRequest
    {
        $found = $this->repository->find($id);

        if (! $found) {
            throw new TravelRequestException('Travel request not found');
        }

        if ($found->status() === TravelRequestStatusEnum::APROVADO) {
            throw new TravelRequestException('Cannot cancel an approved travel request');
        }

        if ($found->status() === TravelRequestStatusEnum::CANCELADO) {
            return $found;
        }

        $data = $found->toArray();
        $data['status'] = TravelRequestStatusEnum::CANCELADO->value;

        $updated = TravelRequest::fromArray($data);

        return $this->repository->save($updated);
    }
}
