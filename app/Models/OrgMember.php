<?php

namespace App\Models;

use App\Enums\OrgMemberStatus;
use App\Models\Concerns\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrgMember extends Model
{
    use HasFactory;
    use HasUuidPrimaryKey;

    protected $fillable = [
        'organization_id',
        'user_id',
        'status',
        'invited_at',
        'invited_by',
        'joined_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrgMemberStatus::class,
            'invited_at' => 'datetime',
            'joined_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function isActive(): bool
    {
        return $this->status === OrgMemberStatus::Active;
    }
}
