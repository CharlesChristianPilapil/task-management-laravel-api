<?php

namespace App\Exceptions;

use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class ApiException extends RuntimeException
{
    public function __construct(
        string $message,
        protected int $statusCode = 400,
        protected mixed $errors = null,
    ) {
        parent::__construct($message);
    }

    public static function make(string $message, int $statusCode = 400, mixed $errors = null): self
    {
        return new self($message, $statusCode, $errors);
    }

    public function render(): JsonResponse
    {
        return ApiResponse::error($this->message, $this->statusCode, $this->errors);
    }
}
