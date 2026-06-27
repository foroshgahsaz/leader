<?php

namespace App\Actions\Crm;

use App\Enums\ActivityAction;
use App\Models\Buyer;
use App\Models\CrmFile;
use App\Models\User;
use App\Services\Crm\CrmFileStorageService;
use App\Services\Logging\ActivityLogger;

class DeleteCrmFileAction
{
    public function __construct(
        protected CrmFileStorageService $fileStorageService,
        protected ActivityLogger $activityLogger,
    ) {}

    public function execute(string $fileId, User $actor): void
    {
        $file = CrmFile::query()->findOrFail($fileId);
        $buyerId = $file->buyer_id;
        $name = $file->original_name;

        $this->fileStorageService->delete($file);

        $this->activityLogger->log(
            action: ActivityAction::Deleted,
            summary: "{$actor->fullName()} deleted file {$name}",
            entityType: Buyer::class,
            entityId: $buyerId,
            actor: $actor,
            buyerId: $buyerId,
            dealId: $file->deal_id,
        );
    }
}
