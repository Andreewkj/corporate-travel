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
            throw new TravelRequestException('Could not create travel request: '.$e->getMessage());
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
}
