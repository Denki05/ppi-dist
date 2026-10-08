<?php

namespace App\Helper;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CustomHelper
{
    /**
     * Parse input kurs IDR ke float, tahan format ID maupun EN.
     * ID: "18.025" / "18.025,00" / "18025,00" -> 18025
     * EN: "18025.00" / "18,025.00" -> 18025
     * Aturan: bila titik & koma sama-sama ada, pemisah TERAKHIR adalah desimal.
     * Titik tunggal pola ribuan (\d{1,3}(\.\d{3})+) dianggap ribuan, selainnya desimal.
     */
    public static function parseKurs($value) {
        if ($value === null || $value === '') return 0;
        if (is_numeric($value) && strpos((string) $value, '.') === false && strpos((string) $value, ',') === false) {
            return (float) $value;
        }
        $s = trim((string) $value);
        $s = str_replace(["\xc2\xa0", ' ', 'Rp', 'RP', 'rp', 'IDR', 'Idr', 'idr'], '', $s);
        if ($s === '' || $s === '-' || $s === '.' || $s === ',') return 0;
        $hasDot = strpos($s, '.') !== false;
        $hasComma = strpos($s, ',') !== false;
        if ($hasDot && $hasComma) {
            if (strrpos($s, ',') > strrpos($s, '.')) {
                // ID: titik ribuan, koma desimal.
                $s = str_replace('.', '', $s);
                $s = str_replace(',', '.', $s);
            } else {
                // EN: koma ribuan, titik desimal.
                $s = str_replace(',', '', $s);
            }
        } elseif ($hasComma) {
            if (substr_count($s, ',') > 1) {
                $s = str_replace(',', '', $s);
            } elseif (preg_match('/,\d{1,2}$/', $s)) {
                $s = str_replace(',', '.', $s);
            } else {
                $s = str_replace(',', '', $s);
            }
        } else {
            if (substr_count($s, '.') > 1) {
                $s = str_replace('.', '', $s);
            } elseif (!preg_match('/^\d{1,3}(\.\d{3})+$/', $s)) {
                // Titik tunggal bukan pola ribuan -> desimal, biarkan.
            } else {
                $s = str_replace('.', '', $s);
            }
        }
        $s = preg_replace('/[^0-9.\-]/', '', $s);
        if ($s === '' || $s === '-' || $s === '.') return 0;
        return (float) $s;
    }

    public static function terbilang($nilai) {
        if($nilai<0) {
            $hasil = "minus ". trim(self::penyebut($nilai));
        } else {
            $hasil = trim(self::penyebut($nilai));
        }           
        return $hasil;
    }
    public static function penyebut($nilai) {
            $nilai = abs($nilai);
            $huruf = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
            $temp = "";
            if ($nilai < 12) {
                $temp = " ". $huruf[$nilai];
            } else if ($nilai <20) {
                $temp = self::penyebut($nilai - 10). " belas";
            } else if ($nilai < 100) {
                $temp = self::penyebut($nilai/10)." puluh". self::penyebut($nilai % 10);
            } else if ($nilai < 200) {
                $temp = " seratus" . self::penyebut($nilai - 100);
            } else if ($nilai < 1000) {
                $temp = self::penyebut($nilai/100) . " ratus" . self::penyebut($nilai % 100);
            } else if ($nilai < 2000) {
                $temp = " seribu" . self::penyebut($nilai - 1000);
            } else if ($nilai < 1000000) {
                $temp = self::penyebut($nilai/1000) . " ribu" . self::penyebut($nilai % 1000);
            } else if ($nilai < 1000000000) {
                $temp = self::penyebut($nilai/1000000) . " juta" . self::penyebut($nilai % 1000000);
            } else if ($nilai < 1000000000000) {
                $temp = self::penyebut($nilai/1000000000) . " milyar" . self::penyebut(fmod($nilai,1000000000));
            } else if ($nilai < 1000000000000000) {
                $temp = self::penyebut($nilai/1000000000000) . " trilyun" . self::penyebut(fmod($nilai,1000000000000));
            }     
            return $temp;
        }
}