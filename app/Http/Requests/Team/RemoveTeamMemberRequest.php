<?php

namespace App\Http\Requests\Team;

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class RemoveTeamMemberRequest extends FormRequest
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
        return [];
    }
}
