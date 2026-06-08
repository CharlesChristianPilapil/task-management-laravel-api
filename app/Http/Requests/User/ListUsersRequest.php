<?php

namespace App\Http\Requests\User;

use App\Http\Requests\ApiFormRequest;
use App\Enums\UserRole;
use Illuminate\Validation\Rule;

class ListUsersRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManageUsers() ?? false;
    }

    protected function authorizationMessage(): string
    {
        return 'Only admins and managers can list users.';
    }

    public function rules(): array
    {
        return [
            'role' => ['sometimes', 'string', Rule::in(UserRole::values())],
            'status' => ['sometimes', 'string', Rule::in(['active', 'inactive'])],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function perPage(): int
    {
        return (int) $this->input('per_page', 15);
    }
}
