<?php

namespace App\Http\Requests\User;

use App\Http\Requests\ApiFormRequest;
use App\Enums\UserRole;
use Illuminate\Validation\Rule;

class StoreUserRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManageUsers() ?? false;
    }

    protected function authorizationMessage(): string
    {
        return 'Only admins and managers can create users.';
    }

    public function rules(): array
    {
        $allowedRoles = $this->user()?->isAdmin() ? UserRole::values() : [UserRole::TeamMember->value];

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['sometimes', 'string', Rule::in($allowedRoles)],
        ];
    }

    public function messages(): array
    {
        return [
            'role.in' => 'Not permitted to create user with this role.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->user()?->isManager() && ! $this->has('role')) {
            $this->merge(['role' => UserRole::TeamMember->value]);
        }
    }
}
