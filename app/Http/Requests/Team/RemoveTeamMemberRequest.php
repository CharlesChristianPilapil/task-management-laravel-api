<?php

namespace App\Http\Requests\Team;

use App\Http\Requests\ApiFormRequest;
use App\Exceptions\ApiException;
use App\Models\Team;
use App\Models\User;
class RemoveTeamMemberRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        $team = $this->route('team');
        $user = $this->user();

        if (! $team instanceof Team || ! $user instanceof User) {
            throw ApiException::make('Team context is required to manage members.', 403);
        }

        if (! $user->canManageTeamMembers($team)) {
            throw ApiException::make('Only team leads and admins can manage team members.', 403);
        }

        return true;
    }

    public function rules(): array
    {
        return [];
    }
}
