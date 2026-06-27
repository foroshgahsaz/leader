<?php

namespace App\Models;

use App\Enums\ImportBatchStatus;
use App\Models\Concerns\BelongsToOrganization;
use App\Models\Concerns\HasUuidPrimaryKey;
use Database\Factories\ImportBatchFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportBatch extends Model
{
    /** @use HasFactory<ImportBatchFactory> */
    use HasFactory;

    use BelongsToOrganization;
    use HasUuidPrimaryKey;

    protected $fillable = [
        'organization_id',
        'user_id',
        'filename',
        'status',
        'total_rows',
        'processed_rows',
        'success_rows',
        'failed_rows',
        'errors',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ImportBatchStatus::class,
            'total_rows' => 'integer',
            'processed_rows' => 'integer',
            'success_rows' => 'integer',
            'failed_rows' => 'integer',
            'errors' => 'array',
            'completed_at' => 'datetime',
        ];
    }

    protected static function newFactory(): ImportBatchFactory
    {
        return ImportBatchFactory::new();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rows(): HasMany
    {
        return $this->hasMany(ImportBatchRow::class);
    }
}
