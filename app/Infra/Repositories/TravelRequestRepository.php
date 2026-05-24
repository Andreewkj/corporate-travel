<?php

declare(strict_types=1);

namespace App\Infra\Repositories;

use App\Domain\Contracts\Repositories\TravelRequestRepositoryInterface;
use App\Domain\Entities\TravelRequest;
use App\Models\TravelRequestModel;

final class TravelRequestRepository implements TravelRequestRepositoryInterface
{
    public function save(TravelRequest $travelRequest): TravelRequest
    {
        $data = $travelRequest->toArray();

        $model = null;
        if ($data['id']) {
            $model = TravelRequestModel::find($data['id']);
            if ($model) {
                $model->update($data);
            }
        }

        if (! $model) {
            $model = TravelRequestModel::create($data);
        }

        return TravelRequest::fromArray($model->toArray());
    }

    public function find(int $id): ?TravelRequest
    {
        $model = TravelRequestModel::find($id);
        return $model ? TravelRequest::fromArray($model->toArray()) : null;
    }

    public function all(): array
    {
        return array_map(fn ($m) => TravelRequest::fromArray($m->toArray()), TravelRequestModel::all()->all());
    }
}
