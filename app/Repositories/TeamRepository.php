<?php

namespace App\Repositories;

use App\Contracts\Repositories\TeamRepositoryInterface;
use App\Models\Team;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TeamRepository implements TeamRepositoryInterface
{
    public function create(array $attributes): Team
    {
        return Team::create($attributes);
    }

    public function paginate(int $perPage): LengthAwarePaginator
    {
        return Team::query()
            ->with('creator')
            ->withCount('members')
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function findWithRelations(Team $team): Team
    {
        return $team->fresh(['creator', 'members']);
    }

    public function attachMember(Team $team, User $user, string $role): void
    {
        $team->members()->syncWithoutDetaching([
            $user->id => ['role' => $role],
        ]);
    }

    public function detachMember(Team $team, User $user): void
    {
        $team->members()->detach($user->id);
    }
}
