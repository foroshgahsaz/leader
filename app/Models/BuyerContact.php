<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use App\Models\Concerns\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BuyerContact extends Model
{
    use BelongsToOrganization;
    use HasUuidPrimaryKey;
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'buyer_id',
        'full_name',
        'title',
        'email',
        'phone',
        'is_primary',
        'is_verified',
        'source',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'is_verified' => 'boolean',
        ];
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Buyer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
