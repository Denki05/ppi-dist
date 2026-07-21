<?php

return [

    /*
    |--------------------------------------------------------------------
    | Logistik User Mapping
    |--------------------------------------------------------------------
    | Sementara ini pemetaan role logistik masih ditembak berdasarkan
    | user_id (bukan lewat menu/permission terpisah), karena user-nya
    | dipastikan tetap/khusus untuk masing-masing peran.
    |
    | Format di .env: comma-separated user id, contoh: "41,55,60"
    */

    'spv_gudang_user_ids' => array_filter(array_map('trim',
        explode(',', env('SPV_GUDANG_USER_IDS', '29'))
    )),

    'checker_user_ids' => array_filter(array_map('trim',
        explode(',', env('CHECKER_USER_IDS', '41'))
    )),

];