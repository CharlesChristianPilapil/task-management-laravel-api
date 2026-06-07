<?php

namespace App\Models;

use App\Enums\TeamMemberRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    protected $fillable = [
        'name',
        'created_by',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members(): BelongsToMany
    {
        $teamMembers = $this->belongsToMany(User::class, 'team_members');

        return $teamMembers->withPivot('role')->withTimestamps();
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function hasMember(User $user): bool
    {
        return $this->members()->where('users.id', $user->id)->exists();
    }

    public function memberRole(User $user): ?TeamMemberRole
    {
        $member = $this->members()->where('users.id', $user->id)->first();

        if ($member === null) {
            return null;
        }

        return TeamMemberRole::from($member->pivot->role);
    }
}
