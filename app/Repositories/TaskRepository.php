<?php

namespace App\Repositories;

use App\Contracts\Repositories\TaskRepositoryInterface;
use App\DataTransferObjects\TaskListFilters;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

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
        $task->forceDelete();
    }

    public function archive(Task $task): void
    {
        $task->delete();
    }

    public function getIncompleteAssignedTasks(): Collection
    {
        $statuses = [TaskStatus::Pending->value, TaskStatus::InProgress->value];
        
        return Task::query()
            ->whereIn('status', $statuses)
            ->whereNotNull('assigned_to')
            ->with(['assignee', 'team'])
            ->orderBy('due_date')
            ->get();
    }

    public function getTasksDueWithinHours(int $hours): Collection
    {
        $now = now();
        $windowEnd = $now->copy()->addHours($hours);

        return Task::query()
            ->whereNotNull('assigned_to')
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$now, $windowEnd])
            ->whereNotIn('status', [TaskStatus::Completed->value, TaskStatus::Cancelled->value])
            ->with(['assignee', 'team'])
            ->orderBy('due_date')
            ->get();
    }

    public function getStaleCancelledTasks(int $olderThanDays): Collection
    {
        $cutoff = now()->subDays($olderThanDays);

        return Task::query()
            ->where('status', TaskStatus::Cancelled->value)
            ->where('updated_at', '<=', $cutoff)
            ->orderBy('updated_at')
            ->get();
    }

    public function groupByAssignee(Collection $tasks): Collection
    {
        return $tasks
            ->groupBy('assigned_to')
            ->map(function (Collection $userTasks) {
                $assignee = $userTasks->first()?->assignee;

                if ($assignee === null) {
                    return null;
                }

                return [
                    'user' => $assignee,
                    'tasks' => $userTasks->values(),
                ];
            })
            ->filter()
            ->values();
    }

    public function getStaleCancelledTaskSummaries(int $olderThanDays): array
    {
        return $this->getStaleCancelledTasks($olderThanDays)
            ->map(fn (Task $task) => [
                'id' => $task->id,
                'title' => $task->title,
                'status' => $task->status->value,
                'team_id' => $task->team_id,
                'updated_at' => $task->updated_at?->toISOString(),
            ])
            ->values()
            ->all();
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
