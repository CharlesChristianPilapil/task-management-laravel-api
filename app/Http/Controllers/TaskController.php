<?php

namespace App\Http\Controllers;

use App\DataTransferObjects\TaskListFilters;
use App\Http\Requests\Task\ListTeamTasksRequest;
use App\Http\Requests\Task\StoreTeamTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Requests\Task\UpdateTaskStatusRequest;
use App\Http\Resources\TaskResource;
use App\Http\Responses\ApiResponse;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(
        private TaskService $taskService,
    ) {}

    public function index(ListTeamTasksRequest $request, Team $team): JsonResponse
    {
        $filters = new TaskListFilters(
            status: $request->validated('status'),
            priority: $request->validated('priority'),
            assignedTo: $request->validated('assigned_to'),
        );

        $tasks = $this->taskService->listTeamTasks(
            $this->authenticatedUser($request),
            $team,
            $filters,
            $request->perPage(),
        );

        return ApiResponse::paginated($tasks, TaskResource::class, 'tasks', 'Tasks retrieved successfully.');
    }

    public function store(StoreTeamTaskRequest $request, Team $team): JsonResponse
    {
        $task = $this->taskService->createTask(
            $this->authenticatedUser($request),
            $team,
            $request->validated(),
        );

        return ApiResponse::success(new TaskResource($task), 'Task created successfully.', 201);
    }

    public function show(Request $request, Task $task): JsonResponse
    {
        $task = $this->taskService->getTask($this->authenticatedUser($request), $task);

        return ApiResponse::success(new TaskResource($task), 'Task retrieved successfully.');
    }

    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $updatedTask = $this->taskService->updateTask(
            $this->authenticatedUser($request),
            $task,
            $request->validated(),
        );

        return ApiResponse::success(new TaskResource($updatedTask), 'Task updated successfully.');
    }

    public function updateStatus(UpdateTaskStatusRequest $request, Task $task): JsonResponse
    {
        $updatedTask = $this->taskService->updateTaskStatus(
            $this->authenticatedUser($request),
            $task,
            $request->validated('status'),
        );

        return ApiResponse::success(new TaskResource($updatedTask), 'Task status updated successfully.');
    }

    public function destroy(Request $request, Task $task): JsonResponse
    {
        $this->taskService->deleteTask($this->authenticatedUser($request), $task);

        return ApiResponse::success(null, 'Task deleted successfully.');
    }

    private function authenticatedUser(Request $request): User
    {
        $user = $request->user();
        assert($user instanceof User);

        return $user;
    }
}
