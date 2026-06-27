<?php

namespace App\Models;

use App\Enums\ActivityAction;
use App\Models\Concerns\BelongsToOrganization;
use App\Models\Concerns\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use BelongsToOrganization;
    use HasUuidPrimaryKey;

    public $timestamps = false;

    protected $fillable = [
        'organization_id',
        'actor_id',
        'entity_type',
        'entity_id',
        'buyer_id',
        'deal_id',
        'action',
        'summary',
        'metadata',
        'ip_address',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'action' => ActivityAction::class,
            'metadata' => 'array',
            'occurred_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Buyer::class);
    }

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class);
    }
}
