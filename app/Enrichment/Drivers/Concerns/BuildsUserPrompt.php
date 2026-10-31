<?php

namespace App\Enrichment\Drivers\Concerns;

trait BuildsUserPrompt
{
    public function buildUserPrompt($brand, $searah, $internalNotes)
    {
        $internalNotes = trim((string) $internalNotes);
        if ($internalNotes === '' || in_array(mb_strtolower($internalNotes), ['tidak ada', 'none', '-'], true)) {
            $internalNotes = 'TIDAK ADA';
        }

        return str_replace(
            ['{{brand}}', '{{searah}}', '{{internal_notes}}', '{{json_template}}'],
            [(string) $brand, (string) $searah, $internalNotes, (string) $this->config['json_template']],
            (string) $this->config['user_template']
        );
    }

    public static function extractJson($text)
    {
        $start = strpos($text, '{');
        $end = strrpos($text, '}');
        if ($start === false || $end === false || $end <= $start) {
            return null;
        }

        return substr($text, $start, $end - $start + 1);
    }
}
