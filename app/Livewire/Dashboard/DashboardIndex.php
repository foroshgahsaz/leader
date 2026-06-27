<?php

namespace App\Livewire\Dashboard;

use App\Services\Dashboard\DashboardService;
use Livewire\Attributes\Layout;
use Livewire\Component;

class DashboardIndex extends Component
{
    public function mount(): void
    {
        $this->authorize('view-dashboard');
    }

    #[Layout('layouts.app')]
    public function render(DashboardService $dashboardService): \Illuminate\Contracts\View\View
    {
        $data = $dashboardService->build(auth()->user());

        return view('livewire.dashboard.index', [
            'data' => $data,
        ]);
    }
}
