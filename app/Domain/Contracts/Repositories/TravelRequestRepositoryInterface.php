<?php

declare(strict_types=1);

namespace App\Domain\Contracts\Repositories;

use App\Domain\Entities\TravelRequest;

interface TravelRequestRepositoryInterface
{
    public function save(TravelRequest $travelRequest): TravelRequest;

    public function find(int $id): ?TravelRequest;

    /**
     * @return TravelRequest[]
     */
    public function all(array $filters = []): array;
}
