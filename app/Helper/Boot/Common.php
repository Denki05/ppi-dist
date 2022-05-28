<?php

if (!function_exists('generate_unique_string')) {
    function generate_unique_string($length = null, $start_string = null, $finish_string = null)
    {
        $string = '';
        if ($start_string) {
            $string .= $start_string;
        }
        if ($length) {
            $string .= str_shuffle(strtoupper(Str::random($length).rand(1, 100)));
        } else {
            $string .= str_shuffle(strtoupper(Str::random().rand(1, 100)));
        }
        if ($finish_string) {
            $string .= $finish_string;
        }

        return $string;
    }
}


if (!function_exists('random_filename')) {
    function random_filename($file)
    {
        return generate_unique_string().'.'.$file->getClientOriginalExtension();
    }
}

if (!function_exists('img_holder')) {
    function img_holder($type = null)
    {
        switch ($type) {
          case 'avatar':
            return asset('superuser_assets/media/placeholders/avatar.png');
          break;
          default:
            return asset('superuser_assets/media/placeholders/default.png');
          break;
        }
    }
}

if (!function_exists('is_active_route')) {
    function is_active_route($route_name) {
        if ($route_name == Route::currentRouteName() OR Str::startsWith(URL::current(), route($route_name))) {
            return 'active';
        }
        return;
    }
}

if (!function_exists('is_open_route')) {
    function is_open_route($string) {
        if (Str::contains(URL::current(), $string)) {
            return 'open';
        }
        return;
    }
}

if (!function_exists('currency')) {
    function currency($amount, $decimals = 2) {
        if(empty($amount) || $amount == "")
            $amount = 0;
        $withDecimal = number_format($amount, $decimals, ',', '.');
        $decimalIfNeeded = str_replace(',00', '', $withDecimal);
        return $decimalIfNeeded;
    }
}

if (!function_exists('rupiah')) {
    function rupiah($amount, $decimals = 2) {
        return 'Rp ' . currency($amount, $decimals);
    }
}