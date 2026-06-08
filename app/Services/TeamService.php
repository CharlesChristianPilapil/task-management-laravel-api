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

    public function listTeams(User $actor, int $perPage): LengthAwarePaginator
    {
        return $this->teamRepository->paginate($perPage, $actor);
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
            $message = 'User is already a member of this team.';
            throw ApiException::make($message, 422);
        }

        $role = $data['role'] ?? TeamMemberRole::Member->value;

        $this->teamRepository->attachMember($team, $user, $role);

        return $this->teamRepository->findWithRelations($team);
    }

    public function removeMember(User $actor, Team $team, User $member): Team
    {
        $this->assertCanManageTeamMembers($actor, $team);

        if (! $team->hasMember($member)) {
            $message = 'User is not a member of this team.';
            throw ApiException::make($message, 422);
        }

        if ($team->created_by === $member->id && $team->memberRole($member) === TeamMemberRole::Lead) {
            $message = 'The team owner cannot be removed.';
            throw ApiException::make($message, 422);
        }

        $this->teamRepository->detachMember($team, $member);

        return $this->teamRepository->findWithRelations($team);
    }

    private function assertCanViewTeam(User $actor, Team $team): void
    {
        if ($actor->isAdmin()) {
            return;
        }

        if (! $actor->belongsToTeam($team)) {
            $message = 'You are not a member of this team.';
            throw ApiException::make($message, 403);
        }
    }

    private function assertCanManageTeamMembers(User $actor, Team $team): void
    {
        if (! $actor->canManageTeamMembers($team)) {
            $message = 'Only team leads and admins can manage team members.';
            throw ApiException::make($message, 403);
        }
    }
}
