<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use App\Models\Concerns\HasUuidPrimaryKey;
use Database\Factories\LeadListFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadList extends Model
{
    /** @use HasFactory<LeadListFactory> */
    use HasFactory;

    use BelongsToOrganization;
    use HasUuidPrimaryKey;
    use SoftDeletes;

    protected $table = 'lead_lists';

    protected $fillable = [
        'organization_id',
        'name',
        'description',
        'color',
        'is_shared',
        'owner_id',
        'buyer_count',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_shared' => 'boolean',
            'buyer_count' => 'integer',
        ];
    }

    protected static function newFactory(): LeadListFactory
    {
        return LeadListFactory::new();
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(LeadListItem::class);
    }

    public function buyers(): BelongsToMany
    {
        return $this->belongsToMany(Buyer::class, 'lead_list_items')
            ->withPivot(['id', 'organization_id', 'added_by'])
            ->withTimestamps();
    }
}
