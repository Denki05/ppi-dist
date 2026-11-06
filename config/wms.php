<?php

return [

    /*
    |--------------------------------------------------------------------
    | Akses project WMS (SPV Gudang)
    |--------------------------------------------------------------------
    | Allowlist user yang boleh masuk lewat API WMS. Tidak memakai profil
    | akses baru; cukup daftar user id agar tidak mengubah hak di Trans.
    |
    | Format .env: comma-separated user id, contoh: "1,29,33"
    */

    'user_ids' => array_values(array_filter(array_map('trim',
        explode(',', env('WMS_USER_IDS', '1,29,33'))
    ))),

    // User yang ditandai sebagai pengambil alih operasional (badge Overtake).
    'overtake_user_ids' => array_values(array_filter(array_map('trim',
        explode(',', env('WMS_OVERTAKE_USER_IDS', '1,33'))
    ))),

    // Masa berlaku API token WMS (detik). Default 8 jam.
    'api_token_ttl' => (int) env('WMS_API_TOKEN_TTL', 28800),

];
