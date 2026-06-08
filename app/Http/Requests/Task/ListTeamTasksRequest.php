<?php

namespace App\Http\Requests\Task;

use App\Http\Requests\ApiFormRequest;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Exceptions\ApiException;
use App\Models\Team;
use App\Models\User;
use Illuminate\Validation\Rule;

class ListTeamTasksRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        $team = $this->route('team');
        $user = $this->user();

        if (! $team instanceof Team || ! $user instanceof User) {
            throw ApiException::make('Team context is required to list tasks.', 403);
        }

        if ($user->isAdmin()) {
            return true;
        }

        if (! $user->belongsToTeam($team)) {
            throw ApiException::make('You are not a member of this team.', 403);
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'string', Rule::in(TaskStatus::values())],
            'priority' => ['sometimes', 'string', Rule::in(TaskPriority::values())],
            'assigned_to' => ['sometimes', 'integer', 'exists:users,id'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function perPage(): int
    {
        return (int) $this->input('per_page', 15);
    }
}
