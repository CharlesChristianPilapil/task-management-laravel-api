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
            $message = 'Managers can only create users with the team member role.';
            throw ApiException::make($message, 403);
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
            $message = 'Managers can only assign the team member role.';
            throw ApiException::make($message, 403);
        }

        return $this->userRepository->update($target, $data);
    }

    public function toggleStatus(User $actor, User $target): User
    {
        $this->assertCanManageTarget($actor, $target);

        if ($actor->id === $target->id) {
            $message = 'You cannot deactivate your own account.';
            throw ApiException::make($message, 422);
        }

        return $this->userRepository->toggleActiveStatus($target);
    }

    private function assertCanManageTarget(User $actor, User $target): void
    {
        if (! $actor->canManageUsers()) {
            $message = 'You do not have permission to manage users.';
            throw ApiException::make($message, 403);
        }

        if ($actor->isManager() && ! $target->isTeamMember()) {
            $message = 'Managers can only manage team member accounts.';
            throw ApiException::make($message, 403);
        }
    }
}
