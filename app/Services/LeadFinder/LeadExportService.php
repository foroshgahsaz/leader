<?php

namespace App\Services\LeadFinder;

use App\Models\Buyer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeadExportService
{
    public function streamCsv(?int $ownerId = null, ?string $status = null): StreamedResponse
    {
        $filename = 'leads-export-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(
            fn () => $this->writeCsv($ownerId, $status),
            $filename,
            [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Cache-Control' => 'no-store, no-cache',
            ],
        );
    }

    protected function writeCsv(?int $ownerId, ?string $status): void
    {
        $handle = fopen('php://output', 'w');

        if ($handle === false) {
            throw new \RuntimeException('Unable to open CSV output stream.');
        }

        fwrite($handle, "\xEF\xBB\xBF");

        fputcsv($handle, [
            'id',
            'name',
            'country_code',
            'city',
            'website',
            'industry',
            'company_type',
            'status',
            'source',
            'owner_id',
            'is_favorite',
            'is_dnc',
            'last_contacted_at',
            'last_activity_at',
            'created_at',
        ]);

        Buyer::query()
            ->when($ownerId !== null, fn ($query) => $query->where('owner_id', $ownerId))
            ->when($status !== null, fn ($query) => $query->where('status', $status))
            ->orderBy('name')
            ->chunkById(100, function ($buyers) use ($handle): void {
                /** @var Buyer $buyer */
                foreach ($buyers as $buyer) {
                    fputcsv($handle, [
                        $buyer->id,
                        $buyer->name,
                        $buyer->country_code,
                        $buyer->city,
                        $buyer->website,
                        $buyer->industry,
                        $buyer->company_type?->value,
                        $buyer->status->value,
                        $buyer->source->value,
                        $buyer->owner_id,
                        $buyer->is_favorite ? '1' : '0',
                        $buyer->is_dnc ? '1' : '0',
                        $buyer->last_contacted_at?->toIso8601String(),
                        $buyer->last_activity_at?->toIso8601String(),
                        $buyer->created_at?->toIso8601String(),
                    ]);
                }
            });

        fclose($handle);
    }
}
