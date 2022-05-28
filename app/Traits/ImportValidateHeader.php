<?php

namespace App\Traits;

trait ImportValidateHeader
{
    private function validateHeader(array $header, array $row)
    {
        foreach($header as $text) {
            if (!array_key_exists($text, $row)) {
                abort(400);
            }
        }
    }
}
