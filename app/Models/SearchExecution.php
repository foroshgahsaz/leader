<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use App\Models\Concerns\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SearchExecution extends Model
{
    use BelongsToOrganization;
    use HasUuidPrimaryKey;

    protected $fillable = [
        'organization_id',
        'user_id',
        'saved_search_id',
        'criteria',
        'result_count',
        'duration_ms',
        'credit_consumed',
        'executed_at',
    ];

    protected function casts(): array
    {
        return [
            'criteria' => 'array',
            'result_count' => 'integer',
            'duration_ms' => 'integer',
            'credit_consumed' => 'integer',
            'executed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function savedSearch(): BelongsTo
    {
        return $this->belongsTo(SavedSearch::class);
    }
}
