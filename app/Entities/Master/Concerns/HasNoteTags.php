<?php

namespace App\Entities\Master\Concerns;

trait HasNoteTags
{
    public function getNoteListAttribute(): array
    {
        return static::parseNoteTags($this->attributes['note'] ?? null);
    }

    public function setNoteAttribute($value): void
    {
        $this->attributes['note'] = static::encodeNoteTags($value);
    }

    public static function parseNoteTags($value): array
    {
        if (is_array($value)) {
            $tags = $value;
        } elseif (is_string($value) && trim($value) !== '') {
            $decoded = json_decode($value, true);
            $tags = is_array($decoded) ? $decoded : [$value];
        } else {
            $tags = [];
        }

        $result = [];
        foreach ($tags as $tag) {
            if (!is_string($tag)) {
                continue;
            }

            $tag = strtoupper(trim(preg_replace('/\s+/', ' ', $tag)));
            if ($tag !== '' && !in_array($tag, $result, true)) {
                $result[] = $tag;
            }
        }

        return $result;
    }

    public static function encodeNoteTags($value): ?string
    {
        $tags = static::parseNoteTags($value);

        return $tags ? json_encode($tags) : null;
    }
}
