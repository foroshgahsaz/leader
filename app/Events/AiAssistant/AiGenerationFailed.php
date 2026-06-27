<?php

namespace App\Events\AiAssistant;

use App\Models\AiGeneration;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AiGenerationFailed
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public AiGeneration $generation,
        public string $errorMessage,
    ) {}
}
