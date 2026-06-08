<?php

namespace App\Http\Requests\Team;

use App\Enums\TeamMemberRole;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddTeamMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        $team = $this->route('team');
        $user = $this->user();

        if (! $team instanceof Team || ! $user instanceof User) {
            return false;
        }

        return $user->canManageTeamMembers($team);
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'role' => ['sometimes', 'string', Rule::in(TeamMemberRole::values())],
        ];
    }
}
