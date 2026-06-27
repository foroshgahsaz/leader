<?php

namespace App\Services\LeadFinder;

use App\Enums\CompanyType;
use App\Enums\ScoreBand;
use App\Models\GlobalBuyer;
use App\Models\Product;

class LeadScoringService
{
    public const MODEL_VERSION = 'rules-v1';

    /**
     * @return array{score: int, score_band: ScoreBand, factors: array<string, int>, explanation: string}
     */
    public function score(GlobalBuyer $globalBuyer, ?Product $product = null): array
    {
        $factors = [];

        $importPoints = min(30, $globalBuyer->import_activity_level * 6);
        if ($importPoints > 0) {
            $factors['import_activity'] = $importPoints;
        }

        if (in_array($globalBuyer->company_type, [CompanyType::Importer, CompanyType::Distributor], true)) {
            $factors['company_type'] = 20;
        } elseif ($globalBuyer->company_type !== null) {
            $factors['company_type'] = 10;
        }

        if ($globalBuyer->website) {
            $factors['website_present'] = 10;
        }

        $freshnessPoints = $this->scoreDataFreshness($globalBuyer);
        if ($freshnessPoints > 0) {
            $factors['data_freshness'] = $freshnessPoints;
        }

        $employeePoints = $this->scoreEmployeeRange($globalBuyer->employee_range);
        if ($employeePoints > 0) {
            $factors['company_size'] = $employeePoints;
        }

        if ($product !== null) {
            $productMatchPoints = $this->scoreProductMatch($globalBuyer, $product);
            if ($productMatchPoints > 0) {
                $factors['product_match'] = $productMatchPoints;
            }
        }

        $score = min(100, array_sum($factors));

        return [
            'score' => $score,
            'score_band' => ScoreBand::fromScore($score),
            'factors' => $factors,
            'explanation' => $this->buildExplanation($score, $factors, $product),
        ];
    }

    protected function scoreDataFreshness(GlobalBuyer $globalBuyer): int
    {
        if (! $globalBuyer->data_freshness_at) {
            return 0;
        }

        $daysOld = $globalBuyer->data_freshness_at->diffInDays(now());

        return match (true) {
            $daysOld <= 30 => 15,
            $daysOld <= 90 => 10,
            $daysOld <= 180 => 5,
            default => 0,
        };
    }

    protected function scoreEmployeeRange(?string $employeeRange): int
    {
        return match ($employeeRange) {
            '500+', '201-500' => 10,
            '51-200' => 8,
            '11-50' => 5,
            '1-10' => 2,
            default => 0,
        };
    }

    protected function scoreProductMatch(GlobalBuyer $globalBuyer, Product $product): int
    {
        $points = 0;

        if ($product->industry && $globalBuyer->industry
            && stripos($globalBuyer->industry, $product->industry) !== false) {
            $points += 15;
        }

        if ($product->category && $globalBuyer->industry
            && stripos($globalBuyer->industry, $product->category) !== false) {
            $points += 10;
        }

        $hsCodes = $product->hsCodes()->pluck('hs_code')->all();
        $importProfile = json_encode($globalBuyer->import_profile ?? []);

        foreach ($hsCodes as $hsCode) {
            if ($importProfile && stripos($importProfile, (string) $hsCode) !== false) {
                $points += 15;
                break;
            }
        }

        return min(25, $points);
    }

    /**
     * @param  array<string, int>  $factors
     */
    protected function buildExplanation(int $score, array $factors, ?Product $product): string
    {
        if ($factors === []) {
            return 'Limited data available; score reflects minimal firmographic signals.';
        }

        $parts = [];

        if (isset($factors['import_activity'])) {
            $parts[] = 'active import profile';
        }

        if (isset($factors['company_type'])) {
            $parts[] = 'relevant company type for export outreach';
        }

        if (isset($factors['product_match'])) {
            $productLabel = $product?->name ?? 'selected product';
            $parts[] = "alignment with {$productLabel}";
        }

        if (isset($factors['data_freshness'])) {
            $parts[] = 'recently refreshed data';
        }

        $summary = implode(', ', $parts);

        return "Score {$score}/100 based on {$summary}.";
    }
}
