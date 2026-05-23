<?php

namespace App\Http\Controllers;

use App\Application\Services\TravelRequestService;
use App\Domain\Contracts\CreateTravelRequestValidateInterface;
use App\Domain\Contracts\LoggerInterface;
use App\Domain\Enums\HttpStatusCodeEnum;
use App\Domain\Exceptions\TravelRequestException;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\CreateTravelRequest;
use InvalidArgumentException;
use Exception;

final class TravelRequestController extends Controller
{
    public function __construct(
        private readonly CreateTravelRequestValidateInterface $validator,
        private readonly TravelRequestService $service,
        private readonly LoggerInterface $logger
    ) {}

    public function store(CreateTravelRequest $request): JsonResponse
    {
        try {
            $travelRequestDto = $this->validator->validate($request->validated());

            $this->service->create($travelRequestDto);

            return response()->json(['message' => 'Travel request created'], HttpStatusCodeEnum::CREATED->value);
        } catch (InvalidArgumentException | TravelRequestException $e) {
            return response()->json(['message' => $e->getMessage()], HttpStatusCodeEnum::UNPROCESSABLE_ENTITY->value);
        } catch (Exception $e) {
            $this->logger->error('Error creating travel request: ' . $e->getMessage());
            return response()->json(['message' => 'Internal server error'], HttpStatusCodeEnum::INTERNAL_SERVER_ERROR->value);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $found = $this->service->find($id);
            if (! $found) {
                return response()->json(['message' => 'Not found'], 404);
            }
            return response()->json($found->toArray(), HttpStatusCodeEnum::OK->value);
        } catch (Exception $e) {
            $this->logger->error('Error fetching travel request: ' . $e->getMessage());
            return response()->json(['message' => 'Internal server error'], HttpStatusCodeEnum::INTERNAL_SERVER_ERROR->value);
        }
    }

    public function index(): JsonResponse
    {
        try {
            $all = $this->service->all();
            return response()->json(array_map(fn($e) => $e->toArray(), $all), HttpStatusCodeEnum::OK->value);
        } catch (Exception $e) {
            $this->logger->error('Error listing travel requests: ' . $e->getMessage());
            return response()->json(['message' => 'Internal server error'], HttpStatusCodeEnum::INTERNAL_SERVER_ERROR->value);
        }
    }
}
