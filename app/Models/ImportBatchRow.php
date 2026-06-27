<?php

namespace App\Models;

use App\Enums\ImportBatchStatus;
use App\Models\Concerns\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportBatchRow extends Model
{
    use HasUuidPrimaryKey;

    protected $fillable = [
        'import_batch_id',
        'row_number',
        'payload',
        'status',
        'buyer_id',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'row_number' => 'integer',
            'payload' => 'array',
            'status' => ImportBatchStatus::class,
        ];
    }

    public function importBatch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Buyer::class);
    }
}
