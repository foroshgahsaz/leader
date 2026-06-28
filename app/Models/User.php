<?php

namespace App\Models;

use App\Enums\OrganizationRole;
use App\Enums\OrgMemberStatus;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasRoles;
    use Notifiable;

    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'password',
        'job_title',
        'avatar_path',
        'timezone',
        'locale',
        'last_login_at',
        'current_organization_id',
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
            'last_login_at' => 'datetime',
        ];
    }

    public function currentOrganization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'current_organization_id');
    }

    public function orgMemberships(): HasMany
    {
        return $this->hasMany(OrgMember::class);
    }

    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class, 'org_members')
            ->withPivot(['id', 'status', 'invited_at', 'invited_by', 'joined_at'])
            ->withTimestamps();
    }

    public function preferences(): HasMany
    {
        return $this->hasMany(UserPreference::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'actor_id');
    }

    public function fullName(): string
    {
        if ($this->first_name || $this->last_name) {
            return trim("{$this->first_name} {$this->last_name}");
        }

        return $this->name;
    }

    public function initials(): string
    {
        $parts = preg_split('/\s+/', $this->fullName()) ?: [];

        return strtoupper(collect($parts)->take(2)->map(fn (string $part) => mb_substr($part, 0, 1))->implode(''));
    }

    public function membershipFor(?Organization $organization = null): ?OrgMember
    {
        $organizationId = $organization?->id ?? $this->current_organization_id;

        if (! $organizationId) {
            return null;
        }

        return $this->orgMemberships()
            ->where('organization_id', $organizationId)
            ->first();
    }

    public function organizationRole(?Organization $organization = null): ?OrganizationRole
    {
        $organizationId = $organization?->id ?? $this->current_organization_id;

        if (! $organizationId) {
            return null;
        }

        setPermissionsTeamId($organizationId);

        $role = $this->roles()->first();

        return $role ? OrganizationRole::tryFrom($role->name) : null;
    }

    public function belongsToOrganization(Organization $organization): bool
    {
        return $this->orgMemberships()
            ->where('organization_id', $organization->id)
            ->where('status', OrgMemberStatus::Active)
            ->exists();
    }

    public function syncProfileName(): void
    {
        $this->name = $this->fullName();
    }

    public function preferredLocale(): string
    {
        $locale = $this->locale ?? config('locales.default', config('app.locale'));

        return \App\Support\Locale::isSupported($locale) ? $locale : 'en';
    }
}
