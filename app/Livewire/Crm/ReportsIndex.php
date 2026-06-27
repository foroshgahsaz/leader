<?php

namespace App\Livewire\Crm;

use App\Services\Crm\CrmReportService;
use Livewire\Attributes\Layout;
use Livewire\Component;

class ReportsIndex extends Component
{
    public function mount(): void
    {
        abort_unless(auth()->user()->can('crm.reports.view'), 403);
    }

    #[Layout('layouts.crm', ['heading' => 'CRM Reports', 'subheading' => 'Pipeline and activity overview.'])]
    public function render(CrmReportService $crmReportService)
    {
        return view('livewire.crm.reports-index', [
            'dashboard' => $crmReportService->dashboard(),
        ]);
    }
}
