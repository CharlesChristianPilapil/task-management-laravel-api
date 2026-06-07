<?php

namespace App\DataTransferObjects;

readonly class UserListFilters
{
    public function __construct(
        public ?string $role = null,
        public ?string $status = null,
    ) {}

    public function isActiveFilter(): ?bool
    {
        if ($this->status === null) {
            return null;
        }

        return $this->status === 'active';
    }
}
