<?php

namespace App\Http\Requests\Task;

use App\Http\Requests\ApiFormRequest;
use App\Enums\TaskPriority;
use App\Exceptions\ApiException;
use App\Models\Team;
use App\Models\User;
use Illuminate\Validation\Rule;

class StoreTeamTaskRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        $team = $this->route('team');
        $user = $this->user();

        if (! $team instanceof Team || ! $user instanceof User) {
            throw ApiException::make('Team context is required to create a task.', 403);
        }

        if (! $user->canManageUsers()) {
            throw ApiException::make('Only admins and managers can create tasks.', 403);
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
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['required', 'string', Rule::in(TaskPriority::values())],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'due_date' => ['nullable', 'date'],
        ];
    }
}
