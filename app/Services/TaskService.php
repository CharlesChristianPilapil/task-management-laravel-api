<?php

namespace App\Services;

use App\Contracts\Repositories\TaskRepositoryInterface;
use App\DataTransferObjects\TaskListFilters;
use App\Enums\TaskStatus;
use App\Exceptions\ApiException;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TaskService
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository,
        private NotificationService $notificationService,
    ) {}

    public function listTeamTasks(User $actor, Team $team, TaskListFilters $filters, int $perPage): LengthAwarePaginator
    {
        $this->assertCanAccessTeam($actor, $team);

        return $this->taskRepository->paginateForTeam($team, $actor, $filters, $perPage);
    }

    public function createTask(User $actor, Team $team, array $data): Task
    {
        $this->assertCanAccessTeam($actor, $team);

        if (isset($data['assigned_to'])) {
            $this->assertAssigneeBelongsToTeam($team, (int) $data['assigned_to']);
        }

        $task = $this->taskRepository->create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'status' => TaskStatus::Pending->value,
            'priority' => $data['priority'],
            'assigned_to' => $data['assigned_to'] ?? null,
            'due_date' => $data['due_date'] ?? null,
            'created_by' => $actor->id,
            'team_id' => $team->id,
        ]);

        $task = $this->taskRepository->findWithRelations($task);

        if ($task->assigned_to !== null) {
            $this->notificationService->queueAssigned($task->id, $task->assigned_to);
        }

        return $task;
    }

    public function getTask(User $actor, Task $task): Task
    {
        $this->assertCanViewTask($actor, $task);

        return $this->taskRepository->findWithRelations($task);
    }

    public function updateTask(User $actor, Task $task, array $data): Task
    {
        $this->assertCanEditTask($actor, $task);

        if (isset($data['assigned_to'])) {
            $this->assertAssigneeBelongsToTeam($task->team, (int) $data['assigned_to']);
        }

        $previousAssigneeId = $task->assigned_to;
        $updatedTask = $this->taskRepository->update($task, $data);

        if (
            isset($data['assigned_to'])
            && $updatedTask->assigned_to !== null
            && $updatedTask->assigned_to !== $previousAssigneeId
        ) {
            $this->notificationService->queueAssigned(
                $updatedTask->id,
                $updatedTask->assigned_to,
            );
        }

        return $updatedTask;
    }

    public function updateTaskStatus(User $actor, Task $task, string $status): Task
    {
        $this->assertCanEditTask($actor, $task);

        $newStatus = TaskStatus::from($status);

        if (! $task->status->canTransitionTo($newStatus)) {
            throw ApiException::make(
                sprintf(
                    'Cannot change task status from %s to %s.',
                    $task->status->label(),
                    $newStatus->label(),
                ),
                422,
            );
        }

        $previousStatusLabel = $task->status->label();
        $updatedTask = $this->taskRepository->update($task, ['status' => $newStatus->value]);

        if ($updatedTask->assigned_to !== null) {
            $this->notificationService->queueStatusChanged(
                $updatedTask->id,
                $updatedTask->assigned_to,
                [
                    'previous_status_label' => $previousStatusLabel,
                ],
            );
        }

        return $updatedTask;
    }

    public function deleteTask(User $actor, Task $task): void
    {
        $this->assertCanDeleteTask($actor, $task);

        $this->taskRepository->delete($task);
    }

    private function assertCanAccessTeam(User $actor, Team $team): void
    {
        if (! $actor->belongsToTeam($team)) {
            $message = 'You are not a member of this team.';
            throw ApiException::make($message, 403);
        }
    }

    private function assertCanViewTask(User $actor, Task $task): void
    {
        $this->assertCanAccessTeam($actor, $task->team);

        if ($actor->isTeamMember() && $task->assigned_to !== $actor->id) {
            throw ApiException::make('You can only access tasks assigned to you.', 403);
        }
    }

    private function assertCanEditTask(User $actor, Task $task): void
    {
        $this->assertCanViewTask($actor, $task);
    }

    private function assertCanDeleteTask(User $actor, Task $task): void
    {
        if (! $actor->isAdmin() && $task->created_by !== $actor->id) {
            $message = 'Only the task creator or an admin can delete this task.';
            throw ApiException::make($message, 403);
        }
    }

    private function assertAssigneeBelongsToTeam(Team $team, int $userId): void
    {
        $isMember = $team->members()->where('users.id', $userId)->exists();

        if (! $isMember) {
            $message = 'The selected assignee is not a member of this team.';
            throw ApiException::make($message, 422);
        }
    }
}
