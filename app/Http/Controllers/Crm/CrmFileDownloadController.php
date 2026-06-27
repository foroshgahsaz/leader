<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\CrmFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CrmFileDownloadController extends Controller
{
    public function __invoke(CrmFile $file): StreamedResponse
    {
        $this->authorize('view', $file);

        return Storage::disk($file->disk)->download($file->path, $file->original_name);
    }
}
