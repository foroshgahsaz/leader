<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use App\Models\Concerns\HasUuidPrimaryKey;
use Database\Factories\SavedSearchFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SavedSearch extends Model
{
    /** @use HasFactory<SavedSearchFactory> */
    use HasFactory;

    use BelongsToOrganization;
    use HasUuidPrimaryKey;
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'name',
        'criteria',
        'result_count_last',
        'last_run_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'criteria' => 'array',
            'result_count_last' => 'integer',
            'last_run_at' => 'datetime',
        ];
    }

    protected static function newFactory(): SavedSearchFactory
    {
        return SavedSearchFactory::new();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function executions(): HasMany
    {
        return $this->hasMany(SearchExecution::class);
    }

    public function sourcedBuyers(): HasMany
    {
        return $this->hasMany(Buyer::class, 'source_search_id');
    }
}
