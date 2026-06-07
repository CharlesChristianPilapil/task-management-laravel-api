<?php

namespace App\Http\Requests\Task;

use App\Enums\TaskPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'priority' => ['sometimes', 'string', Rule::in(TaskPriority::values())],
            'assigned_to' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
            'due_date' => ['sometimes', 'nullable', 'date'],
        ];
    }
}
