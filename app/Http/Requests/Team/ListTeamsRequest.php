<?php

namespace App\Http\Requests\Team;

use App\Http\Requests\ApiFormRequest;

class ListTeamsRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManageUsers() ?? false;
    }

    protected function authorizationMessage(): string
    {
        return 'Only admins and managers can list teams.';
    }

    public function rules(): array
    {
        return [
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function perPage(): int
    {
        return (int) $this->input('per_page', 15);
    }
}
