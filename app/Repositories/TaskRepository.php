<?php

namespace App\Repositories;

use App\Contracts\Repositories\TaskRepositoryInterface;
use App\DataTransferObjects\TaskListFilters;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TaskRepository implements TaskRepositoryInterface
{
    public function create(array $attributes): Task
    {
        return Task::create($attributes);
    }

    public function update(Task $task, array $attributes): Task
    {
        $task->update($attributes);

        return $this->findWithRelations($task);
    }

    public function delete(Task $task): void
    {
        $task->delete();
    }

    public function paginateForTeam(Team $team, User $actor, TaskListFilters $filters, int $perPage): LengthAwarePaginator
    {
        $query = Task::query()
            ->where('team_id', $team->id)
            ->with(['assignee', 'creator'])
            ->orderByDesc('created_at');

        if ($actor->isTeamMember()) {
            $query->where('assigned_to', $actor->id);
        }

        if ($filters->status !== null) {
            $query->where('status', $filters->status);
        }

        if ($filters->priority !== null) {
            $query->where('priority', $filters->priority);
        }

        if ($filters->assignedTo !== null) {
            $query->where('assigned_to', $filters->assignedTo);
        }

        return $query->paginate($perPage);
    }

    public function findWithRelations(Task $task): Task
    {
        return $task->fresh(['assignee', 'creator', 'team']);
    }
}
