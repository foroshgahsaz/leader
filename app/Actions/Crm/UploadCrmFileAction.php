<?php

namespace App\Actions\Crm;

use App\Enums\ActivityAction;
use App\Models\Buyer;
use App\Models\CrmFile;
use App\Models\User;
use App\Services\Crm\CrmFileStorageService;
use App\Services\Logging\ActivityLogger;
use Illuminate\Http\UploadedFile;

class UploadCrmFileAction
{
    public function __construct(
        protected CrmFileStorageService $fileStorageService,
        protected ActivityLogger $activityLogger,
    ) {}

    public function execute(
        UploadedFile $file,
        string $buyerId,
        User $actor,
        ?string $dealId = null,
        ?string $description = null,
    ): CrmFile {
        $crmFile = $this->fileStorageService->store($file, $buyerId, $actor, $dealId, $description);

        Buyer::query()->whereKey($buyerId)->update(['last_activity_at' => now()]);

        $this->activityLogger->log(
            action: ActivityAction::Created,
            summary: "{$actor->fullName()} uploaded file {$crmFile->original_name}",
            entityType: Buyer::class,
            entityId: $buyerId,
            actor: $actor,
            buyerId: $buyerId,
            dealId: $dealId,
        );

        return $crmFile;
    }
}
