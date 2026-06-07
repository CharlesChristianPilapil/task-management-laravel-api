<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponse;
use App\Models\Task;
use App\Services\SchedulerService;
use Illuminate\Http\JsonResponse;

class SchedulerController extends Controller
{
    public function __construct(
        private SchedulerService $schedulerService,
    ) {}

    public function dailyDigest(): JsonResponse
    {
        return ApiResponse::success(
            $this->schedulerService->getDailyDigest(),
            'Daily digest data retrieved successfully.',
        );
    }

    public function deadlineReminders(): JsonResponse
    {
        return ApiResponse::success(
            $this->schedulerService->getDeadlineReminders(),
            'Deadline reminder data retrieved successfully.',
        );
    }

    public function staleCancelledTasks(): JsonResponse
    {
        return ApiResponse::success(
            $this->schedulerService->getStaleCancelledTasks(),
            'Stale cancelled tasks retrieved successfully.',
        );
    }

    public function archive(Task $task): JsonResponse
    {
        $this->schedulerService->archiveTask($task);

        return ApiResponse::success(null, 'Task archived successfully.');
    }
}
