<?php

namespace App\Data\LeadFinder;

use App\Enums\BuyerStatus;

readonly class BuyerSearchCriteriaData
{
    public function __construct(
        public ?string $product = null,
        public ?string $productId = null,
        public array $countries = [],
        public ?string $industry = null,
        public ?string $companyType = null,
        public array $companyTypes = [],
        public ?int $minScore = null,
        public ?int $maxScore = null,
        public ?string $query = null,
        public ?bool $isFavorite = null,
        public ?BuyerStatus $status = null,
        public array $tags = [],
        public ?int $ownerId = null,
        public ?string $sortBy = null,
        public ?string $sortDirection = null,
        public ?int $perPage = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            product: $data['product'] ?? null,
            productId: $data['product_id'] ?? null,
            countries: $data['countries'] ?? [],
            industry: $data['industry'] ?? null,
            companyType: $data['company_type'] ?? null,
            companyTypes: $data['company_types'] ?? [],
            minScore: isset($data['min_score']) ? (int) $data['min_score'] : null,
            maxScore: isset($data['max_score']) ? (int) $data['max_score'] : null,
            query: $data['query'] ?? null,
            isFavorite: isset($data['is_favorite']) ? (bool) $data['is_favorite'] : null,
            status: isset($data['status']) ? BuyerStatus::from($data['status']) : null,
            tags: $data['tags'] ?? [],
            ownerId: isset($data['owner_id']) ? (int) $data['owner_id'] : null,
            sortBy: $data['sort_by'] ?? null,
            sortDirection: $data['sort_direction'] ?? null,
            perPage: isset($data['per_page']) ? (int) $data['per_page'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'product' => $this->product,
            'product_id' => $this->productId,
            'countries' => $this->countries !== [] ? $this->countries : null,
            'industry' => $this->industry,
            'company_type' => $this->companyType,
            'company_types' => $this->companyTypes !== [] ? $this->companyTypes : null,
            'min_score' => $this->minScore,
            'max_score' => $this->maxScore,
            'query' => $this->query,
            'is_favorite' => $this->isFavorite,
            'status' => $this->status?->value,
            'tags' => $this->tags !== [] ? $this->tags : null,
            'owner_id' => $this->ownerId,
            'sort_by' => $this->sortBy,
            'sort_direction' => $this->sortDirection,
            'per_page' => $this->perPage,
        ], fn (mixed $value) => $value !== null);
    }
}
