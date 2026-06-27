<?php

namespace App\Events\Crm;

use App\Models\Deal;
use App\Models\PipelineStage;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DealStageChanged
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Deal $deal,
        public ?PipelineStage $fromStage,
        public PipelineStage $toStage,
        public User $actor,
        public ?string $reason = null,
    ) {}
}
