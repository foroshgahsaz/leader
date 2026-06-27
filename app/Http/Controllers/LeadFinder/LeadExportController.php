<?php

namespace App\Http\Controllers\LeadFinder;

use App\Http\Controllers\Controller;
use App\Services\LeadFinder\LeadExportService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeadExportController extends Controller
{
    public function __invoke(LeadExportService $leadExportService): StreamedResponse
    {
        $this->authorize('export', \App\Models\Buyer::class);

        return $leadExportService->streamCsv(
            ownerId: request()->integer('owner_id') ?: null,
            status: request()->string('status')->toString() ?: null,
        );
    }
}
