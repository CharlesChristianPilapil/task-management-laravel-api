<?php

namespace App\Http\Controllers;

use App\Http\Resources\TaskResource;
use App\Http\Resources\UserResource;
use App\Http\Responses\ApiResponse;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class InternalNotificationController extends Controller
{
    public function show(int $task, int $user): JsonResponse
    {
        $roles = ['assignee', 'creator', 'team'];
        $taskModel = Task::query()->with($roles)->findOrFail($task);

        $userModel = User::query()->findOrFail($user);

        return ApiResponse::success([
            'task' => new TaskResource($taskModel),
            'user' => new UserResource($userModel),
        ], 'Notification details retrieved successfully.');
    }
}
