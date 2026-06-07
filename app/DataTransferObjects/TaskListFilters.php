<?php

namespace App\DataTransferObjects;

readonly class TaskListFilters
{
    public function __construct(
        public ?string $status = null,
        public ?string $priority = null,
        public ?int $assignedTo = null,
    ) {}
}
