<?php

namespace App\Models;

use App\Enums\CrmMeetingStatus;
use App\Models\Concerns\BelongsToOrganization;
use App\Models\Concerns\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmMeeting extends Model
{
    use BelongsToOrganization;
    use HasUuidPrimaryKey;
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'buyer_id',
        'deal_id',
        'contact_id',
        'organizer_id',
        'title',
        'agenda',
        'outcome',
        'location',
        'meeting_url',
        'status',
        'starts_at',
        'ends_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => CrmMeetingStatus::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Buyer::class);
    }

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(BuyerContact::class, 'contact_id');
    }

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }
}
