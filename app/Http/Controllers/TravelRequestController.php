<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Services\TravelRequestService;
use App\Domain\Contracts\CreateTravelRequestValidateInterface;
use App\Domain\Contracts\LoggerInterface;
use App\Domain\Enums\HttpStatusCodeEnum;
use App\Domain\Enums\TravelRequestStatusEnum;
use App\Domain\Exceptions\TravelRequestException;
use App\Http\Requests\CreateTravelRequest;
use App\Http\Requests\UpdateTravelStatusRequest;
use Exception;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

final class TravelRequestController extends Controller
{
    public function __construct(
        private readonly CreateTravelRequestValidateInterface $validator,
        private readonly TravelRequestService $service,
        private readonly LoggerInterface $logger
    ) {
    }

    public function store(CreateTravelRequest $request): JsonResponse
    {
        try {
            $travelRequestDto = $this->validator->validate($request->validated());

            $this->service->create($travelRequestDto);

            return response()->json(
                ['message' => 'Travel request created'],
                HttpStatusCodeEnum::CREATED->value
            );
        } catch (InvalidArgumentException | TravelRequestException $e) {
            return response()->json(
                ['message' => $e->getMessage()],
                HttpStatusCodeEnum::UNPROCESSABLE_ENTITY->value
            );
        } catch (Exception $e) {
            $msg = 'Error creating travel request: ' . $e->getMessage();
            $this->logger->error($msg);

            return response()->json(
                ['message' => 'Internal server error'],
                HttpStatusCodeEnum::INTERNAL_SERVER_ERROR->value
            );
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $found = $this->service->find($id);
            if (! $found) {
                return response()->json(['message' => 'Not found'], 404);
            }
            return response()->json(
                $found->toArray(),
                HttpStatusCodeEnum::OK->value
            );
        } catch (Exception $e) {
            $msg = 'Error fetching travel request: ' . $e->getMessage();
            $this->logger->error($msg);

            return response()->json(
                ['message' => 'Internal server error'],
                HttpStatusCodeEnum::INTERNAL_SERVER_ERROR->value
            );
        }
    }

    public function index(): JsonResponse
    {
        try {
            $all = $this->service->all();

            return response()->json(
                array_map(fn ($e) => $e->toArray(), $all),
                HttpStatusCodeEnum::OK->value
            );
        } catch (Exception $e) {
            $msg = 'Error listing travel requests: ' . $e->getMessage();
            $this->logger->error($msg);

            return response()->json(
                ['message' => 'Internal server error'],
                HttpStatusCodeEnum::INTERNAL_SERVER_ERROR->value
            );
        }
    }

    public function updateStatus(UpdateTravelStatusRequest $request, int $id): JsonResponse
    {
        try {
            $data = $request->validated();
            $status = TravelRequestStatusEnum::from($data['status']);

            $this->service->updateStatus($id, $status);

            return response()->json(
                ['message' => 'Status updated'],
                HttpStatusCodeEnum::OK->value
            );
        } catch (InvalidArgumentException | TravelRequestException $e) {
            return response()->json(
                ['message' => $e->getMessage()],
                HttpStatusCodeEnum::UNPROCESSABLE_ENTITY->value
            );
        } catch (Exception $e) {
            $msg = 'Error updating travel request status: ' . $e->getMessage();
            $this->logger->error($msg);

            return response()->json(
                ['message' => 'Internal server error'],
                HttpStatusCodeEnum::INTERNAL_SERVER_ERROR->value
            );
        }
    }

    public function cancelRequest(int $id): JsonResponse
    {
        try {
            $updated = $this->service->cancelRequest($id);

            return response()->json(
                $updated->toArray(),
                HttpStatusCodeEnum::OK->value
            );
        } catch (TravelRequestException $e) {
            return response()->json(
                ['message' => $e->getMessage()],
                HttpStatusCodeEnum::UNPROCESSABLE_ENTITY->value
            );
        } catch (Exception $e) {
            $msg = 'Error cancelling travel request: ' . $e->getMessage();
            $this->logger->error($msg);

            return response()->json(
                ['message' => 'Internal server error'],
                HttpStatusCodeEnum::INTERNAL_SERVER_ERROR->value
            );
        }
    }
}
