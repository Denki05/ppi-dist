<?php

if (!function_exists('is_file_exists')) {
    function is_file_exists($file)
    {
        return $file !== null AND is_file($file);
    }
}

if (!function_exists('remove_file')) {
    function remove_file($path)
    {
        if (unlink($path)) {
            return true;
        }

        return false;
    }
}

if (!function_exists('textarea_to_array')) {
    function textarea_to_array($input) {
        return explode("\n", str_replace("\r", "", $input));
    }
}

if (!function_exists('strip_vowels')) {
    function strip_vowels($string, $length = null) {
        $str = preg_replace('#[aeiou\s]+#i', '', $string);

        if ($length != null) {
            $str = substr($str, 0, $length);
        }

        return $str;
    }
}