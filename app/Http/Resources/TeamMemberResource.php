<?php

namespace App\Http\Resources;

use App\Enums\TeamMemberRole;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamMemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $role = TeamMemberRole::from($this->pivot->role);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $role->value,
            'role_label' => $role->label(),
        ];
    }
}
