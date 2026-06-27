<?php

namespace App\Jobs\LeadFinder;

use App\Contracts\Repositories\BuyerRepositoryInterface;
use App\Contracts\Repositories\GlobalBuyerRepositoryInterface;
use App\Data\LeadFinder\SaveBuyerData;
use App\Enums\ImportBatchStatus;
use App\Enums\LeadSource;
use App\Models\BuyerContact;
use App\Models\ImportBatch;
use App\Models\ImportBatchRow;
use App\Repositories\Eloquent\GlobalBuyerRepository;
use App\Services\LeadFinder\LeadImportService;
use App\Support\OrganizationContext;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProcessLeadImportJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public string $importBatchId,
        public int $userId,
    ) {}

    public function handle(
        GlobalBuyerRepositoryInterface $globalBuyerRepository,
        BuyerRepositoryInterface $buyerRepository,
        LeadImportService $leadImportService,
        OrganizationContext $organizationContext,
    ): void {
        $batch = ImportBatch::query()->withoutGlobalScopes()->find($this->importBatchId);

        if (! $batch) {
            return;
        }

        $organizationContext->setId($batch->organization_id);

        $batch->update(['status' => ImportBatchStatus::Processing]);

        $errors = [];
        $successRows = 0;
        $failedRows = 0;
        $processedRows = 0;

        ImportBatchRow::query()
            ->where('import_batch_id', $batch->id)
            ->orderBy('row_number')
            ->chunkById(50, function ($rows) use (
                $globalBuyerRepository,
                $buyerRepository,
                $leadImportService,
                &$errors,
                &$successRows,
                &$failedRows,
                &$processedRows,
            ): void {
                $userId = $this->userId;

                /** @var ImportBatchRow $row */
                foreach ($rows as $row) {
                    $processedRows++;

                    try {
                        DB::transaction(function () use (
                            $row,
                            $globalBuyerRepository,
                            $buyerRepository,
                            $leadImportService,
                            $userId,
                            &$successRows,
                        ): void {
                            $rowData = $leadImportService->rowDataFromPayload($row->payload);
                            $providerKey = GlobalBuyerRepository::providerKeyFromImportRow($rowData);

                            $globalBuyer = $globalBuyerRepository->upsertFromImport($rowData, $providerKey);

                            if (! $buyerRepository->existsForGlobalBuyer($globalBuyer->id)) {
                                $buyer = $buyerRepository->create(
                                    new SaveBuyerData(
                                        globalBuyerId: $globalBuyer->id,
                                        source: LeadSource::Import,
                                    ),
                                    $userId,
                                );

                                $this->createContactIfPresent($buyer->id, $rowData->email, $rowData->phone, $userId);

                                $row->update([
                                    'status' => ImportBatchStatus::Completed,
                                    'buyer_id' => $buyer->id,
                                ]);
                            } else {
                                $existingBuyer = $buyerRepository->findByGlobalBuyerId($globalBuyer->id);

                                $row->update([
                                    'status' => ImportBatchStatus::Completed,
                                    'buyer_id' => $existingBuyer?->id,
                                ]);
                            }

                            $successRows++;
                        });
                    } catch (Throwable $exception) {
                        $failedRows++;
                        $errors[] = [
                            'row_number' => $row->row_number,
                            'message' => $exception->getMessage(),
                        ];

                        $row->update([
                            'status' => ImportBatchStatus::Failed,
                            'error_message' => $exception->getMessage(),
                        ]);
                    }
                }
            });

        $batch->update([
            'status' => $failedRows > 0 && $successRows === 0
                ? ImportBatchStatus::Failed
                : ImportBatchStatus::Completed,
            'processed_rows' => $processedRows,
            'success_rows' => $successRows,
            'failed_rows' => $failedRows,
            'errors' => $errors !== [] ? $errors : null,
            'completed_at' => now(),
        ]);
    }

    protected function createContactIfPresent(string $buyerId, ?string $email, ?string $phone, int $userId): void
    {
        if (! $email && ! $phone) {
            return;
        }

        BuyerContact::query()->create([
            'buyer_id' => $buyerId,
            'full_name' => 'Imported Contact',
            'email' => $email,
            'phone' => $phone,
            'is_primary' => true,
            'is_verified' => false,
            'source' => LeadSource::Import->value,
            'created_by' => $userId,
        ]);
    }
}
