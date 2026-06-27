<?php

namespace App\Models;

use App\Enums\ScoreBand;
use App\Models\Concerns\BelongsToOrganization;
use App\Models\Concerns\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuyerScore extends Model
{
    use BelongsToOrganization;
    use HasUuidPrimaryKey;

    protected $fillable = [
        'organization_id',
        'buyer_id',
        'global_buyer_id',
        'product_id',
        'score',
        'score_band',
        'factors',
        'explanation',
        'model_version',
        'is_current',
        'scored_at',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
            'score_band' => ScoreBand::class,
            'factors' => 'array',
            'is_current' => 'boolean',
            'scored_at' => 'datetime',
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

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
