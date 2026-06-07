<?php

namespace App\Http\Controllers;

use App\DataTransferObjects\UserListFilters;
use App\Http\Requests\User\ListUsersRequest;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        private UserService $userService,
    ) {}

    public function index(ListUsersRequest $request): JsonResponse
    {
        $filters = new UserListFilters(
            role: $request->validated('role'),
            status: $request->validated('status'),
        );

        $users = $this->userService->listUsers($filters, $request->perPage());

        return ApiResponse::paginated($users, UserResource::class, 'users', 'Users retrieved successfully.');
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->userService->createUser(
            $this->authenticatedUser($request),
            $request->validated(),
        );

        return ApiResponse::success(new UserResource($user), 'User created successfully.', 201);
    }

    public function show(Request $request, User $user): JsonResponse
    {
        return ApiResponse::success(
            new UserResource($this->userService->getUser($this->authenticatedUser($request), $user)),
            'User retrieved successfully.',
        );
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $updatedUser = $this->userService->updateUser(
            $this->authenticatedUser($request),
            $user,
            $request->validated(),
        );

        return ApiResponse::success(new UserResource($updatedUser), 'User updated successfully.');
    }

    public function toggleStatus(Request $request, User $user): JsonResponse
    {
        $updatedUser = $this->userService->toggleStatus(
            $this->authenticatedUser($request),
            $user,
        );

        return ApiResponse::success(new UserResource($updatedUser), 'User status updated successfully.');
    }

    private function authenticatedUser(Request $request): User
    {
        $user = $request->user();
        assert($user instanceof User);

        return $user;
    }
}
