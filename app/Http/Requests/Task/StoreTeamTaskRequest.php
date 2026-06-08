<?php

namespace App\Http\Requests\Task;

use App\Enums\TaskPriority;
use App\Models\Team;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTeamTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        $team = $this->route('team');
        $user = $this->user();

        if (! $team instanceof Team || $user === null) {
            return false;
        }

        if ($user->canManageUsers()) {
            return true;
        }

        return $user->belongsToTeam($team);
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
