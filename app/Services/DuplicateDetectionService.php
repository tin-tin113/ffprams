<?php

namespace App\Services;

use App\Models\Beneficiary;
use App\Support\PhilippineMobileNumber;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DuplicateDetectionService
{
    /**
     * Find potential duplicates for the given beneficiary data.
     *
     * @param  array  $data  Validated beneficiary form data
     * @param  int|null  $excludeId  ID to exclude (for updates)
     * @return Collection Collection of matches: ['beneficiary' => Beneficiary, 'match_type' => string, 'score' => int]
     */
    public function findPotentialDuplicates(array $data, ?int $excludeId = null): Collection
    {
        $matches = collect();

        // 1. Check exact registration number matches (including soft-deleted)
        $matches = $matches->merge($this->findRegistrationMatches($data, $excludeId));

        // 2. Check name + DOB + barangay combination
        $matches = $matches->merge($this->findNameDobBarangayMatches($data, $excludeId));

        // 3. Check contact number matches
        $matches = $matches->merge($this->findContactMatches($data, $excludeId));

        // 4. Check fuzzy name matches in same barangay
        $matches = $matches->merge($this->findFuzzyNameMatches($data, $excludeId));

        // Remove duplicates (same beneficiary matched multiple ways), keep highest score
        return $matches
            ->groupBy(fn ($match) => $match['beneficiary']->id)
            ->map(fn ($group) => $group->sortByDesc('score')->first())
            ->values();
    }

    /**
     * Check for exact registration number matches.
     */
    private function findRegistrationMatches(array $data, ?int $excludeId): Collection
    {
        $matches = collect();

        $registrationFields = [
            'rsbsa_number' => 'registration_number',
            'fishr_number' => 'registration_number',
            'cloa_ep_number' => 'registration_number',
        ];

        foreach ($registrationFields as $field => $matchType) {
            if (! empty($data[$field])) {
                $existing = Beneficiary::withTrashed()
                    ->where($field, $data[$field])
                    ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
                    ->first();

                if ($existing) {
                    $matches->push([
                        'beneficiary' => $existing,
                        'match_type' => $matchType,
                        'score' => 100,
                    ]);
                }
            }
        }

        return $matches;
    }

    /**
     * Check for name + date of birth + barangay combination matches.
     */
    private function findNameDobBarangayMatches(array $data, ?int $excludeId): Collection
    {
        $matches = collect();

        if (empty($data['full_name']) || empty($data['date_of_birth']) || empty($data['barangay_id'])) {
            return $matches;
        }

        $normalizedName = $this->normalizeName($data['full_name']);

        $existing = Beneficiary::withTrashed()
            ->where('barangay_id', $data['barangay_id'])
            ->whereDate('date_of_birth', $data['date_of_birth'])
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->get();

        foreach ($existing as $beneficiary) {
            $existingNormalizedName = $this->normalizeName($beneficiary->full_name);

            if ($normalizedName === $existingNormalizedName) {
                $matches->push([
                    'beneficiary' => $beneficiary,
                    'match_type' => 'name_dob_barangay',
                    'score' => 90,
                ]);
            }
        }

        return $matches;
    }

    /**
     * Check for contact number matches.
     */
    private function findContactMatches(array $data, ?int $excludeId): Collection
    {
        $matches = collect();

        if (empty($data['contact_number'])) {
            return $matches;
        }

        $normalizedContact = $this->normalizePhoneNumber($data['contact_number']);

        if (strlen($normalizedContact) < 10) {
            return $matches; // Too short to be reliable
        }

        $existing = Beneficiary::withTrashed()
            ->whereNotNull('contact_number')
            ->where('contact_number', '!=', '')
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->get();

        foreach ($existing as $beneficiary) {
            $existingNormalized = $this->normalizePhoneNumber($beneficiary->contact_number);

            if ($normalizedContact === $existingNormalized) {
                $matches->push([
                    'beneficiary' => $beneficiary,
                    'match_type' => 'contact',
                    'score' => 80,
                ]);
            }
        }

        return $matches;
    }

    /**
     * Check for fuzzy name matches in the same barangay.
     */
    private function findFuzzyNameMatches(array $data, ?int $excludeId): Collection
    {
        $matches = collect();

        if (empty($data['full_name']) || empty($data['barangay_id'])) {
            return $matches;
        }

        $normalizedName = $this->normalizeName($data['full_name']);

        $existing = Beneficiary::withTrashed()
            ->where('barangay_id', $data['barangay_id'])
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->get();

        foreach ($existing as $beneficiary) {
            $existingNormalizedName = $this->normalizeName($beneficiary->full_name);

            // Skip exact matches (already caught by name_dob_barangay)
            if ($normalizedName === $existingNormalizedName) {
                continue;
            }

            $similarity = $this->calculateNameSimilarity($normalizedName, $existingNormalizedName);

            // Only flag if similarity is high enough (Levenshtein distance <= 3 characters)
            if ($similarity >= 70) {
                $matches->push([
                    'beneficiary' => $beneficiary,
                    'match_type' => 'fuzzy_name',
                    'score' => $similarity,
                ]);
            }
        }

        return $matches;
    }

    /**
     * Normalize a phone number by removing all non-digit characters.
     */
    private function normalizePhoneNumber(string $phone): string
    {
        $normalized = PhilippineMobileNumber::normalize($phone);

        if ($normalized !== null) {
            // Compare using the 9XXXXXXXXX segment to match equivalent local/international forms.
            return substr($normalized, 1);
        }

        return preg_replace('/\D/', '', $phone) ?? '';
    }

    /**
     * Normalize a name for comparison.
     */
    private function normalizeName(string $name): string
    {
        // Convert to lowercase
        $normalized = Str::lower($name);

        // Remove common suffixes
        $suffixes = ['jr', 'jr.', 'sr', 'sr.', 'ii', 'iii', 'iv'];
        foreach ($suffixes as $suffix) {
            $normalized = preg_replace('/\b'.preg_quote($suffix, '/').'\b/i', '', $normalized);
        }

        // Remove extra whitespace and trim
        $normalized = preg_replace('/\s+/', ' ', $normalized);
        $normalized = trim($normalized);

        // Remove non-alphabetic characters except spaces
        $normalized = preg_replace('/[^a-z\s]/', '', $normalized);

        return $normalized;
    }

    /**
     * Calculate similarity between two names (0-100 scale).
     */
    private function calculateNameSimilarity(string $name1, string $name2): int
    {
        // Use Levenshtein distance
        $distance = levenshtein($name1, $name2);
        $maxLen = max(strlen($name1), strlen($name2));

        if ($maxLen === 0) {
            return 100;
        }

        // Convert distance to similarity percentage
        $similarity = (1 - ($distance / $maxLen)) * 100;

        return (int) round($similarity);
    }
}
