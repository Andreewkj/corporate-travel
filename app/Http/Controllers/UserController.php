<?php

namespace App\Http\Controllers;

use App\Application\DTO\User\CreateUserDTO;
use App\Application\Services\UserService;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\LoginUserRequest;
use App\Domain\Contracts\LoggerInterface;
use App\Domain\Enums\HttpStatusCodeEnum;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;
use Exception;

final class UserController extends Controller
{
    public function __construct(
        private readonly CreateUserRequest $createUserRequest,
        private readonly UserService $userService,
        private readonly LoginUserRequest $loginUserRequest,
        private readonly LoggerInterface $logger
    ) {}

    public function login(Request $request): JsonResponse
    {
        try {
            $credentials = $this->loginUserRequest->validate($request->only('email', 'password'));

            if (Auth::attempt($credentials)) {
                $token = $request->user()->createToken('apiToken')->plainTextToken;
                return response()->json(['token' => $token], HttpStatusCodeEnum::OK->value);
            }

            return response()->json(['message' => 'Invalid credentials'], 401);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], HttpStatusCodeEnum::UNPROCESSABLE_ENTITY->value);
        } catch (Exception $e) {
            $this->logger->error('Error logging in user: '.$e->getMessage());
            return response()->json(['message' => 'Internal server error'], HttpStatusCodeEnum::INTERNAL_SERVER_ERROR->value);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $dto = $this->createUserRequest->validate($request->all());
            $user = $this->userService->createUser(new CreateUserDTO($dto->name, $dto->email, $dto->password));

            return response()->json(['id' => $user->id], HttpStatusCodeEnum::CREATED->value);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], HttpStatusCodeEnum::UNPROCESSABLE_ENTITY->value);
        } catch (Exception $e) {
            $this->logger->error('Error creating user: '.$e->getMessage());
            return response()->json(['message' => 'Internal server error'], HttpStatusCodeEnum::INTERNAL_SERVER_ERROR->value);
        }
    }
}
