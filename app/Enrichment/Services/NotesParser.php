<?php

namespace App\Enrichment\Services;

class NotesParser
{
    const PLACEHOLDERS = ['', '-', 'none', 'tidak ada', 'no notes'];

    public static function isPlaceholder($description)
    {
        return in_array(trim(mb_strtolower((string) $description)), self::PLACEHOLDERS, true);
    }

    /**
     * Parse teks description menjadi notes per level.
     *
     * @return array {top: string[], middle: string[], base: string[], flat: string[], has_internal_notes: bool}
     */
    public static function parse($description)
    {
        $result = ['top' => [], 'middle' => [], 'base' => [], 'flat' => [], 'has_internal_notes' => false];

        $description = trim((string) $description);
        if ($description === '' || self::isPlaceholder($description)) {
            return $result;
        }

        $result['has_internal_notes'] = true;

        $sections = preg_split(
            '/(?:^|[\r\n;])\s*(top|middle|heart|base)\s*notes?\s*[:;,\-]*/iu',
            $description,
            -1,
            PREG_SPLIT_DELIM_CAPTURE
        );

        if (is_array($sections) && count($sections) >= 3) {
            for ($i = 1; $i < count($sections); $i += 2) {
                $level = mb_strtolower($sections[$i]);
                $level = ($level === 'heart') ? 'middle' : $level;
                $content = isset($sections[$i + 1]) ? $sections[$i + 1] : '';
                $result[$level] = self::splitItems($content);
            }
        } elseif (preg_match('/notes?\s*[:;,\-]*/iu', $description)) {
            $parts = preg_split('/notes?\s*[:;,\-]*/iu', $description, 2);
            $result['flat'] = self::splitItems(isset($parts[1]) ? $parts[1] : '');
        }

        return $result;
    }

    public static function flatList(array $parsed)
    {
        $all = array_merge($parsed['top'], $parsed['middle'], $parsed['base'], $parsed['flat']);

        return array_values(array_unique($all));
    }

    protected static function splitItems($text)
    {
        $items = preg_split('/[,;\/\r\n]+/u', $text);
        $cleaned = [];
        foreach ($items as $item) {
            $item = trim($item);
            $item = preg_replace('/[.,;:]+$/u', '', $item);
            $item = trim($item);
            if ($item !== '' && mb_strtolower($item) !== 'notes' && !in_array(mb_strtolower($item), self::PLACEHOLDERS, true)) {
                $cleaned[] = $item;
            }
        }

        return $cleaned;
    }
}
