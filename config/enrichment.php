<?php

/*
|--------------------------------------------------------------------------
| Enrichment - Pengayaan data parfum via LLM
|--------------------------------------------------------------------------
| Driver LLM dipilih dari halaman Superuser > Enrichment > Pengaturan
| (tersimpan di settings database). Kredensial per driver:
| - isi lewat halaman Pengaturan (disimpan di settings), atau
| - fallback ke env key di bawah.
|
| Menambah LLM baru:
| 1. Buat class di app/Enrichment/Drivers/ yang implements App\Enrichment\Contracts\Driver
| 2. Daftarkan di array "drivers" di bawah
| 3. Driver akan otomatis muncul di dropdown Pengaturan
*/

return [

    'default_driver' => env('ENRICHMENT_DRIVER', 'gemini'),

    'drivers' => [
        'gemini' => [
            'label' => 'Gemini (Google)',
            'class' => \App\Enrichment\Drivers\GeminiDriver::class,
            'env_key' => 'GEMINI_API_KEY',
            'model' => 'gemini-3.8-flash',
            'temperature' => 0.1,
            'supports_search' => true,
        ],
        'groq' => [
            'label' => 'Groq (free tier)',
            'class' => \App\Enrichment\Drivers\OpenAiCompatibleDriver::class,
            'env_key' => 'GROQ_API_KEY',
            'base_url' => 'https://api.groq.com/openai/v1',
            'model' => 'openai/gpt-oss-120b',
            'temperature' => 0.1,
        ],
        'openrouter' => [
            'label' => 'OpenRouter (model :free)',
            'class' => \App\Enrichment\Drivers\OpenAiCompatibleDriver::class,
            'env_key' => 'OPENROUTER_API_KEY',
            'base_url' => 'https://openrouter.ai/api/v1',
            'model' => 'nvidia/nemotron-3-ultra-550b-a55b:free',
            'temperature' => 0.1,
        ],
        'nvidia' => [
            'label' => 'NVIDIA NIM (free)',
            'class' => \App\Enrichment\Drivers\OpenAiCompatibleDriver::class,
            'env_key' => 'NVIDIA_API_KEY',
            'base_url' => 'https://integrate.api.nvidia.com/v1',
            'model' => 'meta/llama-3.2-11b-vision-instruct',
            'temperature' => 0.1,
        ],
    ],

    'system_prompt' => <<<'EOT'
Kamu adalah asisten riset data parfum. Tugasmu mengumpulkan data FAKTUAL
tentang satu parfum acuan dan mengembalikannya sebagai JSON.

ATURAN:
1. Gunakan pencarian web. Jangan hanya mengandalkan ingatan.
2. Cari di sumber parfum yang kredibel (mis. Fragrantica, Parfumo, situs
   resmi brand/retailer besar). Prioritaskan sumber resmi untuk tahun rilis.
3. Jangan mengarang. Jika data tidak ditemukan atau tidak yakin, isi null
   (untuk nilai tunggal) atau [] (untuk daftar). Lebih baik kosong daripada salah.
4. Pastikan parfum yang dicocokkan benar-benar sama dengan input, termasuk
   varian (Eau de Parfum/Toilette/Intense/Parfum) dan target gender. Jika
   ada beberapa kandidat yang sama masuk akal, set match.status = "ambiguous"
   dan jelaskan di match_note. Jangan memilih diam-diam.
5. Jika parfum tidak ditemukan, set match.status = "not_found" dan kosongkan
   semua field data.
6. Notes dan accord ditulis dalam bahasa Inggris, huruf kapital di awal kata,
   tanpa typo, satu elemen per array (mis. "Pink Pepper", bukan "pink pepper,").
7. Jangan menyalin kalimat deskripsi dari situs mana pun. description_id
   harus ditulis sendiri dalam bahasa Indonesia, maksimal 2 kalimat, netral.
8. derived_tags adalah inferensi dari notes dan accord, bukan fakta sumber.
   Selalu set is_inferred = true.
9. release_year harus berupa angka 4 digit antara 1900 dan 2026, atau null.
10. sources hanya berisi URL yang benar-benar kamu buka dan pakai.
11. Jika "referensi_notes_internal" diberikan, itu hanya petunjuk dan BISA
    SALAH. Cari notes secara mandiri dulu, baru bandingkan. Isi
    cross_check.reference_notes_status: "match" (sama), "partial" (sebagian
    berbeda), "mismatch" (sangat berbeda), atau "no_reference" (tidak ada
    pembanding). Tulis perbedaan penting di differences.
12. Set needs_review = true jika: match.status bukan "found", confidence
    bukan "high", status cross-check "partial" atau "mismatch", atau hanya
    ada satu sumber lemah. Isi review_reasons dengan alasannya.
13. Keluarkan HANYA satu objek JSON valid sesuai skema. Tanpa teks lain,
    tanpa markdown, tanpa komentar.
EOT

    ,

    'user_template' => <<<'EOT'
Cari data untuk parfum berikut dan isi skema JSON yang telah ditentukan.

brand: {{brand}}
searah: {{searah}}
referensi_notes_internal: {{internal_notes}}

Skema JSON:
{{json_template}}
EOT

    ,

    'json_template' => <<<'EOT'
{
  "input": {"brand": "string", "searah": "string"},
  "match": {"status": "found | ambiguous | not_found", "matched_name": "string | null", "matched_brand": "string | null", "concentration": "EDT | EDP | Parfum | Cologne | Extrait | Other | null", "match_note": "string | null"},
  "release_year": 2015,
  "perfumers": ["string"],
  "gender_target": "women | men | unisex | null",
  "main_accords": ["string"],
  "notes": {"top": ["string"], "middle": ["string"], "base": ["string"]},
  "scent_family": "citrus | aromatic | fougere | woody | amber_oriental | floral | fruity | gourmand | musk | aquatic | leather | chypre | green | oud | other | null",
  "derived_tags": {"mood": ["fresh | sweet | elegant | sporty | sensual | warm | clean | bold | romantic | calm"], "season": ["spring | summer | autumn | winter"], "time_of_day": ["day | night | all_day"], "is_inferred": true},
  "description_id": "string (1-2 kalimat bahasa Indonesia, ditulis sendiri)",
  "verification": {
    "confidence": "high | medium | low",
    "release_year_confidence": "high | medium | low | null",
    "sources": [{"title": "string", "url": "string"}],
    "cross_check": {"reference_notes_status": "match | partial | mismatch | no_reference", "differences": ["string"]},
    "needs_review": true,
    "review_reasons": ["string"]
  }
}
EOT

    ,

    'no_search_note' => <<<'EOT'
CATATAN SESI INI: kamu tidak punya akses pencarian web. Jangan mengarang URL
atau mengaku membuka halaman yang tidak pernah kamu buka. Jika tidak ada URL
yang benar-benar kamu ketahui, kembalikan verification.sources = [] dan set
verification.confidence maksimal "medium".
EOT

];
