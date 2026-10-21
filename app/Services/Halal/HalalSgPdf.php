<?php

namespace App\Services\Halal;

/**
 * Parser PDF minimal untuk ekstraksi aset visual PDF resmi MUIS
 * (gambar latar + QR code) tanpa dependensi baru.
 *
 * Hanya objek "klasik" (bukan object stream) yang didukung; PDF hasil
 * normalisasi Ghostscript (yang dipakai aplikasi) sudah memenuhi syarat.
 */
class HalalSgPdf
{
    /** @var array objek PDF: nomor => ['dict' => string, 'data' => string|null] */
    protected $objects = [];

    /** @var string isi file PDF */
    protected $raw = '';

    public function __construct($path)
    {
        if (!is_file($path)) {
            throw new \RuntimeException('File PDF tidak ditemukan: ' . $path);
        }
        $this->raw = file_get_contents($path);
        $this->parseObjects();
    }

    /**
     * Pecah file PDF menjadi objek (dict + stream mentah).
     */
    protected function parseObjects()
    {
        $raw = $this->raw;
        if (!preg_match_all('/(\d+)\s+0\s+obj\b/', $raw, $m, PREG_OFFSET_CAPTURE)) {
            return;
        }
        $total = count($m[0]);
        for ($i = 0; $i < $total; $i++) {
            $hit = $m[0][$i][0];
            $num = (int) $m[1][$i][0];
            $off = $m[0][$i][1] + strlen($hit);
            $next = ($i + 1 < $total) ? $m[0][$i + 1][1] : strlen($raw);
            $chunk = substr($raw, $off, $next - $off);
            $ss = strpos($chunk, 'stream');
            $dict = ($ss !== false && $ss < 4000) ? substr($chunk, 0, $ss) : substr($chunk, 0, 1500);
            $data = null;
            if ($ss !== false && $ss < 4000) {
                $ds = $ss + 6;
                while (isset($chunk[$ds]) && ($chunk[$ds] === "\r" || $chunk[$ds] === "\n")) {
                    $ds++;
                }
                $end = strpos($chunk, 'endstream', $ds);
                if ($end !== false) {
                    $data = substr($chunk, $ds, $end - $ds);
                }
            }
            $this->objects[$num] = ['dict' => $dict, 'data' => $data];
        }
    }

    /**
     * Inflasi stream (FlateDecode) bila perlu.
     */
    public function inflate($num)
    {
        if (!isset($this->objects[$num])) {
            return null;
        }
        $o = $this->objects[$num];
        if ($o['data'] === null) {
            return null;
        }
        if (strpos($o['dict'], '/FlateDecode') === false) {
            return $o['data'];
        }
        $z = @gzuncompress($o['data']);
        if ($z === false) {
            $z = @gzinflate($o['data']);
        }
        return $z === false ? null : $z;
    }

    /**
     * Nomor objek halaman PDF sesuai urutan /Kids.
     * @return array<int> nomor objek
     */
    public function pageObjects()
    {
        $kids = [];
        foreach ($this->objects as $num => $o) {
            if (strpos($o['dict'], '/Type') === false || strpos($o['dict'], '/Pages') === false) {
                continue;
            }
            if (preg_match('/\/Kids\s*\[(.*?)\]/s', $o['dict'], $km)) {
                preg_match_all('/(\d+)\s+0\s+R/', $km[1], $refs);
                $kids = array_map('intval', $refs[1]);
                break;
            }
        }
        return $kids;
    }

    /**
     * Dictionary mentah sebuah objek PDF.
     * @param int $num nomor objek
     * @return string
     */
    public function objectDict($num)
    {
        return isset($this->objects[$num]) ? $this->objects[$num]['dict'] : '';
    }

    /**
     * Dictionary sumber daya halaman (mengikuti referensi /Resources).
     */
    protected function resources($pageDict)
    {
        if (preg_match('/\/Resources\s+(\d+)\s+0\s+R/', $pageDict, $r)) {
            return isset($this->objects[(int) $r[1]]) ? $this->objects[(int) $r[1]]['dict'] : '';
        }
        if (preg_match('/\/Resources\s*<<(.*)$/s', $pageDict, $r)) {
            return $r[1];
        }
        return '';
    }

    /**
     * Nama XObject gambar => nomor objek (mengikuti referensi /XObject).
     * @param string $resDict dictionary sumber daya
     * @return array
     */
    protected function xobjects($resDict)
    {
        $map = [];
        if (preg_match('/\/XObject\s+(\d+)\s+0\s+R/', $resDict, $r)) {
            $num = (int) $r[1];
            $dict = isset($this->objects[$num]) ? $this->objects[$num]['dict'] : '';
        } elseif (preg_match('/\/XObject\s*<<(.*?)>>/s', $resDict, $xm)) {
            $dict = $xm[1];
        } else {
            return $map;
        }
        if (preg_match_all('/\/([A-Za-z0-9_\.]+)\s+(\d+)\s+0\s+R/', $dict, $refs, PREG_SET_ORDER)) {
            foreach ($refs as $r) {
                $map[$r[1]] = (int) $r[2];
            }
        }
        return $map;
    }

    /**
     * Isi stream konten halaman (menggabungkan /Contents).
     */
    public function pageContent($pageDict)
    {
        if (!preg_match('/\/Contents\s+(\d+)\s+0\s+R/', $pageDict, $c)) {
            return '';
        }
        $s = $this->inflate((int) $c[1]);
        return $s === null ? '' : $s;
    }

