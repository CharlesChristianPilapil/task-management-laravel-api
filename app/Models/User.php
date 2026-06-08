<?php

namespace App\Models;

use App\Enums\TeamMemberRole;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [
            'role' => $this->role->value,
            'is_active' => $this->is_active,
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isManager(): bool
    {
        return $this->role === UserRole::Manager;
    }

    public function isTeamMember(): bool
    {
        return $this->role === UserRole::TeamMember;
    }

    public function canManageUsers(): bool
    {
        return $this->isAdmin() || $this->isManager();
    }

    public function teams(): BelongsToMany
    {
        $teams = $this->belongsToMany(Team::class, 'team_members');

        return $teams->withPivot('role')->withTimestamps();
    }

    public function assignedTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    public function createdTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    public function belongsToTeam(Team $team): bool
    {
        return $this->teams()->where('teams.id', $team->id)->exists();
    }

    public function teamMemberRole(Team $team): ?TeamMemberRole
    {
        $membership = $this->teams()->where('teams.id', $team->id)->first();

        if ($membership === null) {
            return null;
        }

        return TeamMemberRole::from($membership->pivot->role);
    }

    public function isTeamLead(Team $team): bool
    {
        return $this->teamMemberRole($team) === TeamMemberRole::Lead;
    }

    public function canManageTeamMembers(Team $team): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->isManager() && $this->belongsToTeam($team);
    }
}
