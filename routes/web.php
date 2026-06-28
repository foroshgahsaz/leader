<?php

use App\Http\Controllers\Crm\CrmFileDownloadController;
use App\Http\Controllers\LeadFinder\LeadExportController;
use App\Http\Controllers\LeadFinder\LeadImportController;
use App\Livewire\Activity\ActivityLogIndex;
use App\Livewire\Crm\CompanyIndex;
use App\Livewire\Crm\CompanyShow;
use App\Livewire\Dashboard\DashboardIndex;
use App\Livewire\Crm\PipelineBoard;
use App\Livewire\Crm\ReportsIndex;
use App\Livewire\Crm\TaskIndex;
use App\Livewire\LeadFinder\LeadDetail;
use App\Livewire\LeadFinder\LeadSearch;
use App\Livewire\LeadFinder\SavedLeads;
use App\Livewire\Notifications\NotificationIndex;
use App\Livewire\Settings\PreferencesForm;
use App\Livewire\Settings\TeamManagement;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware(['auth', 'verified', 'org.context'])->group(function () {
    Route::get('dashboard', DashboardIndex::class)->name('dashboard');

    Route::view('profile', 'profile')->name('profile');

    Route::prefix('discover')->name('discover.')->group(function () {
        Route::get('/', LeadSearch::class)->name('index');
        Route::get('/saved', SavedLeads::class)->name('saved');
        Route::get('/leads/{buyer}', LeadDetail::class)->name('leads.show');
        Route::get('/export', LeadExportController::class)->name('export');
        Route::post('/import', [LeadImportController::class, 'store'])->name('import.store');
        Route::get('/imports/{importBatch}', [LeadImportController::class, 'show'])->name('imports.show');
    });

    Route::prefix('crm')->name('crm.')->group(function () {
        Route::get('/', CompanyIndex::class)->name('companies.index');
        Route::get('/companies/{company}', CompanyShow::class)->name('companies.show');
        Route::get('/tasks', TaskIndex::class)->name('tasks');
        Route::get('/pipeline', PipelineBoard::class)->name('pipeline');
        Route::get('/reports', ReportsIndex::class)->name('reports');
        Route::get('/files/{file}/download', CrmFileDownloadController::class)->name('files.download');
    });

    Route::prefix('settings')->name('settings.')->group(function () {
        Volt::route('company', 'pages.settings.company')->name('company');
        Route::get('team', TeamManagement::class)->name('team');
        Route::get('notifications', NotificationIndex::class)->name('notifications');
        Route::get('activity', ActivityLogIndex::class)->name('activity');
        Route::get('preferences', PreferencesForm::class)->name('preferences');
    });
});

require __DIR__.'/auth.php';
