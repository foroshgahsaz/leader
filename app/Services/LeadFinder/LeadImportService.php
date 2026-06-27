<?php

namespace App\Services\LeadFinder;

use App\Data\LeadFinder\ImportLeadRowData;
use App\Enums\ImportBatchStatus;
use App\Jobs\LeadFinder\ProcessLeadImportJob;
use App\Models\ImportBatch;
use App\Models\ImportBatchRow;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LeadImportService
{
    /**
     * @var array<int, string>
     */
    protected array $requiredHeaders = ['name', 'country_code'];

    public function import(UploadedFile|string $source, string $filename, User $user): ImportBatch
    {
        $path = $source instanceof UploadedFile ? $source->getRealPath() : $source;

        if (! is_string($path) || ! is_readable($path)) {
            throw new \InvalidArgumentException('Import source is not readable.');
        }

        return DB::transaction(function () use ($path, $filename, $user): ImportBatch {
            $rows = $this->parseCsv($path);

            $batch = ImportBatch::query()->create([
                'user_id' => $user->id,
                'filename' => $filename,
                'status' => ImportBatchStatus::Pending,
                'total_rows' => count($rows),
            ]);

            foreach ($rows as $index => $row) {
                ImportBatchRow::query()->create([
                    'import_batch_id' => $batch->id,
                    'row_number' => $index + 1,
                    'payload' => $row,
                    'status' => ImportBatchStatus::Pending,
                ]);
            }

            ProcessLeadImportJob::dispatch($batch->id, $user->id);

            return $batch->fresh();
        });
    }

    /**
     * @return array<int, array<string, string|null>>
     */
    protected function parseCsv(string $path): array
    {
        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw new \RuntimeException('Unable to read import file.');
        }

        $headerRow = fgetcsv($handle);

        if ($headerRow === false) {
            fclose($handle);

            throw new \InvalidArgumentException('Import file is empty.');
        }

        $headers = array_map(
            fn (mixed $header) => Str::snake(trim((string) $header)),
            $headerRow,
        );

        foreach ($this->requiredHeaders as $requiredHeader) {
            if (! in_array($requiredHeader, $headers, true)) {
                fclose($handle);

                throw new \InvalidArgumentException("Missing required column: {$requiredHeader}");
            }
        }

        $rows = [];

        while (($data = fgetcsv($handle)) !== false) {
            if ($this->isEmptyRow($data)) {
                continue;
            }

            $record = [];

            foreach ($headers as $index => $header) {
                $record[$header] = isset($data[$index]) ? trim((string) $data[$index]) : null;
            }

            if (empty($record['name']) || empty($record['country_code'])) {
                continue;
            }

            $rows[] = $record;
        }

        fclose($handle);

        if ($rows === []) {
            throw new \InvalidArgumentException('Import file contains no valid rows.');
        }

        return $rows;
    }

    /**
     * @param  array<int, string|null>  $row
     */
    protected function isEmptyRow(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    public function rowDataFromPayload(array $payload): ImportLeadRowData
    {
        return new ImportLeadRowData(
            name: trim((string) ($payload['name'] ?? '')),
            countryCode: strtoupper(trim((string) ($payload['country_code'] ?? ''))),
            city: $this->nullableString($payload['city'] ?? null),
            website: $this->nullableString($payload['website'] ?? null),
            industry: $this->nullableString($payload['industry'] ?? null),
            companyType: $this->nullableString($payload['company_type'] ?? null),
            email: $this->nullableString($payload['email'] ?? null),
            phone: $this->nullableString($payload['phone'] ?? null),
        );
    }

    protected function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $string = trim((string) $value);

        return $string === '' ? null : $string;
    }
}
