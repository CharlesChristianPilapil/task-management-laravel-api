<?php

namespace App\Services;

use App\Contracts\Repositories\TeamRepositoryInterface;
use App\Enums\TeamMemberRole;
use App\Exceptions\ApiException;
use App\Models\Team;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TeamService
{
    public function __construct(
        private TeamRepositoryInterface $teamRepository,
    ) {}

    public function listTeams(int $perPage): LengthAwarePaginator
    {
        return $this->teamRepository->paginate($perPage);
    }

    public function createTeam(User $actor, array $data): Team
    {
        $team = $this->teamRepository->create([
            'name' => $data['name'],
            'created_by' => $actor->id,
        ]);

        $this->teamRepository->attachMember($team, $actor, TeamMemberRole::Lead->value);

        return $this->teamRepository->findWithRelations($team);
    }

    public function getTeam(User $actor, Team $team): Team
    {
        $this->assertCanViewTeam($actor, $team);

        return $this->teamRepository->findWithRelations($team);
    }

    public function addMember(User $actor, Team $team, array $data): Team
    {
        $this->assertCanManageTeamMembers($actor, $team);

        $user = User::findOrFail($data['user_id']);

        if ($team->hasMember($user)) {
            throw ApiException::make('User is already a member of this team.', 422);
        }

        $role = $data['role'] ?? TeamMemberRole::Member->value;

        $this->teamRepository->attachMember($team, $user, $role);

        return $this->teamRepository->findWithRelations($team);
    }

    public function removeMember(User $actor, Team $team, User $member): Team
    {
        $this->assertCanManageTeamMembers($actor, $team);

        if (! $team->hasMember($member)) {
            throw ApiException::make('User is not a member of this team.', 422);
        }

        if ($team->created_by === $member->id && $team->memberRole($member) === TeamMemberRole::Lead) {
            throw ApiException::make('The team owner cannot be removed.', 422);
        }

        $this->teamRepository->detachMember($team, $member);

        return $this->teamRepository->findWithRelations($team);
    }

    private function assertCanViewTeam(User $actor, Team $team): void
    {
        if ($actor->canManageUsers()) {
            return;
        }

        if (! $actor->belongsToTeam($team)) {
            throw ApiException::make('Forbidden.', 403);
        }
    }

    private function assertCanManageTeamMembers(User $actor, Team $team): void
    {
        if (! $actor->canManageTeamMembers($team)) {
            throw ApiException::make('Forbidden.', 403);
        }
    }
}
