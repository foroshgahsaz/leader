<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use App\Models\Concerns\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PipelineStage extends Model
{
    use BelongsToOrganization;
    use HasUuidPrimaryKey;

    protected $fillable = [
        'organization_id',
        'key',
        'label',
        'sort_order',
        'is_closed',
        'is_won',
        'color',
    ];

    protected function casts(): array
    {
        return [
            'is_closed' => 'boolean',
            'is_won' => 'boolean',
        ];
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class, 'stage_id');
    }

    public function localizedLabel(): string
    {
        $key = 'pipeline.'.$this->key;

        return __($key) !== $key ? __($key) : $this->label;
    }
}
