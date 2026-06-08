<?php

namespace App\Http\Requests\Task;

use App\Http\Requests\ApiFormRequest;
use App\Enums\TaskStatus;
use Illuminate\Validation\Rule;

class UpdateTaskStatusRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(TaskStatus::values())],
        ];
    }
}
