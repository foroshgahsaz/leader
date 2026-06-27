<?php

namespace App\Services\Crm;

use App\Models\CrmFile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CrmFileStorageService
{
    public function store(
        UploadedFile $file,
        string $buyerId,
        User $user,
        ?string $dealId = null,
        ?string $description = null,
    ): CrmFile {
        $organizationId = $user->current_organization_id;
        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
        $path = "crm/{$organizationId}/{$buyerId}/{$filename}";

        Storage::disk('local')->put($path, file_get_contents($file->getRealPath()));

        return CrmFile::query()->create([
            'buyer_id' => $buyerId,
            'deal_id' => $dealId,
            'uploaded_by' => $user->id,
            'original_name' => $file->getClientOriginalName(),
            'disk' => 'local',
            'path' => $path,
            'mime_type' => $file->getClientMimeType(),
            'size_bytes' => $file->getSize(),
            'description' => $description,
        ]);
    }

    public function delete(CrmFile $file): void
    {
        $file->deleteStoredFile();
        $file->delete();
    }
}
