<?php

namespace App\Services\LeadFinder;

use App\Models\Buyer;
use App\Models\BuyerSummary;
use App\Models\GlobalBuyer;
use App\Support\OrganizationContext;

class CompanySummaryService
{
    public const MODEL_VERSION = 'template-v1';

    public function __construct(
        protected OrganizationContext $organizationContext,
    ) {}

    public function generate(GlobalBuyer $globalBuyer, ?Buyer $buyer = null): BuyerSummary
    {
        $keyFacts = $this->buildKeyFacts($globalBuyer);
        $dataGaps = $this->identifyDataGaps($globalBuyer);
        $confidence = $this->determineConfidence($dataGaps);

        return BuyerSummary::query()->create([
            'organization_id' => $this->organizationContext->id(),
            'buyer_id' => $buyer?->id,
            'global_buyer_id' => $globalBuyer->id,
            'summary' => $this->buildSummaryText($globalBuyer, $keyFacts),
            'key_facts' => $keyFacts,
            'suggested_angle' => $this->buildSuggestedAngle($globalBuyer),
            'confidence' => $confidence,
            'data_gaps' => $dataGaps,
            'model_version' => self::MODEL_VERSION,
            'generated_at' => now(),
        ]);
    }

    /**
     * @return array<int, string>
     */
    protected function buildKeyFacts(GlobalBuyer $globalBuyer): array
    {
        $facts = [];

        $facts[] = "Operates in {$globalBuyer->country_code}"
            .($globalBuyer->city ? " ({$globalBuyer->city})" : '');

        if ($globalBuyer->industry) {
            $facts[] = "Industry: {$globalBuyer->industry}";
        }

        if ($globalBuyer->company_type) {
            $facts[] = 'Company type: '.$globalBuyer->company_type->label();
        }

        if ($globalBuyer->employee_range) {
            $facts[] = "Estimated size: {$globalBuyer->employee_range} employees";
        }

        if ($globalBuyer->import_activity_level > 0) {
            $facts[] = "Import activity level: {$globalBuyer->import_activity_level}/5";
        }

        if ($globalBuyer->website) {
            $facts[] = "Website: {$globalBuyer->website}";
        }

        return $facts;
    }

    protected function buildSummaryText(GlobalBuyer $globalBuyer, array $keyFacts): string
    {
        $name = $globalBuyer->display_name;
        $location = $globalBuyer->city
            ? "{$globalBuyer->city}, {$globalBuyer->country_code}"
            : $globalBuyer->country_code;

        $summary = "{$name} is a company based in {$location}.";

        if ($globalBuyer->industry) {
            $summary .= " They operate in the {$globalBuyer->industry} sector.";
        }

        if ($globalBuyer->company_type) {
            $summary .= ' Their business model is classified as '
                .$globalBuyer->company_type->label()
                .', which may indicate procurement or distribution needs relevant to export suppliers.';
        }

        if ($globalBuyer->import_activity_level >= 3) {
            $summary .= ' Import activity signals suggest an established cross-border sourcing pattern.';
        } elseif ($globalBuyer->import_activity_level > 0) {
            $summary .= ' Some import activity has been observed, indicating potential openness to international suppliers.';
        }

        if (count($keyFacts) <= 2) {
            $summary .= ' Additional firmographic data is limited; validate fit before prioritizing outreach.';
        }

        return $summary;
    }

    protected function buildSuggestedAngle(GlobalBuyer $globalBuyer): string
    {
        return match ($globalBuyer->company_type?->value) {
            'importer' => 'Lead with supply reliability, compliance documentation, and landed cost transparency.',
            'distributor' => 'Emphasize margin support, co-marketing, and regional exclusivity options.',
            'retailer' => 'Highlight consumer demand trends, packaging flexibility, and replenishment cadence.',
            'manufacturer' => 'Focus on input quality, specification fit, and production continuity.',
            'wholesaler' => 'Position volume pricing, logistics efficiency, and SKU breadth.',
            default => 'Open with a concise value proposition tied to the buyer\'s market and category needs.',
        };
    }

    /**
     * @return array<int, string>
     */
    protected function identifyDataGaps(GlobalBuyer $globalBuyer): array
    {
        $gaps = [];

        if (! $globalBuyer->website) {
            $gaps[] = 'website';
        }

        if (! $globalBuyer->industry) {
            $gaps[] = 'industry';
        }

        if (! $globalBuyer->company_type) {
            $gaps[] = 'company_type';
        }

        if (! $globalBuyer->employee_range) {
            $gaps[] = 'employee_range';
        }

        if ($globalBuyer->import_activity_level === 0) {
            $gaps[] = 'import_activity';
        }

        if (empty($globalBuyer->import_profile)) {
            $gaps[] = 'import_profile';
        }

        return $gaps;
    }

    /**
     * @param  array<int, string>  $dataGaps
     */
    protected function determineConfidence(array $dataGaps): string
    {
        $gapCount = count($dataGaps);

        return match (true) {
            $gapCount <= 1 => 'high',
            $gapCount <= 3 => 'medium',
            default => 'low',
        };
    }
}
