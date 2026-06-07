<?php

namespace App\Services;

use App\Contracts\Repositories\TaskRepositoryInterface;
use App\Enums\TaskStatus;
use App\Http\Resources\TaskResource;
use App\Http\Resources\UserResource;
use App\Models\Task;
use Illuminate\Support\Collection;

class SchedulerService
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository,
    ) {}

    public function getDailyDigest(): array
    {
        $tasks = $this->taskRepository->getIncompleteAssignedTasks();

        return [
            'users' => $this->formatGroupedTasks($this->taskRepository->groupByAssignee($tasks)),
        ];
    }

    public function getDeadlineReminders(int $hours = 24): array
    {
        $tasks = $this->taskRepository->getTasksDueWithinHours($hours);

        return [
            'reminders' => $this->formatGroupedTasks($this->taskRepository->groupByAssignee($tasks)),
        ];
    }

    public function getStaleCancelledTasks(int $olderThanDays = 30): array
    {
        return [
            'tasks' => $this->taskRepository->getStaleCancelledTaskSummaries($olderThanDays),
        ];
    }

    public function archiveTask(Task $task): void
    {
        if ($task->status !== TaskStatus::Cancelled) {
            return;
        }

        $this->taskRepository->archive($task);
    }

    private function formatGroupedTasks(Collection $grouped): array
    {
        return $grouped
            ->map(fn (array $group) => [
                'user' => (new UserResource($group['user']))->resolve(),
                'tasks' => TaskResource::collection($group['tasks'])->resolve(),
            ])->all();
    }
}
