<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use App\Models\Concerns\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuyerSummary extends Model
{
    use BelongsToOrganization;
    use HasUuidPrimaryKey;

    protected $fillable = [
        'organization_id',
        'buyer_id',
        'global_buyer_id',
        'summary',
        'key_facts',
        'suggested_angle',
        'confidence',
        'data_gaps',
        'model_version',
        'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'key_facts' => 'array',
            'data_gaps' => 'array',
            'generated_at' => 'datetime',
        ];
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Buyer::class);
    }

    public function globalBuyer(): BelongsTo
    {
        return $this->belongsTo(GlobalBuyer::class);
    }
}
