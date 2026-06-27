<?php

namespace App\Models;

use App\Enums\AuditAction;
use App\Models\Concerns\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasUuidPrimaryKey;

    public $timestamps = false;

    protected $fillable = [
        'organization_id',
        'actor_id',
        'actor_email',
        'action',
        'resource_type',
        'resource_id',
        'ip_address',
        'user_agent',
        'request_id',
        'metadata',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'action' => AuditAction::class,
            'metadata' => 'array',
            'occurred_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