    /**
     * Nama XObject gambar yang dipakai halaman beserta matriks penempatannya.
     * @return array<int, array{name:string, matrix:array}>
     */
    public function imagePlacements($pageDict)
    {
        $res = $this->resources($pageDict);
        $map = $this->xobjects($res);
        $content = $this->pageContent($pageDict);
        $out = [];
        // tokenize manual: cari pola "a b c d e f cm /Name Do"
        if (preg_match_all('/([-\d\.]+)\s+([-\d\.]+)\s+([-\d\.]+)\s+([-\d\.]+)\s+([-\d\.]+)\s+([-\d\.]+)\s+cm\s*\/([A-Za-z0-9_\.]+)\s+Do/s', $content, $ms, PREG_SET_ORDER)) {
            foreach ($ms as $m) {
                $name = $m[7];
                $out[] = [
                    'name' => $name,
                    'object' => isset($map[$name]) ? $map[$name] : null,
                    'matrix' => [(float) $m[1], (float) $m[2], (float) $m[3], (float) $m[4], (float) $m[5], (float) $m[6]],
                ];
            }
        }
        return $out;
    }

    /**
     * Tokenizer sederhana untuk content stream (hex string, nama, angka, operator).
     * @return array
     */
    public function tokenize($s)
    {
        $tokens = [];
        $len = strlen($s);
        $i = 0;
        while ($i < $len) {
            $c = $s[$i];
            if ($c === ' ' || $c === "\r" || $c === "\n" || $c === "\t") {
                $i++;
                continue;
            }
            if ($c === '%') {
                while ($i < $len && $s[$i] !== "\n") {
                    $i++;
                }
                continue;
            }
            if ($c === '<' && $i + 1 < $len && $s[$i + 1] === '<') {
                $tokens[] = '<<';
                $i += 2;
                continue;
            }
            if ($c === '>' && $i + 1 < $len && $s[$i + 1] === '>') {
                $tokens[] = '>>';
                $i += 2;
                continue;
            }
            if ($c === '<') {
                $j = strpos($s, '>', $i);
                if ($j === false) {
                    break;
                }
                $tokens[] = substr($s, $i, $j - $i + 1);
                $i = $j + 1;
                continue;
            }
            if ($c === '(') {
                $j = $i + 1;
                $depth = 1;
                $buf = '';
                while ($j < $len && $depth > 0) {
                    if ($s[$j] === '\\') {
                        $buf .= isset($s[$j + 1]) ? $s[$j + 1] : '';
                        $j += 2;
                        continue;
                    }
                    if ($s[$j] === '(') {
                        $depth++;
                    }
                    if ($s[$j] === ')') {
                        $depth--;
                        if ($depth === 0) {
                            break;
                        }
                    }
                    $buf .= $s[$j];
                    $j++;
                }
                $tokens[] = '(' . $buf . ')';
                $i = $j + 1;
                continue;
            }
            if ($c === '[' || $c === ']') {
                $tokens[] = $c;
                $i++;
                continue;
            }
            if ($c === '/') {
                $j = $i + 1;
                while ($j < $len && !preg_match('/[\s\(\)\[\]<>\/{}%]/', $s[$j])) {
                    $j++;
                }
                $tokens[] = substr($s, $i, $j - $i);
                $i = $j;
                continue;
            }
            if (preg_match('/^[+-]?(\d+\.?\d*|\.\d+)/', substr($s, $i, 32), $mm)) {
                $tokens[] = $mm[0];
                $i += strlen($mm[0]);
                continue;
            }
            $j = $i;
            while ($j < $len && !preg_match('/[\s\(\)\[\]<>\/{}%]/', $s[$j])) {
                $j++;
            }
            if ($j === $i) {
                $j++;
            }
            $tokens[] = substr($s, $i, $j - $i);
            $i = $j;
        }
        return $tokens;
    }

    /**
     * Simpan gambar XObject ke file.
     * @param int $numObj nomor objek gambar
     * @param string $pathFile tujuan (ekstensi menentukan format)
     * @return bool berhasil?
     */
    public function saveImage($numObj, $pathFile)
    {
        if (!isset($this->objects[$numObj])) {
            return false;
        }
        $o = $this->objects[$numObj];
        $dict = $o['dict'];
        $w = preg_match('/\/Width\s+(\d+)/', $dict, $a) ? (int) $a[1] : 0;
        $h = preg_match('/\/Height\s+(\d+)/', $dict, $a) ? (int) $a[1] : 0;
        if ($w <= 0 || $h <= 0) {
            return false;
        }
        // JPEG: bytes stream bisa langsung disimpan.
        if (strpos($dict, '/DCTDecode') !== false && $o['data'] !== null) {
            return (bool) file_put_contents($pathFile, $o['data']);
        }
        // Format lain (FlateDecode DeviceRGB/DeviceGray) -> PNG via GD.
        $data = $this->inflate($numObj);
        if ($data === null || strlen($data) < $w * $h) {
            return false;
        }
        $mask = null;
        if (preg_match('/\/SMask\s+(\d+)\s+0\s+R/', $dict, $sm)) {
            $mask = $this->inflate((int) $sm[1]);
        }
        $im = imagecreatetruecolor($w, $h);
        imagesavealpha($im, true);
        for ($y = 0; $y < $h; $y++) {
            for ($x = 0; $x < $w; $x++) {
                $i = ($y * $w + $x) * 3;
                if ($i + 2 >= strlen($data)) {
                    break;
                }
                $r = ord($data[$i]);
                $g = ord($data[$i + 1]);
                $b = ord($data[$i + 2]);
                $a = 127;
                if ($mask !== null && isset($mask[$y * $w + $x])) {
                    $a = 127 - (int) round(ord($mask[$y * $w + $x]) * 127 / 255);
                }
                imagesetpixel($im, $x, $y, imagecolorallocatealpha($im, $r, $g, $b, $a));
            }
        }
        imagepng($im, $pathFile);
        imagedestroy($im);
        return true;
    }
}
