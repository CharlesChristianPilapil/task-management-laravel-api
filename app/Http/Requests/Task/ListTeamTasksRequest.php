<?php

namespace App\Http\Requests\Task;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Team;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListTeamTasksRequest extends FormRequest
{
    public function authorize(): bool
    {
        $team = $this->route('team');

        return $team instanceof Team && ($this->user()?->belongsToTeam($team) ?? false);
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
