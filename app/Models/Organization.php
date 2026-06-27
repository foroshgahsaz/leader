<?php

namespace App\Models;

use App\Enums\OrganizationStatus;
use App\Models\Concerns\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Organization extends Model
{
    use HasFactory;
    use HasUuidPrimaryKey;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'country_code',
        'website',
        'industry',
        'logo_path',
        'timezone',
        'status',
        'onboarding_completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrganizationStatus::class,
            'onboarding_completed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Organization $organization): void {
            if (empty($organization->slug)) {
                $organization->slug = static::generateUniqueSlug($organization->name);
            }
        });
    }

    public static function generateUniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $counter = 1;

        while (static::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    public function members(): HasMany
    {
        return $this->hasMany(OrgMember::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'org_members')
            ->withPivot(['id', 'status', 'invited_at', 'invited_by', 'joined_at'])
            ->withTimestamps();
    }

    public function settings(): HasMany
    {
        return $this->hasMany(OrgSetting::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function getSetting(string $key, mixed $default = null): mixed
    {
        $setting = $this->settings()->where('key', $key)->first();

        return $setting?->value ?? $default;
    }

    public function setSetting(string $key, mixed $value): OrgSetting
    {
        return $this->settings()->updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
