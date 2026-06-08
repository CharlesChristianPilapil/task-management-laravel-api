<?php

namespace App\Http\Requests\User;

use App\Http\Requests\ApiFormRequest;
use App\Enums\UserRole;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManageUsers() ?? false;
    }

    protected function authorizationMessage(): string
    {
        return 'Only admins and managers can update users.';
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        $allowedRoles = $this->user()?->isAdmin() ? UserRole::values() : [UserRole::TeamMember->value];

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'role' => ['sometimes', 'string', Rule::in($allowedRoles)],
        ];
    }

    public function messages(): array
    {
        return [
            'role.in' => 'Not permitted to assign this role.',
        ];
    }
}
