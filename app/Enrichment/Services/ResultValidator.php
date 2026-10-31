<?php

namespace App\Enrichment\Services;

class ResultValidator
{
    const MATCH_STATUS = ['found', 'ambiguous', 'not_found'];
    const CONCENTRATION = ['EDT', 'EDP', 'Parfum', 'Cologne', 'Extrait', 'Other'];
    const GENDER = ['women', 'men', 'unisex'];
    const SCENT_FAMILY = ['citrus', 'aromatic', 'fougere', 'woody', 'amber_oriental', 'floral', 'fruity', 'gourmand', 'musk', 'aquatic', 'leather', 'chypre', 'green', 'oud', 'other'];
    const MOOD = ['fresh', 'sweet', 'elegant', 'sporty', 'sensual', 'warm', 'clean', 'bold', 'romantic', 'calm'];
    const SEASON = ['spring', 'summer', 'autumn', 'winter'];
    const TIME_OF_DAY = ['day', 'night', 'all_day'];
    const CONFIDENCE = ['high', 'medium', 'low'];
    const CROSS_CHECK_STATUS = ['match', 'partial', 'mismatch', 'no_reference'];

    /**
     * Validasi skema JSON hasil LLM + cek deterministik terhadap searah.
     *
     * @param array $data hasil decode JSON dari LLM
     * @param array|null $searahExpected hasil SearahParser::parse() untuk pembanding (gender/year/concentration)
     * @return array {ok: bool, errors: string[], review_reasons: string[]}
     */
    public static function evaluate(array $data, array $searahExpected = null)
    {
        $errors = [];
        $reasons = [];

        $data = self::fillDefaults($data);

        foreach (['input.brand', 'input.searah'] as $path) {
            $value = self::get($data, $path);
            if (!is_string($value) || trim($value) === '') {
                $errors[] = $path . ' wajib string tidak kosong';
            }
        }

        $status = self::get($data, 'match.status');
        if (!in_array($status, self::MATCH_STATUS, true)) {
            $errors[] = 'match.status harus salah satu dari: ' . implode(', ', self::MATCH_STATUS);
        }

        if ($status === 'not_found') {
            // match_note dikecualikan: penjelasan kenapa tidak ditemukan justru berguna untuk reviewer
            foreach (['match.matched_name', 'match.matched_brand', 'match.concentration', 'release_year', 'perfumers', 'gender_target', 'main_accords', 'notes.top', 'notes.middle', 'notes.base', 'scent_family', 'description_id'] as $path) {
                if (!self::isEmptyValue(self::get($data, $path))) {
                    $errors[] = 'match.status = not_found tetapi ' . $path . ' tidak kosong';
                }
            }
        }

        $concentration = self::get($data, 'match.concentration');
        if ($concentration !== null && !in_array($concentration, self::CONCENTRATION, true)) {
            $errors[] = 'match.concentration tidak valid: ' . $concentration;
        }

        $gender = self::get($data, 'gender_target');
        if ($gender !== null && !in_array($gender, self::GENDER, true)) {
            $errors[] = 'gender_target tidak valid: ' . $gender;
        }

        $family = self::get($data, 'scent_family');
        if ($family !== null && !in_array($family, self::SCENT_FAMILY, true)) {
            $errors[] = 'scent_family tidak valid: ' . $family;
        }

        $year = self::get($data, 'release_year');
        if ($year !== null && (!is_int($year) || $year < 1900 || $year > 2026)) {
            $errors[] = 'release_year harus integer 1900-2026 atau null';
        }

        foreach (['mood' => self::MOOD, 'season' => self::SEASON, 'time_of_day' => self::TIME_OF_DAY] as $field => $allowed) {
            $values = self::get($data, 'derived_tags.' . $field);
            if (!is_array($values)) {
                $errors[] = 'derived_tags.' . $field . ' harus array';
            } else {
                foreach ($values as $value) {
                    if (!in_array($value, $allowed, true)) {
                        $errors[] = 'derived_tags.' . $field . ' nilai tidak valid: ' . $value;
                    }
                }
            }
        }

        $confidence = self::get($data, 'verification.confidence');
        if (!in_array($confidence, self::CONFIDENCE, true)) {
            $errors[] = 'verification.confidence harus salah satu dari: high, medium, low';
        }

        $yearConfidence = self::get($data, 'verification.release_year_confidence');
        if ($yearConfidence !== null && !in_array($yearConfidence, self::CONFIDENCE, true)) {
            $errors[] = 'verification.release_year_confidence tidak valid: ' . $yearConfidence;
        }

        $sources = self::get($data, 'verification.sources');
        if (!is_array($sources)) {
            $errors[] = 'verification.sources harus array';
            $sources = [];
        } else {
            foreach ($sources as $index => $source) {
                if (!is_array($source) || !isset($source['url']) || !is_string($source['url']) || filter_var($source['url'], FILTER_VALIDATE_URL) === false) {
                    $errors[] = 'verification.sources[' . $index . '] harus berisi title dan url valid';
                }
            }
        }

        $crossStatus = self::get($data, 'verification.cross_check.reference_notes_status');
        if (!in_array($crossStatus, self::CROSS_CHECK_STATUS, true)) {
            $errors[] = 'verification.cross_check.reference_notes_status tidak valid: ' . $crossStatus;
        }

        if (!is_bool(self::get($data, 'verification.needs_review'))) {
            $errors[] = 'verification.needs_review harus boolean';
        }

        if (self::get($data, 'derived_tags.is_inferred') !== true) {
            $reasons[] = 'derived_tags.is_inferred tidak di-set true';
        }

        // Pemicu needs_review otomatis
        if ($status !== 'found') {
            $reasons[] = 'match.status = ' . $status;
        }
        if ($confidence !== 'high') {
            $reasons[] = 'confidence = ' . $confidence;
        }
        if (in_array($crossStatus, [FuzzyMatcher::STATUS_PARTIAL, FuzzyMatcher::STATUS_MISMATCH], true)) {
            $reasons[] = 'cross-check notes internal: ' . $crossStatus;
        }
        if (count($sources) < 2) {
            $reasons[] = 'Sumber lemah (kurang dari 2)';
        }

        // Cek deterministik terhadap searah
        if ($searahExpected !== null && $status === 'found') {
            if ($searahExpected['gender'] !== null && $gender !== null && $gender !== $searahExpected['gender']) {
                $reasons[] = 'gender_target LLM (' . $gender . ') berbeda dengan searah (' . $searahExpected['gender'] . ')';
            }
            if ($searahExpected['year'] !== null && $year !== null && (int) $year !== (int) $searahExpected['year']) {
                $reasons[] = 'release_year LLM (' . $year . ') berbeda dengan tahun di searah (' . $searahExpected['year'] . ')';
            }
            if ($searahExpected['concentration'] !== null && $concentration !== null && $concentration !== $searahExpected['concentration']) {
                $reasons[] = 'konsentrasi LLM (' . $concentration . ') berbeda dengan searah (' . $searahExpected['concentration'] . ')';
            }
        }

        $llmReasons = self::get($data, 'verification.review_reasons');
        if (is_array($llmReasons)) {
            foreach ($llmReasons as $llmReason) {
                if (is_string($llmReason) && trim($llmReason) !== '') {
                    $reasons[] = trim($llmReason);
                }
            }
        }

        return [
            'ok' => count($errors) === 0,
            'errors' => array_values(array_unique($errors)),
            'review_reasons' => array_values(array_unique($reasons)),
        ];
    }

    protected static function fillDefaults(array $data)
    {
        foreach (['input', 'match', 'notes', 'derived_tags', 'verification'] as $key) {
            if (!isset($data[$key]) || !is_array($data[$key])) {
                $data[$key] = [];
            }
        }
        if (!isset($data['verification']['cross_check']) || !is_array($data['verification']['cross_check'])) {
            $data['verification']['cross_check'] = [];
        }

        return $data;
    }

    protected static function get(array $data, $path)
    {
        $parts = explode('.', $path);
        $value = $data;
        foreach ($parts as $part) {
            if (!is_array($value) || !array_key_exists($part, $value)) {
                return null;
            }
            $value = $value[$part];
        }

        return $value;
    }

    protected static function isEmptyValue($value)
    {
        return $value === null || $value === '' || $value === [];
    }
}
