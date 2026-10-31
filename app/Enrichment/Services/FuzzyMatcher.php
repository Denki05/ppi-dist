<?php

namespace App\Enrichment\Services;

class FuzzyMatcher
{
    const STATUS_MATCH = 'match';
    const STATUS_PARTIAL = 'partial';
    const STATUS_MISMATCH = 'mismatch';
    const STATUS_NO_REFERENCE = 'no_reference';

    /**
     * Bandingkan notes hasil LLM dengan notes internal (dari description produk).
     *
     * @param array $llmNotes daftar note hasil LLM (sudah flat)
     * @param array $internalNotes daftar note internal
     * @return array {status: string, differences: string[]}
     */
    public static function crossCheck(array $llmNotes, array $internalNotes)
    {
        $internalNotes = array_values(array_filter(array_map(function ($note) {
            return trim((string) $note);
        }, $internalNotes), function ($note) {
            return $note !== '';
        }));

        if (count($internalNotes) === 0) {
            return ['status' => self::STATUS_NO_REFERENCE, 'differences' => []];
        }

        $llmNormalized = array_map(function ($note) {
            return NameNormalizer::normalize($note);
        }, $llmNotes);

        $differences = [];
        $matchedCount = 0;

        foreach ($internalNotes as $internal) {
            if (self::findMatch(NameNormalizer::normalize($internal), $llmNormalized)) {
                $matchedCount++;
            } else {
                $differences[] = 'Note internal "' . $internal . '" tidak ada di hasil LLM';
            }
        }

        if ($matchedCount === count($internalNotes)) {
            $status = self::STATUS_MATCH;
        } elseif ($matchedCount > 0) {
            $status = self::STATUS_PARTIAL;
        } else {
            $status = self::STATUS_MISMATCH;
        }

        return ['status' => $status, 'differences' => $differences];
    }

    protected static function findMatch($internal, array $llmNormalized)
    {
        foreach ($llmNormalized as $llm) {
            if ($llm === '') {
                continue;
            }
            if ($internal === $llm) {
                return true;
            }
            if (mb_strlen($internal) >= 3 && mb_strlen($llm) >= 3 && (strpos($llm, $internal) !== false || strpos($internal, $llm) !== false)) {
                if (self::isWordBoundaryMatch($internal, $llm)) {
                    return true;
                }
            }
            $len = min(mb_strlen($internal), mb_strlen($llm));
            if ($len >= 5 && levenshtein($internal, $llm) <= 2) {
                return true;
            }
            if (self::similarityRatio($internal, $llm) >= 0.75) {
                return true;
            }
        }

        return false;
    }

    protected static function isWordBoundaryMatch($a, $b)
    {
        $shorter = mb_strlen($a) <= mb_strlen($b) ? $a : $b;
        $longer = ($shorter === $a) ? $b : $a;

        return (bool) preg_match('/(^|[^a-z0-9])' . preg_quote($shorter, '/') . '($|[^a-z0-9])/u', $longer);
    }

    protected static function similarityRatio($a, $b)
    {
        if ($a === '' || $b === '') {
            return 0.0;
        }
        similar_text($a, $b, $percent);

        return $percent / 100;
    }
}
