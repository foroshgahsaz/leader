<?php

namespace App\Models;

use App\Enums\CrmActivityType;
use App\Models\Concerns\BelongsToOrganization;
use App\Models\Concerns\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmActivity extends Model
{
    use BelongsToOrganization;
    use HasUuidPrimaryKey;
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'buyer_id',
        'deal_id',
        'contact_id',
        'logged_by',
        'activity_type',
        'subject',
        'body',
        'duration_minutes',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'activity_type' => CrmActivityType::class,
            'occurred_at' => 'datetime',
        ];
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Buyer::class);
    }

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(BuyerContact::class, 'contact_id');
    }

    public function logger(): BelongsTo
    {
        return $this->belongsTo(User::class, 'logged_by');
    }
}
