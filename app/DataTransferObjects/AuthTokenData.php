<?php

namespace App\DataTransferObjects;

use App\Models\User;

readonly class AuthTokenData
{
    public function __construct(
        public string $accessToken,
        public User $user,
        public int $expiresIn,
    ) {}
}
