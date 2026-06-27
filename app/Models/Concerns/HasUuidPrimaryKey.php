<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

trait HasUuidPrimaryKey
{
    use HasUuids;

    public function uniqueIds(): array
    {
        return ['id'];
    }

    public function getIncrementing(): bool
    {
        return false;
    }

    public function getKeyType(): string
    {
        return 'string';
    }

    protected function initializeHasUuidPrimaryKey(): void
    {
        $this->mergeCasts([
            'id' => 'string',
        ]);
    }
}
