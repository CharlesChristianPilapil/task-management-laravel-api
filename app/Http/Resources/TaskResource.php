<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'priority' => $this->priority->value,
            'priority_label' => $this->priority->label(),
            'due_date' => $this->due_date?->toISOString(),
            'team_id' => $this->team_id,
            'assigned_to' => $this->assigned_to,
            'created_by' => $this->created_by,
            'assignee' => $this->whenLoaded('assignee', fn () => new UserResource($this->assignee)),
            'creator' => $this->whenLoaded('creator', fn () => new UserResource($this->creator)),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
