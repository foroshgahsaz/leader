<?php

namespace App\Models;

use App\Enums\BuyerStatus;
use App\Enums\CompanyType;
use App\Enums\LeadSource;
use App\Models\Concerns\BelongsToOrganization;
use App\Models\Concerns\HasUuidPrimaryKey;
use Database\Factories\BuyerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Buyer extends Model
{
    /** @use HasFactory<BuyerFactory> */
    use HasFactory;

    use BelongsToOrganization;
    use HasUuidPrimaryKey;
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'global_buyer_id',
        'provider_key',
        'name',
        'country_code',
        'city',
        'website',
        'phone',
        'industry',
        'description',
        'company_type',
        'employee_range',
        'owner_id',
        'source',
        'source_search_id',
        'status',
        'pipeline_stage',
        'is_favorite',
        'is_dnc',
        'last_contacted_at',
        'last_activity_at',
        'snapshot',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'company_type' => CompanyType::class,
            'source' => LeadSource::class,
            'status' => BuyerStatus::class,
            'is_favorite' => 'boolean',
            'is_dnc' => 'boolean',
            'last_contacted_at' => 'datetime',
            'last_activity_at' => 'datetime',
            'snapshot' => 'array',
        ];
    }

    protected static function newFactory(): BuyerFactory
    {
        return BuyerFactory::new();
    }

    public function globalBuyer(): BelongsTo
    {
        return $this->belongsTo(GlobalBuyer::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sourceSearch(): BelongsTo
    {
        return $this->belongsTo(SavedSearch::class, 'source_search_id');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(BuyerContact::class);
    }

    public function scores(): HasMany
    {
        return $this->hasMany(BuyerScore::class);
    }

    public function currentScore(): HasOne
    {
        return $this->hasOne(BuyerScore::class)->where('is_current', true);
    }

    public function summaries(): HasMany
    {
        return $this->hasMany(BuyerSummary::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(BuyerNote::class);
    }

    public function tags(): HasMany
    {
        return $this->hasMany(BuyerTag::class);
    }

    public function listItems(): HasMany
    {
        return $this->hasMany(LeadListItem::class);
    }

    public function lists(): BelongsToMany
    {
        return $this->belongsToMany(LeadList::class, 'lead_list_items')
            ->withPivot(['id', 'organization_id', 'added_by'])
            ->withTimestamps();
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function aiGenerations(): HasMany
    {
        return $this->hasMany(AiGeneration::class);
    }

    public function deal(): HasOne
    {
        return $this->hasOne(Deal::class);
    }

    public function crmActivities(): HasMany
    {
        return $this->hasMany(CrmActivity::class);
    }

    public function crmMeetings(): HasMany
    {
        return $this->hasMany(CrmMeeting::class);
    }

    public function crmFiles(): HasMany
    {
        return $this->hasMany(CrmFile::class);
    }
}
