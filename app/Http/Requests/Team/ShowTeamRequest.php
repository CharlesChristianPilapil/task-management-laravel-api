<?php

namespace App\Http\Requests\Team;

use App\Http\Requests\ApiFormRequest;

class ShowTeamRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [];
    }
}
