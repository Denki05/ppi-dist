<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateProductAssets extends Command
{
    protected $signature = 'generate:product-assets';
    protected $description = 'Generate product_assets from master data';

    // =========================
    // API
    // =========================
    private function callApi($url, $params = [])
    {
        $query = http_build_query($params);
        $fullUrl = $url . '?' . $query;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fullUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            curl_close($ch);
            return null;
        }

        curl_close($ch);

        return json_decode($response, true);
    }

    // =========================
    // NORMALIZE (ANTI ERROR)
    // =========================
    private function normalize($text)
    {
        if (!$text) return '';

        $text = strtoupper((string)$text);

        // replace karakter khusus
        $map = [
            'À'=>'A','Á'=>'A','Â'=>'A','Ã'=>'A','Ä'=>'A','Å'=>'A',
            'È'=>'E','É'=>'E','Ê'=>'E','Ë'=>'E',
            'Ì'=>'I','Í'=>'I','Î'=>'I','Ï'=>'I',
            'Ò'=>'O','Ó'=>'O','Ô'=>'O','Õ'=>'O','Ö'=>'O',
            'Ù'=>'U','Ú'=>'U','Û'=>'U','Ü'=>'U',
            'Ç'=>'C','Ñ'=>'N',
            'Œ'=>'OE','Æ'=>'AE'
        ];

        $text = strtr($text, $map);

        // hapus simbol
        $text = preg_replace('/[^A-Z0-9\s]/', ' ', $text);

        // rapikan spasi
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }

    // =========================
    // KEYWORDS
    // =========================
    private function getKeywords($text)
    {
        $text = $this->normalize($text);

        $remove = [
            'EAU','DE','PARFUM','TOILETTE',
            'EDP','EDT',
            'FOR','MEN','WOMEN',
            'AND'
        ];

        $words = explode(' ', $text);

        $filtered = array_filter($words, function ($w) use ($remove) {
            return !in_array($w, $remove) && strlen($w) > 2;
        });

        return array_values(array_unique($filtered));
    }

    // =========================
    // BRAND MATCH
    // =========================
    private function matchBrand($dbBrand, $folders)
    {
        $dbNorm = $this->normalize($dbBrand);
        $dbWords = explode(' ', $dbNorm);

        // =========================
        // 1. EXACT MATCH
        // =========================
        foreach ($folders as $f) {
            if ($this->normalize($f) === $dbNorm) {
                return $f;
            }
        }

        // =========================
        // 2. FULL KEYWORD MATCH
        // =========================
        foreach ($folders as $f) {
            $fNorm = $this->normalize($f);

            $allMatch = true;
            foreach ($dbWords as $w) {
                if (!empty($w) && strpos($fNorm, $w) === false) {
                    $allMatch = false;
                    break;
                }
            }

            if ($allMatch) {
                return $f;
            }
        }

        // =========================
        // 3. INTERSECTION SCORE
        // =========================
        $best = null;
        $bestScore = 0;

        foreach ($folders as $f) {

            $fWords = explode(' ', $this->normalize($f));

            $score = count(array_intersect($dbWords, $fWords));

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $f;
            }
        }

        if ($bestScore >= 1) {
            return $best;
        }

        // =========================
        // 4. SIMILARITY (LAST)
        // =========================
        $best = null;
        $bestPercent = 0;

        foreach ($folders as $f) {
            similar_text($dbNorm, $this->normalize($f), $percent);

            if ($percent > $bestPercent) {
                $bestPercent = $percent;
                $best = $f;
            }
        }

        if ($bestPercent >= 60) {
            return $best;
        }

        // =========================
        // 5. FALLBACK: FIRST WORD
        // =========================
        $first = $dbWords[0] ?? null;

        if ($first) {
            foreach ($folders as $f) {
                if (strpos($this->normalize($f), $first) !== false) {
                    return $f;
                }
            }
        }

        return null;
    }

    // =========================
    // SEARAH MATCH
    // =========================
    private function matchSearah($dbSearah, $folders)
    {
        $dbWords = $this->getKeywords($dbSearah);

        $best = null;
        $bestScore = 0;

        foreach ($folders as $folder) {

            $folderNorm = $this->normalize($folder);
            $folderWords = explode(' ', $folderNorm);

            $score = count(array_intersect($dbWords, $folderWords));

            // BOOST kalau kata pertama cocok
            if (!empty($dbWords) && in_array($dbWords[0], $folderWords)) {
                $score += 2;
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $folder;
            }
        }

        if ($bestScore >= 1) {
            return $best;
        }

        // fallback similarity
        $dbNorm = $this->normalize($dbSearah);

        $best = null;
        $bestPercent = 0;

        foreach ($folders as $folder) {
            similar_text($dbNorm, $this->normalize($folder), $percent);

            if ($percent > $bestPercent) {
                $bestPercent = $percent;
                $best = $folder;
            }
        }

        return ($bestPercent >= 50) ? $best : null;
    }

    // =========================
    // MAIN PROCESS
    // =========================
    public function handle()
    {
        $brandCache = [];
        $searahCache = [];
        $failed = [];

        $rootMap = [
            'GCF' => '01_GCF',
            'SENSES' => '02_SENSES',
        ];

        $products = DB::select("
            SELECT
                master_brand_lokal.brand_name AS merek,
                master_brand_references.name AS brand,
                master_sub_brand_references.name AS searah,
                master_products.code AS product_code,
                master_products.name AS product_name,
                master_products.id AS product_id
            FROM master_sub_brand_references
            INNER JOIN master_brand_references 
                ON master_sub_brand_references.brand_reference_id = master_brand_references.id
            INNER JOIN master_products 
                ON master_products.sub_brand_reference_id = master_sub_brand_references.id
            INNER JOIN master_brand_lokal 
                ON master_products.brand_name = master_brand_lokal.brand_name
        ");

        foreach ($products as $p) {

            $merek = strtoupper(trim($p->merek));

            if (!isset($rootMap[$merek])) {
                continue;
            }

            $internal = $rootMap[$merek];

            // =========================
            // BRAND CACHE
            // =========================
            if (!isset($brandCache[$internal])) {

                $res = $this->callApi(
                    'https://drive.lssoft88.xyz/api/list',
                    ['path' => base64_encode($internal)]
                );

                if (!$res) continue;

                $brandCache[$internal] = collect($res)
                    ->where('type','folder')
                    ->pluck('name')
                    ->toArray();
            }

            $brandFolder = $this->matchBrand($p->brand, $brandCache[$internal]);

            if (!$brandFolder) {

                $failed[] = [
                    'type'=>'brand_not_match',
                    'product_code'=>$p->product_code,
                    'product_name'=>$p->product_name,
                    'merek'=>$p->merek,
                    'brand_db'=>$p->brand,
                    'available_folders'=>$brandCache[$internal]
                ];

                continue;
            }

            // =========================
            // SEARAH CACHE
            // =========================
            $key = $internal.'/'.$brandFolder;

            if (!isset($searahCache[$key])) {

                $res = $this->callApi(
                    'https://drive.lssoft88.xyz/api/list',
                    ['path' => base64_encode($key)]
                );

                if (!$res) continue;

                $searahCache[$key] = collect($res)
                    ->where('type','folder')
                    ->pluck('name')
                    ->toArray();
            }

            $searahFolder = $this->matchSearah($p->searah, $searahCache[$key]);

            if (!$searahFolder) {

                $failed[] = [
                    'type'=>'searah_not_match',
                    'product_code'=>$p->product_code,
                    'product_name'=>$p->product_name,
                    'merek'=>$p->merek,
                    'brand'=>$p->brand,
                    'searah_db'=>$p->searah,
                    'keywords'=>$this->getKeywords($p->searah),
                    'available_folders'=>$searahCache[$key]
                ];

                continue;
            }

            // =========================
            // SAVE
            // =========================
            $basePath = $internal.'/'.$brandFolder.'/'.$searahFolder.'/'.strtoupper($p->product_name);

            DB::table('product_assets')->updateOrInsert(
                ['product_id'=>$p->product_id],
                [
                    'product_code'=>$p->product_code,
                    'product_name'=>$p->product_name,
                    'merek'=>$p->merek,
                    'brand'=>$p->brand,
                    'searah'=>$p->searah,
                    'base_path'=>$basePath,
                    'updated_at'=>now(),
                    'created_at'=>now()
                ]
            );

            DB::table('folder_mappings')->updateOrInsert(
                [
                    'brand_db'=>$p->brand,
                    'searah_db'=>$p->searah
                ],
                [
                    'merek_db'=>$p->merek,
                    'merek_folder'=>$internal,
                    'brand_folder'=>$brandFolder,
                    'searah_folder'=>$searahFolder,
                    'updated_at'=>now(),
                    'created_at'=>now()
                ]
            );

            $this->info("✔ {$p->product_code} - {$p->product_name}");
        }

        // =========================
        // LOG
        // =========================
        file_put_contents(
            storage_path('logs/product_asset_failed.json'),
            json_encode($failed, JSON_PRETTY_PRINT)
        );

        $this->error("FAILED: ".count($failed));
        $this->info("DONE");
    }
}