<?php

namespace App\Http\Requests\Team;

use App\Http\Requests\ApiFormRequest;

class StoreTeamRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManageUsers() ?? false;
    }

    protected function authorizationMessage(): string
    {
        return 'Only admins and managers can create teams.';
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:teams,name'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'A team with this name already exists.',
        ];
    }
}
