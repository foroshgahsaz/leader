<?php

namespace App\Models;

use App\Models\Concerns\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPreference extends Model
{
    use HasUuidPrimaryKey;

    protected $fillable = [
        'user_id',
        'organization_id',
        'preferences',
    ];

    protected function casts(): array
    {
        return [
            'preferences' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public static function defaults(): array
    {
        return [
            'theme' => 'system',
            'density' => 'comfortable',
            'email_notifications' => true,
            'task_reminders' => true,
            'marketing_emails' => false,
            'start_page' => 'dashboard',
        ];
    }

    public function getPreference(string $key, mixed $default = null): mixed
    {
        return data_get($this->preferences ?? [], $key, $default ?? data_get(static::defaults(), $key));
    }
}
