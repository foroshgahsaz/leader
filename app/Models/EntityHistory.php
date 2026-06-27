<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use App\Models\Concerns\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class EntityHistory extends Model
{
    use BelongsToOrganization;
    use HasUuidPrimaryKey;

    public $timestamps = false;

    protected $fillable = [
        'organization_id',
        'entity_type',
        'entity_id',
        'field_name',
        'old_value',
        'new_value',
        'changed_by',
        'change_reason',
        'changed_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'changed_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function entity(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'entity_type', 'entity_id');
    }

    public function changedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
