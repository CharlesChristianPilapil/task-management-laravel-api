<?php

namespace App\Contracts\Repositories;

use App\DataTransferObjects\TaskListFilters;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TaskRepositoryInterface
{
    public function create(array $attributes): Task;

    public function update(Task $task, array $attributes): Task;

    public function delete(Task $task): void;

    public function paginateForTeam(Team $team, User $actor, TaskListFilters $filters, int $perPage): LengthAwarePaginator;

    public function findWithRelations(Task $task): Task;
}
