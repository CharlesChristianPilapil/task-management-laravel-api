<?php

namespace App\Http\Controllers;

use App\Http\Requests\Team\AddTeamMemberRequest;
use App\Http\Requests\Team\ListTeamsRequest;
use App\Http\Requests\Team\RemoveTeamMemberRequest;
use App\Http\Requests\Team\ShowTeamRequest;
use App\Http\Requests\Team\StoreTeamRequest;
use App\Http\Resources\TeamResource;
use App\Http\Responses\ApiResponse;
use App\Models\Team;
use App\Models\User;
use App\Services\TeamService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function __construct(
        private TeamService $teamService,
    ) {}

    public function index(ListTeamsRequest $request): JsonResponse
    {
        $teams = $this->teamService->listTeams(
            $this->authenticatedUser($request),
            $request->perPage(),
        );

        return ApiResponse::paginated($teams, TeamResource::class, 'teams', 'Teams retrieved successfully.');
    }

    public function store(StoreTeamRequest $request): JsonResponse
    {
        $team = $this->teamService->createTeam(
            $this->authenticatedUser($request),
            $request->validated(),
        );

        return ApiResponse::success(new TeamResource($team), 'Team created successfully.', 201);
    }

    public function show(ShowTeamRequest $request, Team $team): JsonResponse
    {
        $team = $this->teamService->getTeam(
            $this->authenticatedUser($request),
            $team,
        );

        return ApiResponse::success(new TeamResource($team), 'Team retrieved successfully.');
    }

    public function addMember(AddTeamMemberRequest $request, Team $team): JsonResponse
    {
        $team = $this->teamService->addMember(
            $this->authenticatedUser($request),
            $team,
            $request->validated(),
        );

        return ApiResponse::success(new TeamResource($team), 'Team member added successfully.');
    }

    public function removeMember(RemoveTeamMemberRequest $request, Team $team, User $user): JsonResponse
    {
        $team = $this->teamService->removeMember(
            $this->authenticatedUser($request),
            $team,
            $user,
        );

        return ApiResponse::success(new TeamResource($team), 'Team member removed successfully.');
    }

    private function authenticatedUser(Request $request): User
    {
        $user = $request->user();
        assert($user instanceof User);

        return $user;
    }
}
