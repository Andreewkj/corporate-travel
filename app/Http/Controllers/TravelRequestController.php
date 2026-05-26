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
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

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
            $user = $request->user();
            $data = $request->validated();
            $data['user_id'] = $user->id;

            $travelRequestDto = $this->validator->validate($data);

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

    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $found = $this->service->findForUser($id, $request->user()->id);
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

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->validate([
                'status' => 'sometimes|in:solicitado,aprovado,cancelado',
                'destination' => 'sometimes|string|max:255',
                'start_date' => 'sometimes|date',
                'end_date' => 'sometimes|date|after_or_equal:start_date',
            ]);
            $filters['user_id'] = $request->user()->id;

            $all = $this->service->all($filters);

            return response()->json(
                array_map(fn($e) => $e->toArray(), $all),
                HttpStatusCodeEnum::OK->value
            );
        } catch (ValidationException $e) {
            return response()->json(
                [
                    'message' => 'Invalid filters',
                    'errors' => $e->errors(),
                ],
                HttpStatusCodeEnum::UNPROCESSABLE_ENTITY->value
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

            $updated = $this->service->updateStatus($id, $status, $request->user());

            return response()->json(
                [
                    'message' => 'Status updated',
                    'data' => $updated->toArray(),
                ],
                HttpStatusCodeEnum::OK->value
            );
        } catch (AuthorizationException $e) {
            return response()->json(
                ['message' => $e->getMessage()],
                HttpStatusCodeEnum::FORBIDDEN->value
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

}
