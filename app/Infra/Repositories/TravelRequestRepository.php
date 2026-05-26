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

    public function all(array $filters = []): array
    {
        $query = TravelRequestModel::query();

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['destination'])) {
            $query->where('destination', 'like', '%' . $filters['destination'] . '%');
        }

        if (! empty($filters['start_date'])) {
            $query->whereDate('start_date', '>=', $filters['start_date']);
        }

        if (! empty($filters['end_date'])) {
            $query->whereDate('end_date', '<=', $filters['end_date']);
        }

        if (! empty($filters['user_id'])) {
            $query->where('user_id', (int) $filters['user_id']);
        }

        return array_map(fn ($m) => TravelRequest::fromArray($m->toArray()), $query->get()->all());
    }
}
