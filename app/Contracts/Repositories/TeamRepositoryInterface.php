<?php

namespace App\Contracts\Repositories;

use App\Models\Team;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TeamRepositoryInterface
{
    public function create(array $attributes): Team;

    public function paginate(int $perPage): LengthAwarePaginator;

    public function findWithRelations(Team $team): Team;

    public function attachMember(Team $team, User $user, string $role): void;

    public function detachMember(Team $team, User $user): void;
}
