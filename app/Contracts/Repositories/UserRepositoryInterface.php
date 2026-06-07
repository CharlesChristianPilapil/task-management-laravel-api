<?php

namespace App\Contracts\Repositories;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    public function create(array $attributes): User;

    public function update(User $user, array $attributes): User;

    public function paginate(?string $role, ?bool $isActive, int $perPage): LengthAwarePaginator;

    public function toggleActiveStatus(User $user): User;
}
