<?php

namespace App\Http\Requests;

use App\Exceptions\ApiException;
use Illuminate\Foundation\Http\FormRequest;

abstract class ApiFormRequest extends FormRequest
{
    protected function failedAuthorization(): void
    {
        throw ApiException::make($this->authorizationMessage(), 403);
    }

    protected function authorizationMessage(): string
    {
        return 'You do not have permission to perform this action.';
    }
}
