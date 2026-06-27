<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use App\Models\Concerns\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Deal extends Model
{
    use BelongsToOrganization;
    use HasUuidPrimaryKey;
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'buyer_id',
        'stage_id',
        'owner_id',
        'title',
        'estimated_value',
        'currency_code',
        'expected_close_date',
        'closed_at',
        'lost_reason',
        'lost_notes',
        'last_stage_change_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'estimated_value' => 'decimal:2',
            'expected_close_date' => 'date',
            'closed_at' => 'datetime',
            'last_stage_change_at' => 'datetime',
        ];
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Buyer::class);
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class, 'stage_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function stageHistories(): HasMany
    {
        return $this->hasMany(DealStageHistory::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(BuyerNote::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(CrmActivity::class);
    }

    public function meetings(): HasMany
    {
        return $this->hasMany(CrmMeeting::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(CrmFile::class);
    }
}
