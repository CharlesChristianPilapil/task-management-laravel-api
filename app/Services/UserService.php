<?php

namespace App\Services;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\DataTransferObjects\UserListFilters;
use App\Enums\UserRole;
use App\Exceptions\ApiException;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {}

    public function listUsers(UserListFilters $filters, int $perPage): LengthAwarePaginator
    {
        return $this->userRepository->paginate(
            role: $filters->role,
            isActive: $filters->isActiveFilter(),
            perPage: $perPage,
        );
    }

    public function createUser(User $actor, array $data): User
    {
        $role = $data['role'] ?? UserRole::TeamMember->value;

        if ($actor->isManager() && $role !== UserRole::TeamMember->value) {
            throw ApiException::make('Forbidden.', 403);
        }

        return $this->userRepository->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $role,
            'is_active' => true,
        ]);
    }

    public function getUser(User $actor, User $target): User
    {
        $this->assertCanManageTarget($actor, $target);

        return $target;
    }

    public function updateUser(User $actor, User $target, array $data): User
    {
        $this->assertCanManageTarget($actor, $target);

        if ($actor->isManager() && isset($data['role']) && $data['role'] !== UserRole::TeamMember->value) {
            throw ApiException::make('Forbidden.', 403);
        }

        return $this->userRepository->update($target, $data);
    }

    public function toggleStatus(User $actor, User $target): User
    {
        $this->assertCanManageTarget($actor, $target);

        if ($actor->id === $target->id) {
            throw ApiException::make('You cannot deactivate your own account.', 422);
        }

        return $this->userRepository->toggleActiveStatus($target);
    }

    private function assertCanManageTarget(User $actor, User $target): void
    {
        if (! $actor->canManageUsers()) {
            throw ApiException::make('Forbidden.', 403);
        }

        if ($actor->isManager() && ! $target->isTeamMember()) {
            throw ApiException::make('Forbidden.', 403);
        }
    }
}
