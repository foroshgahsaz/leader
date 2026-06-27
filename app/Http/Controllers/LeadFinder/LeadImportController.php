<?php

namespace App\Http\Controllers\LeadFinder;

use App\Http\Controllers\Controller;
use App\Http\Requests\LeadFinder\LeadImportRequest;
use App\Models\ImportBatch;
use App\Services\LeadFinder\LeadImportService;
use Illuminate\Http\RedirectResponse;

class LeadImportController extends Controller
{
    public function store(LeadImportRequest $request, LeadImportService $leadImportService): RedirectResponse
    {
        $batch = $leadImportService->importFromUploadedFile(
            $request->file('file'),
            auth()->user(),
        );

        return redirect()
            ->route('discover.imports.show', $batch)
            ->with('status', __('Import started. Processing :count rows.', ['count' => $batch->total_rows]));
    }

    public function show(ImportBatch $importBatch): RedirectResponse
    {
        $this->authorize('import', \App\Models\Buyer::class);

        abort_unless($importBatch->organization_id === auth()->user()->current_organization_id, 404);

        return redirect()
            ->route('discover.index')
            ->with('status', __('Import batch: :success succeeded, :failed failed.', [
                'success' => $importBatch->success_rows,
                'failed' => $importBatch->failed_rows,
            ]));
    }
}
