<?php

namespace App\Models;

use App\Enums\AiGenerationStatus;
use App\Enums\AiGenerationType;
use App\Models\Concerns\BelongsToOrganization;
use App\Models\Concerns\HasUuidPrimaryKey;
use Database\Factories\AiGenerationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiGeneration extends Model
{
    /** @use HasFactory<AiGenerationFactory> */
    use HasFactory;

    use BelongsToOrganization;
    use HasUuidPrimaryKey;

    protected $fillable = [
        'organization_id',
        'buyer_id',
        'user_id',
        'type',
        'status',
        'input',
        'output',
        'prompt_tokens',
        'completion_tokens',
        'total_tokens',
        'model',
        'model_version',
        'error_message',
        'parent_id',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => AiGenerationType::class,
            'status' => AiGenerationStatus::class,
            'input' => 'array',
            'output' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    protected static function newFactory(): AiGenerationFactory
    {
        return AiGenerationFactory::new();
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Buyer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function markProcessing(): self
    {
        $this->update([
            'status' => AiGenerationStatus::Processing,
            'started_at' => now(),
        ]);

        return $this;
    }

    /**
     * @param  array<string, mixed>  $output
     */
    public function markCompleted(
        array $output,
        string $model,
        int $promptTokens,
        int $completionTokens,
        int $totalTokens,
    ): self {
        $this->update([
            'status' => AiGenerationStatus::Completed,
            'output' => $output,
            'model' => $model,
            'prompt_tokens' => $promptTokens,
            'completion_tokens' => $completionTokens,
            'total_tokens' => $totalTokens,
            'completed_at' => now(),
            'error_message' => null,
        ]);

        return $this;
    }

    public function markFailed(string $message): self
    {
        $this->update([
            'status' => AiGenerationStatus::Failed,
            'error_message' => $message,
            'completed_at' => now(),
        ]);

        return $this;
    }
}
